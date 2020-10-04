<?php

namespace App\Repository;

use DateTime;
use DateInterval;
use App\Entity\User;
use App\Entity\Advert;
use App\ViewModel\SearchData;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

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
     * Récupère les produits en ligne d'un utilisateur
     * @return Advert[]
     */
    public function findOnlineAdvertsForUser(User $user){
        $todayWithHours = new DateTime();
        $today = $todayWithHours->format("Y-m-d");

        $query= $this   
                ->createQueryBuilder('p')
                ->andWhere("p.deadline >= :today AND p.user = :user")
                ->orderBy('p.deadline','DESC')
                ->setParameter(':today', $today)
                ->setParameter(':user', $user);

        return $query->getQuery()->getResult();
    }

    /**
     * Récupère les produits en lien avec une recherche
     * @return Advert[]
     */
    public function findSearch(SearchData $search): array
    {   
        $todayWithHours = new DateTime();
        $today = $todayWithHours->format("Y-m-d");

        $query= $this   
            ->createQueryBuilder('p')
            ->leftJoin("p.city", "c")
            ->andWhere("p.deliverer is null AND c is not null AND p.deadline >= :today ")
            ->orderBy('p.createdAt','DESC')
            ->setParameter(':today', $today);

        if(!empty($search->q) && empty($search->distance)){
            $query = $query 
                ->andWhere("c.name LIKE :q")
                ->setParameter("q", "%{$search->q}%");
        }

        if(!empty($search->distance)){
            $oneDegInOneKmLon = 111*cos(deg2rad($search->lat)); //60km pour la france
            $degForGivenKmLon = $search->distance / $oneDegInOneKmLon;

            $oneDegInOneKmLat = 111;
            $degForGivenKmLat = $search->distance / $oneDegInOneKmLat;

            $lonInf = $search->lon - $degForGivenKmLon;
            $lonSup = $search->lon + $degForGivenKmLon;
            $latInf = $search->lat - $degForGivenKmLat;
            $latSup = $search->lat + $degForGivenKmLat;

            $query = $query 
                ->andWhere("c.longitude BETWEEN :lonInf AND :lonSup AND c.latitude BETWEEN :latInf AND :latSup")
                ->andWhere("(((c.longitude - :lon ) * :unityLon) * ((c.longitude - :lon ) * :unityLon)) + (((c.latitude - :lat ) * 111) * ((c.latitude - :lat ) * 111)) < :distance * :distance")
                ->setParameter(":lonInf", $lonInf)
                ->setParameter(":lonSup", $lonSup)
                ->setParameter(":latInf", $latInf)
                ->setParameter(":latSup", $latSup)
                ->setParameter(":unityLon", $oneDegInOneKmLon)
                ->setParameter(":distance", $search->distance)
                ->setParameter(":lon", $search->lon)
                ->setParameter(":lat", $search->lat);
        }

        return $query->getQuery()->getResult();
    }

    /**
     * Récupère les produits qui appartiennent à l'utilisateur et qui ont un livreur
     * @return Advert[]
     */
    public function findAdvertsWithDeliverer(User $user): array
    {   
        $date = new DateTime();
        $dateWithoutHours = $date->format('Y-m-d');

        $query= $this   
            ->createQueryBuilder('p')
            ->andWhere("p.deliverer is not null AND p.user = :user AND p.deadline >= :date AND p.isDelivered = false")
            ->setParameter("user", $user)
            ->setParameter("date", $dateWithoutHours);
        
        return $query->getQuery()->getResult();
    }

    /**
     * Récupère un tableau de demande de l'utilisateur mais qui n'ont pas de livreurs
     * @return Advert[]
     */
    public function findMyAdvertsWithoutDeliverer(User $user): array
    {
        $query = $this  ->createQueryBuilder('a')
                        ->andWhere("a.user = :user AND a.deliverer is null")
                        ->setParameter("user", $user);
        
        return $query->getQuery()->getResult();
    }

    /**
     * Récupère un tableau de demande dépassée
     * @return Advert[]
     */
    public function findWaste(): array
    {

        $date = new DateTime();
        $date->sub(new DateInterval('P15D'));

        $query = $this  ->createQueryBuilder('a')
                        ->andWhere("a.deadline < :date AND a.deliverer is null")
                        ->setParameter(':date', $date);
        
        return $query->getQuery()->getResult();
    }

    /**
     * Récupère un tableau d'échanges qui se sont passés 
     * @return Advert[]
     */
    public function findHistorical(User $user): array
    {

        $date = new DateTime();
        $dateWithoutHours = $date->format('Y-m-d');

        $query = $this  ->createQueryBuilder('a')
                        ->andWhere("(a.isDelivered = true OR (a.deadline < :date AND a.deliverer is not null)) AND a.user = :user")
                        ->setParameter(':date', $dateWithoutHours)
                        ->setParameter(':user', $user);
        
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
