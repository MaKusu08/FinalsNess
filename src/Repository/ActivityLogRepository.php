<?php

namespace App\Repository;

use App\Entity\ActivityLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ActivityLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ActivityLog::class);
    }

    /**
     * Get all logs ordered by newest first
     */
    public function findLatestLogs(int $limit = 50): array
    {
        return $this->createQueryBuilder('l')
            ->orderBy('l.createdAt', 'DESC') // ✅ FIX
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Count total logs
     */
    public function countLogs(): int
    {
        return (int) $this->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Get logs by action type
     */
    public function findByAction(string $action): array
    {
        return $this->createQueryBuilder('l')
            ->where('l.action LIKE :action')
            ->setParameter('action', '%' . $action . '%')
            ->orderBy('l.createdAt', 'DESC') // ✅ FIX
            ->getQuery()
            ->getResult();
    }

    /**
     * Get logs by username
     */
    public function findByUsername(string $username): array
    {
        return $this->createQueryBuilder('l')
            ->where('l.username = :username')
            ->setParameter('username', $username)
            ->orderBy('l.createdAt', 'DESC') // ✅ FIX
            ->getQuery()
            ->getResult();
    }
}
