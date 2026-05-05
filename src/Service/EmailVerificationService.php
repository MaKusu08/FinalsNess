<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment as TwigEnvironment;

class EmailVerificationService
{
    private EntityManagerInterface $entityManager;
    private MailerInterface $mailer;
    private TwigEnvironment $twig;
    private UrlGeneratorInterface $urlGenerator;
    private string $senderEmail;
    private string $senderName;

    public function __construct(
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
        TwigEnvironment $twig,
        UrlGeneratorInterface $urlGenerator,
        string $senderEmail,
        string $senderName
    ) {
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->urlGenerator = $urlGenerator;
        $this->senderEmail = $senderEmail;
        $this->senderName = $senderName;
    }

    public function generateVerificationToken(User $user): string
    {
        $token = bin2hex(random_bytes(32));
        $user->setVerificationToken($token);
        $user->setTokenExpiresAt(new \DateTimeImmutable('+24 hours'));
        $this->entityManager->flush();
        return $token;
    }

    public function sendVerificationEmail(User $user, bool $isApi = false): void
    {
        try {
            $token = $this->generateVerificationToken($user);
            
            if ($isApi) {
                $verificationUrl = $this->urlGenerator->generate('api_verify_email', [
                    'token' => $token
                ], UrlGeneratorInterface::ABSOLUTE_URL);
            } else {
                $verificationUrl = $this->urlGenerator->generate('app_verify_email', [
                    'token' => $token
                ], UrlGeneratorInterface::ABSOLUTE_URL);
            }
            
            $userName = $user->getFirstName() ?? $user->getUsername() ?? explode('@', $user->getEmail())[0];
            
            $email = (new Email())
                ->from($this->senderEmail, $this->senderName)
                ->to($user->getEmail())
                ->subject('Verify Your Email - Lumière Entertainment')
                ->html($this->getVerificationEmailHtml($userName, $verificationUrl))
                ->text($this->getVerificationEmailText($userName, $verificationUrl));
            
            $this->mailer->send($email);
        } catch (\Exception $e) {
            error_log('Failed to send verification email: ' . $e->getMessage());
        }
    }

    private function getVerificationEmailHtml(string $userName, string $verificationUrl): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Verify Your Email</title>
        </head>
        <body style='font-family: Arial, sans-serif; background: #0a0a0c; color: #f5f2eb; padding: 40px;'>
            <div style='max-width: 600px; margin: 0 auto; background: #111115; border-radius: 12px; padding: 40px; border: 1px solid #C9A84C;'>
                <h1 style='color: #C9A84C;'>Verify Your Email</h1>
                <p>Hello <strong>$userName</strong>,</p>
                <p>Thank you for registering with <strong>Lumière Entertainment</strong>!</p>
                <p>Please verify your email address by clicking the button below:</p>
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='$verificationUrl' style='display: inline-block; background: #C9A84C; color: #0a0a0c; padding: 12px 30px; text-decoration: none; border-radius: 40px; font-weight: bold;'>Verify Email</a>
                </div>
                <p style='font-size: 12px; color: #8A8070;'>This link expires in 24 hours.</p>
                <hr style='border-color: rgba(201,168,76,0.2); margin: 30px 0;'>
                <p style='font-size: 12px; color: #8A8070;'>Best regards,<br><strong style='color: #C9A84C;'>Lumière Entertainment Team</strong></p>
            </div>
        </body>
        </html>
        ";
    }

    private function getVerificationEmailText(string $userName, string $verificationUrl): string
    {
        return "Hello $userName,\n\n" .
               "Please verify your email by clicking this link: $verificationUrl\n\n" .
               "This link expires in 24 hours.\n\n" .
               "Best regards,\nLumière Entertainment Team";
    }

    public function verifyEmail(string $token): array
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['verificationToken' => $token]);
        
        if (!$user) {
            return ['success' => false, 'message' => 'Invalid verification token.'];
        }
        
        if ($user->isTokenExpired()) {
            return ['success' => false, 'message' => 'Verification token has expired. Please request a new one.'];
        }
        
        if ($user->isVerified()) {
            return ['success' => false, 'message' => 'Email already verified.'];
        }
        
        $user->setIsVerified(true);
        $user->setVerificationToken(null);
        $user->setTokenExpiresAt(null);
        $this->entityManager->flush();
        
        return ['success' => true, 'message' => 'Email verified successfully!', 'user' => $user];
    }

    public function resendVerificationEmail(User $user, bool $isApi = false): void
    {
        $user->setVerificationToken(null);
        $user->setTokenExpiresAt(null);
        $this->entityManager->flush();
        $this->sendVerificationEmail($user, $isApi);
    }

    public function getUserByEmail(string $email): ?User
    {
        return $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
    }
}