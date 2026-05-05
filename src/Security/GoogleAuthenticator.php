<?php

namespace App\Security;

use App\Entity\User;
use App\Service\BrevoEmailService;
use Doctrine\ORM\EntityManagerInterface;
use League\OAuth2\Client\Provider\Google;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class GoogleAuthenticator extends AbstractAuthenticator
{
    private EntityManagerInterface $entityManager;
    private RouterInterface $router;
    private BrevoEmailService $emailService;
    private UserPasswordHasherInterface $passwordHasher;

    private array $allowedDomains = [
        'gmail.com',
        'lumiere.com',
        'staff.lumiere.com',
    ];

    public function __construct(
        EntityManagerInterface $entityManager,
        RouterInterface $router,
        BrevoEmailService $emailService,
        UserPasswordHasherInterface $passwordHasher
    ) {
        $this->entityManager = $entityManager;
        $this->router = $router;
        $this->emailService = $emailService;
        $this->passwordHasher = $passwordHasher;
    }

    private function getGoogleProvider(Request $request): Google
    {
        $provider = new Google([
            'clientId'     => $_ENV['GOOGLE_CLIENT_ID'] ?? '',
            'clientSecret' => $_ENV['GOOGLE_CLIENT_SECRET'] ?? '',
            'redirectUri'  => $request->getSchemeAndHttpHost() . '/connect/google/check',
        ]);
        
        // For development only - Disable SSL verification
        // Remove this line in production!
        $provider->setHttpClient(new \GuzzleHttp\Client([
            'verify' => false
        ]));
        
        return $provider;
    }

    public function supports(Request $request): ?bool
    {
        return $request->getPathInfo() === '/connect/google/check' && $request->get('code');
    }

    public function authenticate(Request $request): Passport
    {
        $code = $request->get('code');
        
        try {
            $provider = $this->getGoogleProvider($request);
            
            // Get access token
            $token = $provider->getAccessToken('authorization_code', [
                'code' => $code
            ]);
            
            // Get user info
            $googleUser = $provider->getResourceOwner($token);
            $email = $googleUser->getEmail();
            $domain = substr(strrchr($email, "@"), 1);
            
            if (!in_array($domain, $this->allowedDomains)) {
                throw new AuthenticationException('Access denied. Only staff members can login with Google.');
            }
            
            $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
            
            if (!$user) {
                $user = new User();
                $user->setEmail($email);
                $user->setUsername($email);
                $user->setRoles(['ROLE_STAFF', 'ROLE_USER']);
                $user->setIsActive(true);
                $user->setIsVerified(true);
                $user->setGoogleId($googleUser->getId());
                $user->setRegistrationSource('google');
                
                $nameParts = explode(' ', $googleUser->getName() ?? explode('@', $email)[0], 2);
                $user->setFirstName($nameParts[0]);
                if (isset($nameParts[1])) {
                    $user->setLastName($nameParts[1]);
                }
                
                $user->setPassword($this->passwordHasher->hashPassword($user, bin2hex(random_bytes(32))));
                
                $this->entityManager->persist($user);
                $this->entityManager->flush();
                
                try {
                    $this->emailService->sendWelcomeEmail($email, $user->getFirstName() ?? $user->getUsername());
                } catch (\Exception $e) {
                    error_log('Welcome email failed: ' . $e->getMessage());
                }
            } else {
                if (!$user->getGoogleId()) {
                    $user->setGoogleId($googleUser->getId());
                    $user->setRegistrationSource('google');
                    $this->entityManager->flush();
                }
                
                try {
                    $this->emailService->sendGoogleLoginAlert($email, $user->getFirstName() ?? $user->getUsername());
                } catch (\Exception $e) {
                    error_log('Login alert failed: ' . $e->getMessage());
                }
            }
            
            if (!$user->isActive()) {
                throw new AuthenticationException('Your account is disabled.');
            }
            
            return new SelfValidatingPassport(new UserBadge($user->getUsername(), fn() => $user));
            
        } catch (\Exception $e) {
            throw new AuthenticationException('Google authentication failed: ' . $e->getMessage());
        }
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $user = $token->getUser();
        $request->getSession()->getFlashBag()->add('success', 'Welcome ' . ($user->getFirstName() ?? $user->getUsername()) . '!');
        return new RedirectResponse($this->router->generate('admin_dashboard'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $request->getSession()->getFlashBag()->add('error', $exception->getMessage());
        return new RedirectResponse($this->router->generate('login'));
    }
}