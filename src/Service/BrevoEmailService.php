<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment as TwigEnvironment;

class BrevoEmailService
{
    private MailerInterface $mailer;
    private TwigEnvironment $twig;
    private string $senderEmail;
    private string $senderName;

    public function __construct(
        MailerInterface $mailer,
        TwigEnvironment $twig,
        string $senderEmail,
        string $senderName
    ) {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->senderEmail = $senderEmail;
        $this->senderName = $senderName;
    }

    public function sendWelcomeEmail(string $toEmail, string $username): void
    {
        try {
            $html = $this->twig->render('emails/welcome.html.twig', [
                'username' => $username
            ]);

            $email = (new Email())
                ->from($this->senderEmail, $this->senderName)
                ->to($toEmail)
                ->subject('Welcome to Lumière Entertainment!')
                ->html($html)
                ->text(
                    "Welcome $username!\n\n" .
                    "Thank you for joining Lumière Entertainment via Google login.\n\n" .
                    "You can now access your staff dashboard.\n\n" .
                    "Best regards,\n" .
                    $this->senderName
                );

            $this->mailer->send($email);
        } catch (\Exception $e) {
            // Log error but don't break the application
            error_log('Failed to send welcome email: ' . $e->getMessage());
        }
    }

    public function sendGoogleLoginAlert(string $toEmail, string $username): void
    {
        try {
            $email = (new Email())
                ->from($this->senderEmail, $this->senderName)
                ->to($toEmail)
                ->subject('New Google Login Detected')
                ->html(
                    "<h1>Hello $username!</h1>
                    <p>You have successfully logged in using your Google account.</p>
                    <p>If this wasn't you, please contact support immediately.</p>
                    <br>
                    <p>Best regards,<br><strong>Lumière Entertainment Team</strong></p>"
                )
                ->text(
                    "Hello $username!\n\n" .
                    "You have successfully logged in using your Google account.\n\n" .
                    "If this wasn't you, please contact support immediately.\n\n" .
                    "Best regards,\nLumière Entertainment Team"
                );

            $this->mailer->send($email);
        } catch (\Exception $e) {
            error_log('Failed to send login alert: ' . $e->getMessage());
        }
    }
}