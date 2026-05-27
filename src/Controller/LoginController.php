<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    #[Route('/login', name: 'login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // If user is already logged in, redirect based on role
        if ($this->getUser()) {
            $user = $this->getUser();

            if (in_array('ROLE_ADMIN', $user->getRoles())) {
                return $this->redirectToRoute('admin_dashboard');
            }

            if (in_array('ROLE_STAFF', $user->getRoles())) {
                return $this->redirectToRoute('staff_dashboard');
            }

            if (in_array('ROLE_CUSTOMER', $user->getRoles())) {
                return $this->redirectToRoute('home');
            }

            return $this->redirectToRoute('home');
        }

        // Get login error if any
        $error = $authenticationUtils->getLastAuthenticationError();

        // Last username entered
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/logout', name: 'logout')]
    public function logout(): void
    {
        throw new \LogicException('This method should never be called directly.');
    }
}