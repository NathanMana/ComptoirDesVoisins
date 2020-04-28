<?php

namespace App\Repository;

use App\Entity\Offer;
use App\Data\SearchData;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Offer|null find($id, $lockMode = null, $lockVersion = null)
 * @method Offer|null findOneBy(array $criteria, array $orderBy = null)
 * @method Offer[]    findAll()
 * @method Offer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OfferRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Offer::class);
    }

    /**
     * Récupère les produits en lien avec une recherche
     * @return Offer[]
     */
    public function findSearch(SearchData $search): array
    {   
        $query= $this   
            ->createQueryBuilder('p')
            ->andWhere("p.available > 0");
        
        if(!empty($search->q)){
            $query = $query 
                ->andWhere("p.citiesDelivery LIKE :q")
                ->setParameter("q", "%{$search->q}%");
        }
        return $query->getQuery()->getResult();
    }

    // /**
    //  * @return Offer[] Returns an array of Offer objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Offer
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}