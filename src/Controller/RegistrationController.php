<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Service\EmailVerificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private EmailVerificationService $emailVerificationService;

    public function __construct(
        EntityManagerInterface $entityManager,
        EmailVerificationService $emailVerificationService
    ) {
        $this->entityManager = $entityManager;
        $this->emailVerificationService = $emailVerificationService;
    }

    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        Security $security
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('home');
        }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            
            if (empty($user->getRoles())) {
                $user->setRoles(['ROLE_USER']);
            }
            
            $user->setIsVerified(false);
            $user->setIsActive(true);
            
            // Set email as username if not set
            if (!$user->getUsername() && $user->getEmail()) {
                $user->setUsername($user->getEmail());
            }

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            // Send verification email
            $this->emailVerificationService->sendVerificationEmail($user, false);

            $this->addFlash('success', 'Registration successful! Please check your email to verify your account.');
            
            return $this->redirectToRoute('login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/verify/email/{token}', name: 'app_verify_email')]
    public function verifyUserEmail(string $token): Response
    {
        $result = $this->emailVerificationService->verifyEmail($token);
        
        if ($result['success']) {
            $this->addFlash('success', $result['message']);
            return $this->redirectToRoute('login');
        }
        
        $this->addFlash('error', $result['message']);
        return $this->redirectToRoute('app_register');
    }

    #[Route('/resend-verification', name: 'app_resend_verification')]
    public function resendVerification(Request $request): Response
    {
        $email = $request->get('email');
        
        if (!$email) {
            $this->addFlash('error', 'Please provide an email address.');
            return $this->redirectToRoute('login');
        }
        
        $user = $this->emailVerificationService->getUserByEmail($email);
        
        if (!$user) {
            $this->addFlash('error', 'No user found with this email address.');
            return $this->redirectToRoute('login');
        }
        
        if ($user->isVerified()) {
            $this->addFlash('info', 'This email is already verified.');
            return $this->redirectToRoute('login');
        }
        
        $this->emailVerificationService->resendVerificationEmail($user, false);
        $this->addFlash('success', 'Verification email sent! Please check your inbox.');
        
        return $this->redirectToRoute('login');
    }
}