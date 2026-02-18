<?php

namespace App\Repository;

use App\Entity\Budget;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Budget>
 */
class BudgetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Budget::class);
    }

    /**
     * Trouve les budgets d'un utilisateur pour un mois donné
     */
    public function findByUserAndMonth(User $user, int $month, int $year): array
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.user = :user')
            ->andWhere('b.month = :month')
            ->andWhere('b.year = :year')
            ->setParameter('user', $user)
            ->setParameter('month', $month)
            ->setParameter('year', $year)
            ->orderBy('b.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve tous les budgets d'un utilisateur
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.user = :user')
            ->setParameter('user', $user)
            ->orderBy('b.year', 'DESC')
            ->addOrderBy('b.month', 'DESC')
            ->getQuery()
            ->getResult();
    }
    /**
     * Trouve les budgets d'un utilisateur sur une plage de dates
     */
    public function findByUserAndDateRange(User $user, \DateTimeImmutable $startDate, \DateTimeImmutable $endDate): array
    {
        $startMonth = (int) $startDate->format('m');
        $startYear = (int) $startDate->format('Y');
        $endMonth = (int) $endDate->format('m');
        $endYear = (int) $endDate->format('Y');

        return $this->createQueryBuilder('b')
            ->andWhere('b.user = :user')
            ->andWhere('(b.year > :startYear OR (b.year = :startYear AND b.month >= :startMonth))')
            ->andWhere('(b.year < :endYear OR (b.year = :endYear AND b.month < :endMonth))')
            ->setParameter('user', $user)
            ->setParameter('startYear', $startYear)
            ->setParameter('startMonth', $startMonth)
            ->setParameter('endYear', $endYear)
            ->setParameter('endMonth', $endMonth)
            ->orderBy('b.year', 'ASC')
            ->addOrderBy('b.month', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
