<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class RoleRedirectController extends AbstractController
{
    #[Route('/role-redirect', name: 'role_redirect')]
    public function redirectByRole()
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('admin_dashboard');
        }

        if ($this->isGranted('ROLE_STAFF')) {
            return $this->redirectToRoute('staff_dashboard');
        }

        return $this->redirectToRoute('login');
    }
}
