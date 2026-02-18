<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Repository\TransactionRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Attribute\Route as AttributeRoute;

#[AttributeRoute('/transaction')]
class TransactionController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TransactionRepository $transactionRepository,
        private CategoryRepository $categoryRepository
    ) {}

    #[AttributeRoute('/', name: 'app_transaction_index')]
    public function index(Request $request): Response
    {
        $user = $this->getUser();

        $month = $request->query->getInt('month', (int) date('m'));
        $year = $request->query->getInt('year', (int) date('Y'));

        $transactions = $this->transactionRepository->findByUserAndMonth($user, $month, $year);

        $income = $this->transactionRepository->getTotalIncomeByMonth($user, $month, $year);
        $expense = $this->transactionRepository->getTotalExpenseByMonth($user, $month, $year);
        $expensesByCategory = $this->transactionRepository->getExpensesByCategory($user, $month, $year);
        $monthlyTrend = $this->transactionRepository->getMonthlyTrend($user);
        $recentTransactions = $this->transactionRepository->findRecentByUser($user);

        $statistics = [
            'balance' => [
                'income' => $income,
                'expense' => $expense,
                'balance' => $income - $expense,
            ],
            'budgetUsage' => [],
            'expensesByCategory' => $expensesByCategory,
        ];

        return $this->render('transaction/index.html.twig', [
            'transactions' => $transactions,
            'currentMonth' => $month,
            'currentYear' => $year,
            'statistics' => $statistics,
            'monthlyTrend' => $monthlyTrend,
            'recentTransactions' => $recentTransactions,
        ]);
    }


    #[AttributeRoute('/new', name: 'app_transaction_new')]
    public function new(Request $request): Response
    {
        $user = $this->getUser();
        $categories = $this->categoryRepository->findByUser($user);

        if ($request->isMethod('POST')) {
            $transaction = new Transaction();
            $transaction->setUser($user);
            $transaction->setTitle($request->request->get('title'));
            $transaction->setDescription($request->request->get('description'));
            $transaction->setAmount($request->request->get('amount'));
            $transaction->setType($request->request->get('type'));
            $transaction->setTransactionDate(new \DateTime($request->request->get('transaction_date')));
            $transaction->setPaymentMethod($request->request->get('payment_method'));
            $transaction->setNotes($request->request->get('notes'));

            $categoryId = $request->request->get('category_id');
            $category = $this->categoryRepository->find($categoryId);
            $transaction->setCategory($category);

            $this->entityManager->persist($transaction);
            $this->entityManager->flush();

            $this->addFlash('success', 'Transaction créée avec succès!');
            return $this->redirectToRoute('app_transaction_index');
        }

        return $this->render('transaction/new.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[AttributeRoute('/{id}/edit', name: 'app_transaction_edit')]
    public function edit(Request $request, Transaction $transaction): Response
    {
        $user = $this->getUser();

        if ($transaction->getUser() !== $user) {
            throw $this->createAccessDeniedException();
        }

        $categories = $this->categoryRepository->findByUser($user);

        if ($request->isMethod('POST')) {
            $transaction->setTitle($request->request->get('title'));
            $transaction->setDescription($request->request->get('description'));
            $transaction->setAmount($request->request->get('amount'));
            $transaction->setType($request->request->get('type'));
            $transaction->setTransactionDate(new \DateTime($request->request->get('transaction_date')));
            $transaction->setPaymentMethod($request->request->get('payment_method'));
            $transaction->setNotes($request->request->get('notes'));
            $transaction->setUpdatedAt(new \DateTimeImmutable());

            $categoryId = $request->request->get('category_id');
            $category = $this->categoryRepository->find($categoryId);
            $transaction->setCategory($category);

            $this->entityManager->flush();

            $this->addFlash('success', 'Transaction modifiée avec succès!');
            return $this->redirectToRoute('app_transaction_index');
        }

        return $this->render('transaction/edit.html.twig', [
            'transaction' => $transaction,
            'categories' => $categories,
        ]);
    }

    #[AttributeRoute('/{id}/delete', name: 'app_transaction_delete', methods: ['POST'])]
    public function delete(Request $request, Transaction $transaction): Response
    {
        $user = $this->getUser();

        if ($transaction->getUser() !== $user) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete' . $transaction->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($transaction);
            $this->entityManager->flush();

            $this->addFlash('success', 'Transaction supprimée avec succès!');
        }

        return $this->redirectToRoute('app_transaction_index');
    }
}
