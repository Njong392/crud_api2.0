<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ProductRepository;
use App\Form\ProductType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Service\FileUploader;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\File;

final class ProductController extends AbstractController
{
    #[Route('/products', name: 'product_index')]
    public function index(ProductRepository $repository, LoggerInterface $logger, Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        // access the current logged in user
        $currentUser = $this->getUser();
        $search = trim($request->query->get('q', ''));
        $currentPage = $request->query->getInt('page', 1);
        $limit = 5;

        $paginator = $repository->findAllByUser($currentUser, $currentPage, $limit, $search);

        $totalItems = count($paginator);
        $totalPages = ceil($totalItems / $limit);

        $logger->info('Products fetched');

        if ($currentPage > $totalPages) {
            return $this->redirectToRoute('product_index');
        }


        return $this->render('product/index.html.twig', [
            'paginator' => $paginator,
            'currentPage' => $currentPage,
            'hasPreviousPage' => $currentPage > 1,
            'hasNextPage' => $currentPage < $totalPages,
            'totalPages' => $totalPages,
            'search' => $search
        ]);
    }

    #[Route('/product/new', name: 'product_new')]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        LoggerInterface $logger,
        FileUploader $fileUploader
        // #[Autowire('%kernel.project_dir%/public/uploads/images')] string $imgDir
    ) {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');
        $product = new Product();

        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $product->setCreator($this->getUser());

            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('image')->getData();

            // the image field is not required, so the file is processed when uploaded
            if ($imageFile) {
                $imageFileName = $fileUploader->upload($imageFile);
                $product->setImageFilename($imageFileName);
            }

            $em->persist($product);
            $em->flush();
            $logger->info('Product created successfully');

            $this->addFlash('notice', 'Product created successfully');

            return $this->redirectToRoute('product_show', [
                'id' => $product->getId()
            ]);
        } elseif ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('notice', 'Some validation occurred');
            $logger->error('Validation error occurred while creating this product');
        }

        return $this->render('product/new.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/product/{id<\d+>}', name: 'product_show')]
    public function show(Product $product, LoggerInterface $logger)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        $currentUser = $this->getUser();

        if ($currentUser instanceof User) {
            $userId = $currentUser->getId();
        }

        $isCurrentUser = $product->getCreator()->getId() === $userId;


        if (!$product) {
            $this->addFlash('notice', 'No such product exists!');
            $logger->error('Trying to get a product that does not exist');
            return $this->redirectToRoute('product_index');
        }

        $logger->info('Product fetched');
        return $this->render('product/show.html.twig', [
            'product' => $product,
            'isCurrentUser' => $isCurrentUser
        ]);
    }

    #[Route('/product/{id<\d+>}/edit', name: 'product_edit')]
    public function edit(
        Product $product,
        Request $request,
        EntityManagerInterface $em,
        LoggerInterface $logger,
        FileUploader $fileUploader
    ) {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        // create new file for the already existing file

        $oldFilename = $fileUploader->getTargetDirectory() . '/' . $product->getImageFilename();
        $originalName = $product->getImageFilename();
        $product->setImageFilename(
            new File($oldFilename)
        );

        $form = $this->createForm(ProductType::class, $product);
        $form->get('image')->setData(new File($oldFilename));

        $form->handleRequest($request);


        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $em->flush();
                $logger->info('Product updated');

                $this->addFlash('notice', 'Product updated successfully');

                return $this->redirectToRoute('product_show', [
                    'id' => $product->getId()
                ]);
            } else {
                $logger->error('Validation error occurred while updating {product}', ['product' => $product]);
            }
        }

        return $this->render('product/edit.html.twig', [
            'form' => $form->createView(),
            'product' => $product,
            'filename' => $originalName
        ]);
    }

    #[Route('/product/{id<\d+>}/delete', name: 'product_delete')]
    public function delete(
        Request $request,
        Product $product,
        EntityManagerInterface $em,
        LoggerInterface $logger,
        FileUploader $fileUploader
    ) {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');
        if ($request->isMethod('POST')) {
            $em->getConnection()->beginTransaction();
            try {
                // delete product from db
                $em->remove($product);
                $em->flush();

                // delete image file
                $fileUploader->deleteImage($fileUploader->getTargetDirectory(), $product);

                $em->getConnection()->commit();

                $logger->info('Product deleted');

                $this->addFlash('notice', 'Product deleted!');

                return $this->redirectToRoute('product_index');
            } catch (\Exception $e) {
                $em->getConnection()->rollBack();
                $logger->alert('Some error occurred while deleting, {error}', ['error' => $e->getMessage()]);
            }
        }
        return $this->render('product/delete.html.twig', [
            'id' => $product->getId(),
        ]);
    }
}
