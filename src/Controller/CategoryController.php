<?php

namespace App\Controller;

use App\Entity\Category;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

final class CategoryController extends AbstractController
{
    #[Route('/categories', name: 'category_index')]
    public function allCategories(CategoryRepository $repository, LoggerInterface $logger): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        $categories = $repository->findAllWithProducts();
        $logger->info('Categories fetched');

        return $this->render('category/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/category/{id<\d+>}', name: 'category_show')]
    public function showCategory(Category $category, LoggerInterface $logger, CategoryRepository $repository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        $cat = $repository->findOneById($category->getId());

        if (!$cat) {
            $this->addFlash('notice', 'No such category exists!');
            $logger->error('Trying to get a category that does not exist');
            return $this->redirectToRoute('category_index');
        }

        $logger->info('Category');
        return $this->render('category/show.html.twig', [
            'category' => $cat,
        ]);
    }


    #[Route('/category/{id<\d+>}/delete', name: 'category_delete')]
    public function deleteCategory(Category $category, LoggerInterface $logger, Request $request, EntityManagerInterface $em, CategoryRepository $repository)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        if ($this->isGranted('ROLE_ADMIN')) {
            if (!$category) {
                $this->addFlash('notice', 'No such category exists!');
                $logger->error('Trying to get a category that does not exist');
                return $this->redirectToRoute('category_index');
            } else {
                if ($request->isMethod('POST')) {

                    $count = $category->getProducts()->count();

                    if ($count > 0) {
                        $logger->warning('Trying to delete a category with products');

                        $newCategory = $repository->findOneBy(['name' => 'Uncategorized']);

                        if($category->getName() === $newCategory->getName()){
                            return $this->redirectToRoute('category_index');
                        }

                        // move products to another category
                        foreach ($category->getProducts() as $product) {
                            $product->setCategory($newCategory);
                        }

                        $em->flush();
                    }

                    $em->remove($category);
                    $em->flush();
                    $logger->info('Category deleted');

                    $this->addFlash('notice', 'Category deleted!');

                    return $this->redirectToRoute('category_index');
                }
            }

            return $this->render('category/delete.html.twig', [
                'category' => $category,
            ]);
        }

        return $this->redirectToRoute('category_index');
    }
}
