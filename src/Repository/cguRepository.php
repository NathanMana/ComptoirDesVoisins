<?php

namespace App\Repository;

use App\Entity\cgu;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method cgu|null find($id, $lockMode = null, $lockVersion = null)
 * @method cgu|null findOneBy(array $criteria, array $orderBy = null)
 * @method cgu[]    findAll()
 * @method cgu[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class cguRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, cgu::class);
    }

    // /**
    //  * @return cgu[] Returns an array of cgu objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CGU
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
