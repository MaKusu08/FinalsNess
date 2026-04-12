<?php

namespace App\Controller;

use App\Entity\AdminRooms;
use App\Form\AdminRoomsType;
use App\Repository\AdminRoomsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Service\ActivityLogger;

#[Route('/admin/rooms')]
#[IsGranted('ROLE_ADMIN')]
#[IsGranted('ROLE_STAFF')]
class AdminRoomsController extends AbstractController
{
    #[Route('', name: 'app_admin_rooms_index', methods: ['GET'])]
    public function index(AdminRoomsRepository $repository): Response
    {
        $user = $this->getUser();

        if ($this->isGranted('ROLE_ADMIN')) {
            $rooms = $repository->findAll();
        } else {
            $rooms = $repository->findByOwner($user);
        }

        return $this->render('admin_rooms/index.html.twig', [
            'admin_rooms' => $rooms,
        ]);
    }

    #[Route('/new', name: 'app_admin_rooms_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, ActivityLogger $logger): Response
    {
        $room = new AdminRooms();
        $form = $this->createForm(AdminRoomsType::class, $room);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $room->setCreatedBy($this->getUser());

            $em->persist($room);
            $em->flush();

            $logger->log('Created Room: '. $room->getRoomName());

            return $this->redirectToRoute('app_admin_rooms_index');
        }

        return $this->render('admin_rooms/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_rooms_show', methods: ['GET'])]
    public function show(AdminRooms $room): Response
    {
        if (
            $this->isGranted('ROLE_STAFF') &&
            $room->getCreatedBy() !== $this->getUser()
        ) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('admin_rooms/show.html.twig', [
            'admin_room' => $room,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_rooms_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, AdminRooms $room, EntityManagerInterface $em): Response
    {
        if (
            $this->isGranted('ROLE_STAFF') &&
            $room->getCreatedBy() !== $this->getUser()
        ) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(AdminRoomsType::class, $room);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('app_admin_rooms_index');
        }

        return $this->render('admin_rooms/edit.html.twig', [
            'form' => $form,
            'admin_room' => $room,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_rooms_delete', methods: ['POST'])]
    public function delete(Request $request, AdminRooms $room, EntityManagerInterface $em): Response
    {
        if (
            $this->isGranted('ROLE_STAFF') &&
            $room->getCreatedBy() !== $this->getUser()
        ) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete'.$room->getId(), $request->request->get('_token'))) {
            $em->remove($room);
            $em->flush();
        }

        return $this->redirectToRoute('app_admin_rooms_index');
    }
}