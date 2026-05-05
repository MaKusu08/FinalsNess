<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'stats' => $this->getStats(),
        ]);
    }

    #[Route('/about', name: 'app_about')]
    public function about(): Response
    {
        return $this->render('about/index.html.twig');
    }

    #[Route('/rooms', name: 'app_rooms')]
    public function rooms(): Response
    {
        return $this->render('rooms/index.html.twig', [
            'rooms' => $this->getRooms(),
        ]);
    }

    #[Route('/packages', name: 'app_packages')]
    public function packages(): Response
    {
        return $this->render('packages/index.html.twig', [
            'products' => $this->getProducts(),
        ]);
    }

    #[Route('/team', name: 'app_team')]
    public function team(): Response
    {
        return $this->render('team/index.html.twig', [
            'admins' => $this->getAdmins(),
        ]);
    }

    #[Route('/contact', name: 'app_contact')]
    public function contact(): Response
    {
        return $this->render('contact/index.html.twig', [
            'rooms'      => $this->getRooms(),
            'time_slots' => $this->getTimeSlots(),
        ]);
    }

    #[Route('/booking', name: 'app_booking', methods: ['POST'])]
    public function booking(Request $request): Response
    {
        $data = [
            'first_name' => $request->request->get('first_name'),
            'last_name'  => $request->request->get('last_name'),
            'email'      => $request->request->get('email'),
            'phone'      => $request->request->get('phone'),
            'date'       => $request->request->get('date'),
            'time_slot'  => $request->request->get('time_slot'),
            'room'       => $request->request->get('room'),
            'message'    => $request->request->get('message'),
        ];

        // TODO: Save to database, send email, etc.
        $this->addFlash('success', 'Your booking request has been received! We will contact you within the hour.');

        return $this->redirectToRoute('app_contact');
    }

    // ========== HELPER METHODS ==========
    
    private function getRooms(): array
    {
        return [
            [
                'number'   => 'I',
                'name'     => 'The Rouge',
                'tag'      => '2–6 Guests',
                'desc'     => 'Intimate and passionate. Deep crimson tones, velvet seating, and a curated cocktail menu perfect for romantic evenings and small celebrations.',
                'badge'    => 'Most Popular',
                'class'    => 'tc1',
            ],
            [
                'number'   => 'II',
                'name'     => 'The Noir',
                'tag'      => '4–10 Guests',
                'desc'     => 'Classic elegance redefined. Midnight blue walls, brass fixtures, and IMAX-grade projection for the cinephile who demands perfection.',
                'badge'    => null,
                'class'    => 'tc2',
            ],
            [
                'number'   => 'III',
                'name'     => 'The Grand',
                'tag'      => '8–20 Guests',
                'desc'     => 'Opulent and commanding. For premieres, corporate events, and celebrations that deserve the full Lumière treatment.',
                'badge'    => 'Premium',
                'class'    => 'tc3',
            ],
        ];
    }

    private function getProducts(): array
    {
        return [
            [
                'icon'   => '♥',
                'tag'    => 'Romance',
                'name'   => 'Date Night Package',
                'desc'   => 'The Rouge room · 2 hrs · Rose setup · Sparkling wine · Snack platter for 2.',
                'price'  => '₱2,500',
                'unit'   => '/ session',
                'class'  => 'pt1',
            ],
            [
                'icon'   => '★',
                'tag'    => 'Celebration',
                'name'   => 'Birthday Premiere',
                'desc'   => 'The Noir room · 3 hrs · Custom banner · Cake service · Up to 10 guests.',
                'price'  => '₱4,200',
                'unit'   => '/ session',
                'class'  => 'pt2',
            ],
            [
                'icon'   => '◆',
                'tag'    => 'Corporate',
                'name'   => 'Executive Screening',
                'desc'   => 'The Grand room · 4 hrs · AV setup · Catering · Up to 20 guests.',
                'price'  => '₱7,500',
                'unit'   => '/ session',
                'class'  => 'pt3',
            ],
            [
                'icon'   => '▲',
                'tag'    => 'Classic',
                'name'   => 'Solo & Duo Session',
                'desc'   => 'Any room · 2 hrs · Popcorn & drinks for 2 · Full streaming access.',
                'price'  => '₱1,500',
                'unit'   => '/ session',
                'class'  => 'pt4',
            ],
            [
                'icon'   => '✦',
                'tag'    => 'Add-On',
                'name'   => 'Luxury Concessions',
                'desc'   => 'Gourmet popcorn, charcuterie board, cocktail set, and soft drinks for your party.',
                'price'  => '₱850',
                'unit'   => '/ set',
                'class'  => 'pt5',
            ],
            [
                'icon'   => '◉',
                'tag'    => 'Premium Add-On',
                'name'   => 'Themed Room Setup',
                'desc'   => 'Custom floral, lighting theme, printed props, and personalized welcome message.',
                'price'  => '₱1,200',
                'unit'   => '/ setup',
                'class'  => 'pt6',
            ],
        ];
    }

    private function getAdmins(): array
    {
        return [
            [
                'initial' => 'M',
                'name'    => 'Marcus D. Ege',
                'role'    => 'Founder & CEO',
                'bio'     => 'Visionary behind Lumière with 10 years in luxury hospitality and an obsession for great cinema.',
                'class'   => 'av1',
            ],
            [
                'initial' => 'M',
                'name'    => 'Marco Santos',
                'role'    => 'Head of Operations',
                'bio'     => 'Ensures every room is flawless from first booking to final credits. Detail is his superpower.',
                'class'   => 'av2',
            ],
            [
                'initial' => 'I',
                'name'    => 'Isabella Cruz',
                'role'    => 'Creative Director',
                'bio'     => 'Curates room aesthetics, packages, and experiences. She turns concepts into unforgettable moments.',
                'class'   => 'av3',
            ],
            [
                'initial' => 'R',
                'name'    => 'Rafael Lim',
                'role'    => 'Tech & AV Lead',
                'bio'     => 'Guarantees perfect projection, audio, and every technical detail behind the Lumière experience.',
                'class'   => 'av4',
            ],
        ];
    }

    private function getStats(): array
    {
        return [
            ['num' => '3',    'label' => 'Private Rooms'],
            ['num' => '4K',   'label' => 'Laser Projection'],
            ['num' => '120"', 'label' => 'Screen Size'],
            ['num' => '★4.9', 'label' => 'Guest Rating'],
            ['num' => '500+', 'label' => 'Events Hosted'],
        ];
    }

    private function getTimeSlots(): array
    {
        return ['10:00 AM', '1:00 PM', '4:00 PM', '7:00 PM', '10:00 PM'];
    }
}