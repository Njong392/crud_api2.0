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


final class ProductController extends AbstractController
{
    #[Route('/product', name: 'product_index')]
    public function index(ProductRepository $repository): Response
    {
      
        return $this->render('product/index.html.twig', [
            'products' => $repository->findAll(),
        ]);
    }

     #[Route('/product/new', name:'product_new')]
    public function new(Request $request, EntityManagerInterface $em)
    {   
        $product = new Product();

        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $em->persist($product);
            $em->flush();

            $this->addFlash('notice', 'Product created successfully');

            return $this->redirectToRoute('product_show', [
                'id' => $product->getId()
            ]);
        }

        return $this->render('product/new.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/product/{id<\d+>}', name:'product_show')]
    public function show(Product $product)
    {   
        return $this->render('product/show.html.twig', [
            'product' => $product
        ]);
    }

    #[Route('/product/{id<\d+>}/edit', name:'product_edit')]
    public function edit(Product $product, Request $request, EntityManagerInterface $em)
    {

        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
           
            $em->flush();

            $this->addFlash('notice', 'Product updated successfully');

            return $this->redirectToRoute('product_show', [
                'id' => $product->getId()
            ]);
        }

        return $this->render('product/edit.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/product/{id<\d+>}/delete', name:'product_delete')]
    public function delete(Request $request, Product $product, EntityManagerInterface $em)
    {   
        if($request->isMethod('POST'))
            {
                $em->remove($product);
                $em->flush();

                $this->addFlash('notice','Product deleted!');

                return $this->redirectToRoute('product_index');
            }
        return $this->render('product/delete.html.twig', [
            'id' => $product->getId(),
        ]);
    }
}

