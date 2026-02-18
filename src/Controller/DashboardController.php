<?php

namespace App\Controller;

use App\Services\BudgetService;
use App\Repository\TransactionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route as AttributeRoute;

class DashboardController extends AbstractController
{
    public function __construct(
        private BudgetService $budgetService,
        private TransactionRepository $transactionRepository
    ) {}

    #[AttributeRoute('/', name: 'app_dashboard')]
    public function index(Request $request): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $month = $request->query->getInt('month', (int) date('m'));
        $year = $request->query->getInt('year', (int) date('Y'));

        $startDate = new \DateTimeImmutable("$year-$month-01 00:00:00");
        $endDate = $startDate->modify('first day of next month');

        $statistics = $this->budgetService->getMonthlyStatisticsByDateRange($user, $startDate, $endDate);

        $recentTransactions = $this->transactionRepository->findRecentByUser($user, 5);

        $monthlyTrend = $this->budgetService->getMonthlyTrend($user);

        return $this->render('dashboard/index.html.twig', [
            'user' => $user,
            'currentMonth' => $month,
            'currentYear' => $year,
            'statistics' => $statistics,
            'recentTransactions' => $recentTransactions,
            'monthlyTrend' => $monthlyTrend,
        ]);
    }

    #[AttributeRoute('/dashboard/report', name: 'app_dashboard_report')]
    public function report(Request $request): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $month = $request->query->getInt('month', (int) date('m'));
        $year = $request->query->getInt('year', (int) date('Y'));

        $startDate = new \DateTimeImmutable("$year-$month-01 00:00:00");
        $endDate = $startDate->modify('first day of next month');

        $report = $this->budgetService->generateMonthlyReportByDateRange($user, $startDate, $endDate);

        return $this->render('dashboard/report.html.twig', [
            'month' => $month,
            'year' => $year,
            'report' => $report,
        ]);
    }
}
