<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Repository\UserRepository;
use App\Repository\ActivityLogRepository;
use App\Repository\AdminMoviesRepository;
use App\Repository\AdminReservationsRepository;
use Symfony\Component\HttpFoundation\Response;

#[Route('/admin/dashboard')]
#[IsGranted('ROLE_ADMIN', 'ROLE_STAFF')]
class AdminDashboardController extends AbstractController
{
    #[Route('', name: 'admin_dashboard')]
    public function index(
        UserRepository $userRepo,
        ActivityLogRepository $logRepo,
        AdminMoviesRepository $movieRepo,
        AdminReservationsRepository $reservationRepo
    ): Response {

        /* ============================
           STAFF DASHBOARD
        ============================ */
        if ($this->isGranted('ROLE_STAFF') && !$this->isGranted('ROLE_ADMIN')) {

            $reservations = $reservationRepo->findBy(
            [],
            ['Reservation_Date' => 'DESC'],
            5
        );

        $totalReservations = $reservationRepo->count([]);

        $todayStart = new \DateTimeImmutable('today');
        $todayEnd   = new \DateTimeImmutable('tomorrow');

        $todayReservations = (int) $reservationRepo->createQueryBuilder('r')
           ->select('COUNT(r.id)')
           ->where('r.Reservation_Date >= :start')
           ->andWhere('r.Reservation_Date < :end')
           ->setParameter('start', $todayStart)
           ->setParameter('end', $todayEnd)
           ->getQuery()
           ->getSingleScalarResult();

        $pendingPayments = $reservationRepo->count([
           'Payment_Status' => 'Pending'
        ]);

        return $this->render('admin/dashboard.html.twig', [
        'reservations'       => $reservations,
        'totalReservations'  => $totalReservations,
        'todayReservations'  => $todayReservations,
        'pendingPayments'    => $pendingPayments,
           ]);
        }

        /* ============================
           ADMIN DASHBOARD
        ============================ */
        return $this->render('admin/dashboard.html.twig', [
            'userCount'  => $userRepo->count([]),
            'logCount'   => $logRepo->countLogs(),
            'movieCount' => $movieRepo->count([]),
            'logs'       => $logRepo->findLatestLogs(10),
        ]);
    }
}