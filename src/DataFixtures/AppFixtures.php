<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Product;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $category = new Category();

        $category->setName('Accessories');
        $manager->persist($category);

        $category = new Category();
        $category->setName('Gadgets');
        $manager->persist($category);
        $manager->flush();

    }

}
