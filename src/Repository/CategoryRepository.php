<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Category>
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }


       /**
        * @return Category[] Returns an array of Category objects
        */
       public function findAllWithProducts(): array
       {
           return $this->createQueryBuilder('c')
               ->leftJoin('c.products', 'p')
               ->addSelect('p')
               ->getQuery()
               ->getResult()
           ;
       }

       public function findOneById(int $id)
       {
        return $this->createQueryBuilder('c')
            ->where('c.id = :id')
            ->setParameter('id', $id)
            ->leftJoin('c.products', 'p')
            ->addSelect('p')
            ->getQuery()
            ->getOneOrNullResult()
        ;
       }

    //    public function findOneBySomeField($value): ?Category
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
