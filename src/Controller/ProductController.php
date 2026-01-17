<?php

namespace App\Controller;

use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ProductRepository;
use App\Form\ProductType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;

final class ProductController extends AbstractController
{
    #[Route('/product', name: 'product_index')]
    public function index(ProductRepository $repository, LoggerInterface $logger): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        $products = $repository->findAll();
        $logger->info('Products fetched');

        return $this->render('product/index.html.twig', [
            'products' => $products,
        ]);
    }

     #[Route('/product/new', name:'product_new')]
    public function new(Request $request, EntityManagerInterface $em, LoggerInterface $logger)
    {   
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');
        $product = new Product();

        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $em->persist($product);
            $em->flush();
            $logger->info('Product created successfully, {product}', ['product' => $product]);

            $this->addFlash('notice', 'Product created successfully');

            return $this->redirectToRoute('product_show', [
                'id' => $product->getId()
            ]);
        } else {
            $logger->error('Validation error occurred while creating this product');
        }

        return $this->render('product/new.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/product/{id<\d+>}', name:'product_show')]
    public function show(Product $product, LoggerInterface $logger)
    {   
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        if (!$product) {
            $this->addFlash('notice', 'No such product exists!');
            $logger->error('Trying to get a product that does not exist');
            return $this->redirectToRoute('product_index');
        }

        $logger->info('Product, {product}', ['product' => $product]);
        return $this->render('product/show.html.twig', [
            'product' => $product
        ]);
    }

    #[Route('/product/{id<\d+>}/edit', name:'product_edit')]
    public function edit(Product $product, Request $request, EntityManagerInterface $em, LoggerInterface $logger)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');
        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
           
            $em->flush();
            $logger->info('Product updated, {product}', ['product'=> $product]);

            $this->addFlash('notice', 'Product updated successfully');

            return $this->redirectToRoute('product_show', [
                'id' => $product->getId()
            ]);
        } else{
            $logger->error('Validation error occurred while updating {product}', ['product'=> $product]);
        }

        return $this->render('product/edit.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/product/{id<\d+>}/delete', name:'product_delete')]
    public function delete(Request $request, Product $product, EntityManagerInterface $em, LoggerInterface $logger)
    {   
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');
        if($request->isMethod('POST'))
            {
                $em->remove($product);
                $em->flush();
                $logger->info('Product deleted');

                $this->addFlash('notice','Product deleted!');

                return $this->redirectToRoute('product_index');
            }
        return $this->render('product/delete.html.twig', [
            'id' => $product->getId(),
        ]);
    }
}

