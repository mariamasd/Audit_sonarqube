<?php

namespace App\Repository;

use App\Entity\Transaction;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Transaction>
 */
class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    /**
     * Helper: crée la plage de dates pour un mois
     */
    private function getMonthDateRange(int $month, int $year): array
    {
        $start = new \DateTimeImmutable(sprintf('%04d-%02d-01 00:00:00', $year, $month));
        $end = $start->modify('+1 month');

        return [$start, $end];
    }

    /**
     * Trouve les transactions d'un utilisateur pour un mois donné
     */
    public function findByUserAndMonth(User $user, int $month, int $year): array
    {
        [$start, $end] = $this->getMonthDateRange($month, $year);

        return $this->createQueryBuilder('t')
            ->andWhere('t.user = :user')
            ->andWhere('t.transactionDate >= :start')
            ->andWhere('t.transactionDate < :end')
            ->setParameter('user', $user)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('t.transactionDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Calcule le total des revenus pour un mois
     */
    public function getTotalIncomeByMonth(User $user, int $month, int $year): float
    {
        [$start, $end] = $this->getMonthDateRange($month, $year);

        $result = $this->createQueryBuilder('t')
            ->select('SUM(t.amount) as total')
            ->andWhere('t.user = :user')
            ->andWhere('t.type = :type')
            ->andWhere('t.transactionDate >= :start')
            ->andWhere('t.transactionDate < :end')
            ->setParameter('user', $user)
            ->setParameter('type', 'income')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getSingleScalarResult();

        return $result ? (float) $result : 0.0;
    }

    /**
     * Calcule le total des dépenses pour un mois
     */
    public function getTotalExpenseByMonth(User $user, int $month, int $year): float
    {
        [$start, $end] = $this->getMonthDateRange($month, $year);

        $result = $this->createQueryBuilder('t')
            ->select('SUM(t.amount) as total')
            ->andWhere('t.user = :user')
            ->andWhere('t.type = :type')
            ->andWhere('t.transactionDate >= :start')
            ->andWhere('t.transactionDate < :end')
            ->setParameter('user', $user)
            ->setParameter('type', 'expense')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getSingleScalarResult();

        return $result ? (float) $result : 0.0;
    }

    /**
     * Obtient les dépenses groupées par catégorie pour un mois
     */
    public function getExpensesByCategory(User $user, int $month, int $year): array
    {
        [$start, $end] = $this->getMonthDateRange($month, $year);

        return $this->createQueryBuilder('t')
            ->select('c.name as categoryName, SUM(t.amount) as total, c.color')
            ->join('t.category', 'c')
            ->andWhere('t.user = :user')
            ->andWhere('t.type = :type')
            ->andWhere('t.transactionDate >= :start')
            ->andWhere('t.transactionDate < :end')
            ->setParameter('user', $user)
            ->setParameter('type', 'expense')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->groupBy('c.id')
            ->orderBy('total', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Obtient les revenus groupés par catégorie pour un mois
     */
    public function getIncomesByCategory(User $user, int $month, int $year): array
    {
        [$start, $end] = $this->getMonthDateRange($month, $year);

        return $this->createQueryBuilder('t')
            ->select('c.name as categoryName, SUM(t.amount) as total, c.color')
            ->join('t.category', 'c')
            ->andWhere('t.user = :user')
            ->andWhere('t.type = :type')
            ->andWhere('t.transactionDate >= :start')
            ->andWhere('t.transactionDate < :end')
            ->setParameter('user', $user)
            ->setParameter('type', 'income')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->groupBy('c.id')
            ->orderBy('total', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * ✅ VERSION CORRIGÉE — évolution des 12 derniers mois
     */
    public function getMonthlyTrend(User $user): array
    {
        $startDate = new \DateTimeImmutable('-11 months');
        $startDate = $startDate->modify('first day of this month')->setTime(0, 0);

        $transactions = $this->createQueryBuilder('t')
            ->andWhere('t.user = :user')
            ->andWhere('t.transactionDate >= :startDate')
            ->setParameter('user', $user)
            ->setParameter('startDate', $startDate)
            ->orderBy('t.transactionDate', 'ASC')
            ->getQuery()
            ->getResult();

        $trend = [];

        for ($i = 0; $i < 12; $i++) {
            $date = (clone $startDate)->modify("+$i month");
            $key = $date->format('Y-m');

            $trend[$key] = [
                'month' => $date->format('m/Y'),
                'income' => 0,
                'expense' => 0,
            ];
        }

        // Grouper les données
        foreach ($transactions as $t) {
            $key = $t->getTransactionDate()->format('Y-m');

            if (!isset($trend[$key])) {
                continue;
            }

            if ($t->getType() === 'income') {
                $trend[$key]['income'] += $t->getAmount();
            } else {
                $trend[$key]['expense'] += $t->getAmount();
            }
        }

        return array_values($trend);
    }

    /**
     * Trouve les dernières transactions
     */
    public function findRecentByUser(User $user, int $limit = 10): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.user = :user')
            ->setParameter('user', $user)
            ->orderBy('t.transactionDate', 'DESC')
            ->addOrderBy('t.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les transactions d'un utilisateur sur une plage de dates
     */
    public function findByUserAndDateRange(User $user, \DateTimeImmutable $start, \DateTimeImmutable $end): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.user = :user')
            ->andWhere('t.transactionDate >= :start')
            ->andWhere('t.transactionDate < :end')
            ->setParameter('user', $user)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('t.transactionDate', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
