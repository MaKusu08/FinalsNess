<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[Route('/staff')]
#[IsGranted('ROLE_STAFF')]
class StaffProfileController extends AbstractController
{
    #[Route('/profile', name: 'staff_profile')]
    public function profile()
    {
        return $this->render('staff/profile.html.twig', [
            'user' => $this->getUser(),
        ]);
    }
}