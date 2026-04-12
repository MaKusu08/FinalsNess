<?php

namespace App\Controller;

use App\Entity\AdminReservations;
use App\Form\AdminReservationsType;
use App\Repository\AdminReservationsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/reservations')]
final class AdminReservationsController extends AbstractController
{
    #[Route(name: 'app_admin_reservations_index', methods: ['GET'])]
    public function index(AdminReservationsRepository $adminReservationsRepository): Response
    {
        return $this->render('admin_reservations/index.html.twig', [
            'admin_reservations' => $adminReservationsRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_admin_reservations_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $adminReservation = new AdminReservations();
        $form = $this->createForm(AdminReservationsType::class, $adminReservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($adminReservation);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_reservations_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin_reservations/new.html.twig', [
            'admin_reservation' => $adminReservation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_reservations_show', methods: ['GET'])]
    public function show(AdminReservations $adminReservation): Response
    {
        return $this->render('admin_reservations/show.html.twig', [
            'admin_reservation' => $adminReservation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_reservations_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, AdminReservations $adminReservation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AdminReservationsType::class, $adminReservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_reservations_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin_reservations/edit.html.twig', [
            'admin_reservation' => $adminReservation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_reservations_delete', methods: ['POST'])]
    public function delete(Request $request, AdminReservations $adminReservation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$adminReservation->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($adminReservation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_reservations_index', [], Response::HTTP_SEE_OTHER);
    }
}
