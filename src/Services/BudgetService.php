<?php

namespace App\Services;

use App\Entity\User;
use App\Repository\BudgetRepository;
use App\Repository\TransactionRepository;

class BudgetService
{
    public function __construct(
        private BudgetRepository $budgetRepository,
        private TransactionRepository $transactionRepository
    ) {}

    /**
     * Obtenir les statistiques pour une plage de dates
     */
    public function getMonthlyStatisticsByDateRange(User $user, \DateTimeImmutable $startDate, \DateTimeImmutable $endDate): array
    {
        $transactions = $this->transactionRepository->findByUserAndDateRange($user, $startDate, $endDate);

        $income = 0;
        $expense = 0;
        $expensesByCategory = [];
        $incomesByCategory = [];

        foreach ($transactions as $t) {
            $catName = $t->getCategory()->getName();
            if ($t->getType() === 'income') {
                $income += $t->getAmount();
                $incomesByCategory[$catName] = ($incomesByCategory[$catName] ?? 0) + $t->getAmount();
            } else {
                $expense += $t->getAmount();
                $expensesByCategory[$catName] = ($expensesByCategory[$catName] ?? 0) + $t->getAmount();
            }
        }

        $budgets = $this->budgetRepository->findByUserAndDateRange($user, $startDate, $endDate);
        $budgetUsage = [];

        foreach ($budgets as $budget) {
            $spent = $expensesByCategory[$budget->getCategoryName()] ?? 0;
            $amount = $budget->getAmount();
            $budgetUsage[] = [
                'budget' => $budget,
                'spent' => $spent,
                'remaining' => $amount - $spent,
                'percentage' => $amount > 0 ? ($spent / $amount) * 100 : 0,
            ];
        }

        $balance = $income - $expense;

        return [
            'balance' => [
                'income' => $income,
                'expense' => $expense,
                'balance' => $balance,
            ],
            'expensesByCategory' => $expensesByCategory,
            'incomesByCategory' => $incomesByCategory,
            'budgetUsage' => $budgetUsage, // <-- ajouté
        ];
    }

    /**
     * Génère un rapport complet pour une plage de dates
     */
    public function generateMonthlyReportByDateRange(User $user, \DateTimeImmutable $startDate, \DateTimeImmutable $endDate): array
    {
        $statistics = $this->getMonthlyStatisticsByDateRange($user, $startDate, $endDate);

        $transactions = $this->transactionRepository->findByUserAndDateRange($user, $startDate, $endDate);

        $totalTransactions = count($transactions);
        $totalExpense = array_sum(array_map(fn($t) => $t->getAmount(), array_filter($transactions, fn($t) => $t->getType() === 'expense')));
        $averageExpense = $totalTransactions > 0 ? $totalExpense / $totalTransactions : 0;

        $expensesByCategory = [];
        foreach ($transactions as $t) {
            if ($t->getType() === 'expense') {
                $catName = $t->getCategory()->getName();
                $expensesByCategory[$catName] = ($expensesByCategory[$catName] ?? 0) + $t->getAmount();
            }
        }

        $topExpenseCategory = null;
        $maxExpense = 0;
        foreach ($expensesByCategory as $category => $amount) {
            if ($amount > $maxExpense) {
                $maxExpense = $amount;
                $topExpenseCategory = $category;
            }
        }

        return [
            'statistics' => $statistics,
            'transactions' => $transactions,
            'metrics' => [
                'totalTransactions' => $totalTransactions,
                'averageExpense' => $averageExpense,
                'topExpenseCategory' => $topExpenseCategory,
                'topExpenseAmount' => $maxExpense,
            ],
        ];
    }

    /**
     * Obtient l'évolution mensuelle sur 12 mois
     */
    public function getMonthlyTrend(User $user): array
    {
        $trend = [];
        $now = new \DateTimeImmutable('first day of this month');

        for ($i = 11; $i >= 0; $i--) {
            $start = $now->modify("-$i months");
            $end = $start->modify('first day of next month');

            $transactions = $this->transactionRepository->findByUserAndDateRange($user, $start, $end);

            $income = 0;
            $expense = 0;

            foreach ($transactions as $t) {
                if ($t->getType() === 'income') {
                    $income += $t->getAmount();
                } else {
                    $expense += $t->getAmount();
                }
            }

            $trend[$start->format('Y-m')] = [
                'month' => $start->format('Y-m'),
                'income' => $income,
                'expense' => $expense,
                'balance' => $income - $expense,
            ];
        }

        return array_values($trend);
    }
}
