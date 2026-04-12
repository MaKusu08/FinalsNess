<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[Route('/admin/account')]
#[IsGranted('ROLE_ADMIN')]
class AdminAccountController extends AbstractController
{
    #[Route('', name: 'admin_account')]
    public function profile()
    {
        return $this->render('admin/account/profile.html.twig', [
            'user' => $this->getUser()
        ]);
    }

    #[Route('/change-password', name: 'admin_change_password')]
    public function changePassword(
        Request $request,
        UserPasswordHasherInterface $hasher
    ) {
        if ($request->isMethod('POST')) {
            $user = $this->getUser();
            $hashed = $hasher->hashPassword(
                $user,
                $request->request->get('password')
            );

            $user->setPassword($hashed);
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'Password updated');
        }

        return $this->render('admin/account/change_password.html.twig');
    }
}
