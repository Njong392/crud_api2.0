<?php

namespace App\Repository;

use App\Entity\Product;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * @return Product[] Returns an array of Product objects
     */
    public function findAllByUser(User $user, int $currentPage = 1, int $limit = 5, string $search): Paginator
    {
        $query = $this->createQueryBuilder('p')
            ->orderBy('p.id', 'DESC')
            ->where('p.creator = :user')
            ->andWhere('p.name LIKE :search OR p.description LIKE :search')
            ->setParameter('search', '%' . $search . '%')
            ->setParameter('user', $user)
            ->leftJoin('p.category', 'c')
            ->addSelect('c')
            ->getQuery()
            ->setFirstResult(($currentPage - 1) * $limit)
            ->setMaxResults($limit);

        return new Paginator($query, true);
    }

    public function findOneById(User $user, int $id)
    {
        return $this->createQueryBuilder('p')
            ->where('p.creator = :user')
            ->andWhere('p.id = :id')
            ->setParameter('user', $user)
            ->setParameter('id', $id)
            ->leftJoin('p.category', 'c')
            ->addSelect('c')
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    //    public function findOneBySomeField($value): ?Product
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
