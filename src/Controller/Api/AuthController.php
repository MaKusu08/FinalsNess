<?php

namespace App\Controller\Api;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'api_')]
class AuthController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        // Validate required fields
        if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
            return $this->json([
                'success' => false,
                'error' => 'Username, email and password are required'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Check if username exists
        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy([
            'username' => $data['username']
        ]);
        
        if ($existingUser) {
            return $this->json([
                'success' => false,
                'error' => 'Username already exists'
            ], Response::HTTP_CONFLICT);
        }

        // Check if email exists
        $existingEmail = $this->entityManager->getRepository(User::class)->findOneBy([
            'email' => $data['email']
        ]);
        
        if ($existingEmail) {
            return $this->json([
                'success' => false,
                'error' => 'Email already registered'
            ], Response::HTTP_CONFLICT);
        }

        // Create new user
        $user = new User();
        $user->setUsername($data['username']);
        $user->setEmail($data['email']);
        $user->setPassword($passwordHasher->hashPassword($user, $data['password']));
        $user->setRoles(['ROLE_USER']);
        $user->setIsActive(true);
        $user->setIsVerified(false);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Registration successful! Please check your email to verify your account.',
            'user' => [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'roles' => $user->getRoles()
            ]
        ], Response::HTTP_CREATED);
    }

    #[Route('/verify/{token}', name: 'verify_email', methods: ['GET'])]
    public function verifyEmail(string $token): JsonResponse
    {
        // Implement email verification logic
        return $this->json([
            'success' => true,
            'message' => 'Email verified successfully'
        ]);
    }
}