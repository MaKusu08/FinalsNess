<?php

namespace App\Repository;

use App\Entity\AdminRooms;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AdminRoomsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdminRooms::class);
    }

    public function findByOwner(User $user): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.createdBy = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }
}