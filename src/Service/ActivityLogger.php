<?php

namespace App\Service;

use App\Entity\ActivityLog;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class ActivityLogger
{
    public function __construct(
        private EntityManagerInterface $em,
        private Security $security
    ) {}

    public function log(string $action): void
    {
        $user = $this->security->getUser();

        if (!$user) {
            return;
        }

        $log = new ActivityLog();
        $log->setUsername($user->getUserIdentifier());
        $log->setRole($user->getRoles()[0] ?? 'ROLE_USER');
        $log->setAction($action);

        $this->em->persist($log);
        $this->em->flush();
    }
}