<?php

namespace App\Controller;

use App\Entity\AdminMovies;
use App\Form\AdminMoviesType;
use App\Repository\AdminMoviesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Service\ActivityLogger;

#[Route('/admin/movies')]
#[IsGranted('ROLE_ADMIN')]
#[IsGranted('ROLE_STAFF')]
final class AdminMoviesController extends AbstractController
{
    #[Route(name: 'app_admin_movies_index', methods: ['GET'])]
    public function index(AdminMoviesRepository $adminMoviesRepository): Response
    {
        $user = $this->getUser();

        if ($this->isGranted('ROLE_ADMIN')) {
            $movies = $adminMoviesRepository->findAll();
        } else {
            $movies = $adminMoviesRepository->findBy([
                'createdBy' => $user
            ]);
        }

        return $this->render('admin_movies/index.html.twig', [
            'admin_movies' => $movies,
        ]);
    }

    #[Route('/new', name: 'app_admin_movies_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
        ActivityLogger $logger

    ): Response {
        $adminMovie = new AdminMovies();
        $form = $this->createForm(AdminMoviesType::class, $adminMovie);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {

            $imageFile = $form->get('Movie_Image')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                $imageFile->move(
                    $this->getParameter('movies_upload_dir'),
                    $newFilename
                );

                $adminMovie->setMovieImage($newFilename);
            }

            // ✅ OWNER SET HERE
            $adminMovie->setCreatedBy($this->getUser());

            $entityManager->persist($adminMovie);
            $entityManager->flush();

            $logger->log('Created Movie: ' . $adminMovie->getMovieName());

            return $this->redirectToRoute('app_admin_movies_index');
        }

        return $this->render('admin_movies/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_movies_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        AdminMovies $adminMovie,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
        ActivityLogger $logger
    ): Response {
        // 🔒 STAFF CAN ONLY EDIT OWN MOVIES
        if (
            $this->isGranted('ROLE_STAFF') &&
            $adminMovie->getCreatedBy() !== $this->getUser()
        ) {
            throw $this->createAccessDeniedException();
        }

        $existingImage = $adminMovie->getMovieImage();

        $form = $this->createForm(AdminMoviesType::class, $adminMovie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $imageFile = $form->get('Movie_Image')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                $imageFile->move(
                    $this->getParameter('movies_upload_dir'),
                    $newFilename
                );

                $adminMovie->setMovieImage($newFilename);
            } else {
                $adminMovie->setMovieImage($existingImage);
            }

            $entityManager->flush();

            $logger->log('Updated Movie: ' . $adminMovie->getMovieName());

            return $this->redirectToRoute('app_admin_movies_index');
        }

        return $this->render('admin_movies/edit.html.twig', [
            'form' => $form,
            'admin_movie' => $adminMovie,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_movies_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        AdminMovies $adminMovie,
        EntityManagerInterface $entityManager,
        ActivityLogger $logger
    ): Response {
        // 🔒 STAFF CAN ONLY DELETE OWN MOVIES
        if (
            $this->isGranted('ROLE_STAFF') &&
            $adminMovie->getCreatedBy() !== $this->getUser()
        ) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete'.$adminMovie->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($adminMovie);
            $entityManager->flush();

            $logger->log('Deleted Movie: ' . $adminMovie->getMovieName());
        }
        

        return $this->redirectToRoute('app_admin_movies_index');
    }
}