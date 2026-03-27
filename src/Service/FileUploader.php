<?php

namespace App\Service;

use App\Entity\Product;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploader
{
    public function __construct(
        private string $targetDirectory,
        private SluggerInterface $slugger,
    ) {}

    public function upload(UploadedFile $file): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

        try {

            $file->move($this->getTargetDirectory(), $fileName);
        } catch (FileException $e) {
            print_r($e->getMessage());
        }

        return $fileName;
    }

    public function getTargetDirectory(): string
    {
        return $this->targetDirectory;
    }

    public function deleteImage(string $imageBasePath, Product $product)
    {
        $imagePath = $imageBasePath . '/' . $product->getImageFilename();
        $filesystem = new Filesystem();
        if($product->getImageFilename() && $filesystem->exists($imagePath)) {
            $filesystem->remove($imagePath);
        }
    }
}
