<?php

namespace App\Controller;

use App\Entity\Category;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CategoryRepository;
use Psr\Log\LoggerInterface;

final class CategoryController extends AbstractController
{
    #[Route('/categories', name: 'category_index')]
    public function allCategories(CategoryRepository $repository, LoggerInterface $logger): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        $categories = $repository->findAll();
        $logger->info('Categories fetched: {categories}', ['categories'=> $categories]);

        return $this->render('category/index.html.twig', [
            'categories'=> $categories,
        ]);
    }

    #[Route('/category/{id<\d+>}', name: 'category_show')]
    public function showCategory(Category $category, LoggerInterface $logger): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        if(!$category){
            $this->addFlash('notice', 'No such product exists!');
            $logger->error('Trying to get a product that does not exist');
            return $this->redirectToRoute('category_index');
        }

        $logger->info('Category, {category}', ['category' => $category]);
        return $this->render('category/show.html.twig', [
            'category'=> $category,
        ]);
    }
}