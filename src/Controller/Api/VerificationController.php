<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Service\EmailVerificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;

#[Route('/api', name: 'api_')]
class VerificationController extends AbstractController
{
    private EmailVerificationService $emailVerificationService;
    private EntityManagerInterface $entityManager;

    public function __construct(
        EmailVerificationService $emailVerificationService,
        EntityManagerInterface $entityManager
    ) {
        $this->emailVerificationService = $emailVerificationService;
        $this->entityManager = $entityManager;
    }

    #[Route('/register', name: 'register', methods: ['POST'])]
    public function apiRegister(Request $request, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $validator = Validation::createValidator();
        $emailConstraint = new Assert\Email();
        $emailErrors = $validator->validate($data['email'] ?? '', $emailConstraint);
        
        if (count($emailErrors) > 0) {
            return $this->json(['error' => 'Invalid email address'], Response::HTTP_BAD_REQUEST);
        }
        
        // Check if user exists
        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);
        if ($existingUser) {
            return $this->json(['error' => 'User already exists'], Response::HTTP_CONFLICT);
        }
        
        // Create new user
        $user = new User();
        $user->setEmail($data['email']);
        $user->setUsername($data['email']);
        $user->setFirstName($data['first_name'] ?? null);
        $user->setLastName($data['last_name'] ?? null);
        $user->setRoles(['ROLE_USER']);
        $user->setIsActive(true);
        $user->setIsVerified(false);
        
        if (isset($data['password'])) {
            $user->setPassword($passwordHasher->hashPassword($user, $data['password']));
        }
        
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        
        // Send verification email
        $this->emailVerificationService->sendVerificationEmail($user, true);
        
        return $this->json([
            'success' => true,
            'message' => 'User registered. Please verify your email.',
            'user_id' => $user->getId()
        ], Response::HTTP_CREATED);
    }

    #[Route('/verify-email', name: 'verify_email', methods: ['POST'])]
    public function apiVerifyEmail(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['token'])) {
            return $this->json(['error' => 'Token is required'], Response::HTTP_BAD_REQUEST);
        }
        
        $result = $this->emailVerificationService->verifyEmail($data['token']);
        
        if ($result['success']) {
            return $this->json([
                'success' => true,
                'message' => $result['message'],
                'user_id' => $result['user']->getId()
            ], Response::HTTP_OK);
        }
        
        return $this->json(['error' => $result['message']], Response::HTTP_BAD_REQUEST);
    }

    #[Route('/resend-verification', name: 'resend_verification', methods: ['POST'])]
    public function apiResendVerification(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['email'])) {
            return $this->json(['error' => 'Email is required'], Response::HTTP_BAD_REQUEST);
        }
        
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);
        
        if (!$user) {
            return $this->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }
        
        if ($user->isVerified()) {
            return $this->json(['error' => 'Email already verified'], Response::HTTP_BAD_REQUEST);
        }
        
        $this->emailVerificationService->sendVerificationEmail($user, true);
        
        return $this->json([
            'success' => true,
            'message' => 'Verification email sent'
        ], Response::HTTP_OK);
    }
}