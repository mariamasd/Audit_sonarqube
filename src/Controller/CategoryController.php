<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Attribute\Route as AttributeRoute;

#[AttributeRoute('/category')]
class CategoryController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CategoryRepository $categoryRepository
    ) {}

    #[AttributeRoute('/', name: 'app_category_index')]
    public function index(): Response
    {
        $user = $this->getUser();
        $categories = $this->categoryRepository->findByUser($user);

        return $this->render('category/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[AttributeRoute('/new', name: 'app_category_new')]
    public function new(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $category = new Category();
            $category->setUser($this->getUser());
            $category->setName($request->request->get('name'));
            $category->setDescription($request->request->get('description'));
            $category->setType($request->request->get('type'));
            $category->setColor($request->request->get('color'));
            $category->setIcon($request->request->get('icon'));

            $this->entityManager->persist($category);
            $this->entityManager->flush();

            $this->addFlash('success', 'Catégorie créée avec succès!');
            return $this->redirectToRoute('app_category_index');
        }

        return $this->render('category/new.html.twig');
    }

    #[AttributeRoute('/{id}/edit', name: 'app_category_edit')]
    public function edit(Request $request, Category $category): Response
    {
        $user = $this->getUser();

        if ($category->getUser() !== $user) {
            throw $this->createAccessDeniedException();
        }

        if ($request->isMethod('POST')) {
            $category->setName($request->request->get('name'));
            $category->setDescription($request->request->get('description'));
            $category->setType($request->request->get('type'));
            $category->setColor($request->request->get('color'));
            $category->setIcon($request->request->get('icon'));

            $this->entityManager->flush();

            $this->addFlash('success', 'Catégorie modifiée avec succès!');
            return $this->redirectToRoute('app_category_index');
        }

        return $this->render('category/edit.html.twig', [
            'category' => $category,
        ]);
    }

    #[AttributeRoute('/{id}/delete', name: 'app_category_delete', methods: ['POST'])]
    public function delete(Request $request, Category $category): Response
    {
        $user = $this->getUser();

        if ($category->getUser() !== $user) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete' . $category->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($category);
            $this->entityManager->flush();

            $this->addFlash('success', 'Catégorie supprimée avec succès!');
        }

        return $this->redirectToRoute('app_category_index');
    }
}
