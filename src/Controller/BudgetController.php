<?php

namespace App\Controller;

use App\Entity\Budget;
use App\Repository\BudgetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Attribute\Route as AttributeRoute;

#[AttributeRoute('/budget')]
class BudgetController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private BudgetRepository $budgetRepository
    ) {}

    #[AttributeRoute('/', name: 'app_budget_index')]
    public function index(Request $request): Response
    {
        $user = $this->getUser();

        $month = $request->query->getInt('month', (int) date('m'));
        $year = $request->query->getInt('year', (int) date('Y'));

        $budgets = $this->budgetRepository->findByUserAndMonth($user, $month, $year);

        return $this->render('budget/index.html.twig', [
            'budgets' => $budgets,
            'currentMonth' => $month,
            'currentYear' => $year,
        ]);
    }

    #[AttributeRoute('/new', name: 'app_budget_new')]
    public function new(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $budget = new Budget();
            $budget->setUser($this->getUser());
            $budget->setName($request->request->get('name'));
            $budget->setDescription($request->request->get('description'));
            $budget->setAmount($request->request->get('amount'));
            $budget->setMonth($request->request->getInt('month'));
            $budget->setYear($request->request->getInt('year'));
            $budget->setCategoryName($request->request->get('category_name'));

            $this->entityManager->persist($budget);
            $this->entityManager->flush();

            $this->addFlash('success', 'Budget créé avec succès!');
            return $this->redirectToRoute('app_budget_index');
        }

        return $this->render('budget/new.html.twig', [
            'currentMonth' => (int) date('m'),
            'currentYear' => (int) date('Y'),
        ]);
    }

    #[AttributeRoute('/{id}/edit', name: 'app_budget_edit')]
    public function edit(Request $request, Budget $budget): Response
    {
        $user = $this->getUser();

        if ($budget->getUser() !== $user) {
            throw $this->createAccessDeniedException();
        }

        if ($request->isMethod('POST')) {
            $budget->setName($request->request->get('name'));
            $budget->setDescription($request->request->get('description'));
            $budget->setAmount($request->request->get('amount'));
            $budget->setMonth($request->request->getInt('month'));
            $budget->setYear($request->request->getInt('year'));
            $budget->setCategoryName($request->request->get('category_name'));
            $budget->setUpdatedAt(new \DateTimeImmutable());

            $this->entityManager->flush();

            $this->addFlash('success', 'Budget modifié avec succès!');
            return $this->redirectToRoute('app_budget_index');
        }

        return $this->render('budget/edit.html.twig', [
            'budget' => $budget,
        ]);
    }

    #[AttributeRoute('/{id}/delete', name: 'app_budget_delete', methods: ['POST'])]
    public function delete(Request $request, Budget $budget): Response
    {
        $user = $this->getUser();

        if ($budget->getUser() !== $user) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete' . $budget->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($budget);
            $this->entityManager->flush();

            $this->addFlash('success', 'Budget supprimé avec succès!');
        }

        return $this->redirectToRoute('app_budget_index');
    }
}