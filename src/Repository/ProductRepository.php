<?php

namespace App\Repository;

use App\Classe\Search;
use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * Reqûete qui va me permettre de recuperer les produits en fonction de la recherche de l'utilisateur
     * @return Product
     */
    public function FindWithSearch(Search $search)
    {
        $query=$this
            ->createQueryBuilder('p')
            ->select('c','p') //selection de Product et category (table)
            ->join('p.category', 'c'); //création de la jointure entee product et category
        if (!empty($search->categories)){
            $query=$query
                ->andWhere('c.id IN (:categories)') //Where ... en sql
                ->setParameter('categories', $search->categories);
        }
        if (!empty($search->string)){
            $query=$query
                ->andWhere('p.name LIKE :string')
                ->setParameter('string', "%{$search->string}%");
        }
        return $query->getQuery()->getResult();
    }
    // /**
    //  * @return Product[] Returns an array of Product objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Product
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
