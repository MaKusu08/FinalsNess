<?php

namespace App\Controller;

use App\Repository\ActivityLogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[Route('/admin/logs')]
#[IsGranted('ROLE_ADMIN')]
class LogsController extends AbstractController
{
    #[Route('', name: 'admin_logs')]
    public function index(ActivityLogRepository $logRepo)
    {
        return $this->render('admin_logs/index.html.twig', [
            'logs' => $logRepo->findLatestLogs(100),
        ]);
    }
}
