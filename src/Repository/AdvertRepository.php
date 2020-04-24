<?php

namespace App\Repository;

use App\Data\SearchData;
use App\Entity\Advert;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Advert|null find($id, $lockMode = null, $lockVersion = null)
 * @method Advert|null findOneBy(array $criteria, array $orderBy = null)
 * @method Advert[]    findAll()
 * @method Advert[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AdvertRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Advert::class);
    }

    /**
     * Récupère les produits en lien avec une recherche
     * @return Advert[]
     */
    public function findSearch(SearchData $search): array
    {   
        $query= $this   
            ->createQueryBuilder('p')
            ->andWhere("p.deliverer is null");
        
        if(!empty($search->q)){
            $query = $query 
                ->andWhere("p.City LIKE :q")
                ->setParameter("q", "%{$search->q}%");
        }
        return $query->getQuery()->getResult();
    }

    // /**
    //  * @return Advert[] Returns an array of Advert objects
    //  */
    /*
    public function findByCity($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.city = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Advert
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
