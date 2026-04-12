<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\ActivityLog;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[Route('/admin/users')]
#[IsGranted('ROLE_ADMIN')]
class UserCrudController extends AbstractController
{
    #[Route('', name: 'admin_users')]
    public function index(EntityManagerInterface $em)
    {
        return $this->render('admin_users/index.html.twig', [
            'users' => $em->getRepository(User::class)->findAll()
        ]);
    }

    #[Route('/create', name: 'admin_users_create')]
    public function create(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher)
    {
        if ($request->isMethod('POST')) {
            $user = new User();
            $user->setUsername($request->request->get('username'));
            $user->setRoles([$request->request->get('role')]);
            $user->setPassword(
                $hasher->hashPassword($user, $request->request->get('password'))
            );

            $em->persist($user);

            $log = (new ActivityLog())
                ->setUsername($this->getUser()->getUserIdentifier())
                ->setRole('ROLE_ADMIN')
                ->setAction('Created user: '.$user->getUsername());

            $em->persist($log);
            $em->flush();

            $this->addFlash('success', 'User created successfully');
            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin_users/create.html.twig');
    }

    #[Route('/{id}/edit', name: 'admin_users_edit')]
    public function edit(User $user, Request $request, EntityManagerInterface $em)
    {
        if ($request->isMethod('POST')) {
            $user->setRoles([$request->request->get('role')]);

            $log = (new ActivityLog())
                ->setUsername($this->getUser()->getUserIdentifier())
                ->setRole('ROLE_ADMIN')
                ->setAction('Updated user: '.$user->getUsername());

            $em->persist($log);
            $em->flush();

            $this->addFlash('success', 'User updated');
            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin_users/edit.html.twig', compact('user'));
    }

    #[Route('/{id}/delete', name: 'admin_users_delete')]
    public function delete(User $user, EntityManagerInterface $em)
    {
        $em->remove($user);

        $log = (new ActivityLog())
            ->setUsername($this->getUser()->getUserIdentifier())
            ->setRole('ROLE_ADMIN')
            ->setAction('Deleted user: '.$user->getUsername());

        $em->persist($log);
        $em->flush();

        $this->addFlash('success', 'User deleted');
        return $this->redirectToRoute('admin_users');
    }
}
