<?php

namespace App\Repository;

use DateTime;
use DateInterval;
use App\Entity\User;
use App\Entity\Offer;
use App\ViewModel\SearchData;
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
     * Récupère les produits en ligne d'un utilisateur
     * @return Offer[]
     */
    public function findOnlineOffersForUser(User $user): array
    {  
        $todayWithHours = new DateTime();
        $today = $todayWithHours->format("Y-m-d");

        $query= $this   
            ->createQueryBuilder('p')
            ->andWhere("p.dateDelivery >= :date AND p.user = :user")
            ->orderBy('p.dateDelivery','ASC')
            ->setParameter(':date', $today)
            ->setParameter(':user', $user->getId());

        return $query->getQuery()->getResult();
    }

    /**
     * Récupère les produits en lien avec une recherche
     * @return Offer[]
     */
    public function findSearch(SearchData $search): array
    {   
        $todayWithHours = new DateTime();
        $today = $todayWithHours->setTimezone(new \DateTimeZone('UTC'))->format("Y-m-d");

        $query= $this   
            ->createQueryBuilder('p')
            ->leftJoin("p.citiesDelivery", "c")
            ->andWhere("p.available < p.limited AND p.dateDelivery >= :date")
            ->orderBy('p.dateDelivery','ASC')
            ->setParameter(':date', $today);
        
        if(!empty($search->q) && empty($search->distance)){
            $query = $query 
                ->andWhere("c.name LIKE :q")
                ->setParameter("q", "%{$search->q}%");
        }

        if(!empty($search->groceryType)){
            
            $orStatements = $query->expr()->orX();
            foreach ($search->groceryType as $pattern) {
                $orStatements->add(
                    $query->expr()->like('p.groceryType', $query->expr()->literal('%' . $pattern . '%'))
                );
            }
            $query->andWhere($orStatements);
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
     * Récupère les offres créées par l'utilisateur et qui ont un client (au moins 1)
     * @return Offer[]
     */
    public function findOffersWithClient(User $user): array
    {   

        $query= $this   
            ->createQueryBuilder('p')
            ->leftjoin ('p.clients','c')
            ->andWhere("p.user = :user AND c is not null")
            ->setParameter("user", $user);
        
        return $query->getQuery()->getResult();
    }

    /**
     * Récupère les offres créées par l'utilisateur et qui ont un client (au moins 1) et qui ne sont pas dépassées
     * @return Offer[]
     */
    public function findOffersWithClientButNotDead(User $user): array
    {   

        $todayWithHours = new DateTime();
        $today = $todayWithHours->format("Y-m-d");

        $query= $this   
            ->createQueryBuilder('p')
            ->leftjoin ('p.clients','c')
            ->andWhere("p.user = :user AND c is not null AND p.dateDelivery >= :now")
            ->setParameter("user", $user)
            ->setParameter("now", $today);
        
        return $query->getQuery()->getResult();
    }

    /**
     * Récupère seulement les champs nécéssaires à l'affichage dans le calendrier
     */
    public function findCalendar(User $user):array
    {
        $query = $this  ->createQueryBuilder('p')
                        ->addSelect('p.id', 'p.dateDelivery', 'p.available')
                        ->leftjoin ('p.clients','c')
                        ->andWhere("p.user = :user")
                        ->andWhere("c is not null")
                        ->setParameter("user", $user);
        
        return $query->getQuery()->getResult();
    }

    /**
     * Récupère un tableau d'offres de l'utilisateur qui ne sont pas remplie (available != limited)
     * @return Advert[]
     */
    public function findMyOffersWithPlace(User $user): array
    {
        $todayWithHours = new DateTime();
        $today = $todayWithHours->format("Y-m-d");

        $query = $this  ->createQueryBuilder('a')
                        ->andWhere("a.user = :user AND a.available < a.limited AND a.dateDelivery >= :date")
                        ->setParameter("user", $user)
                        ->setParameter("date", $today);
        
        return $query->getQuery()->getResult();
    }

    /**
     * Récupère un tableau de demande dépassée
     * @return Offer[]
     */
    public function findWaste(): array
    {

        $date = new DateTime();
        $date->sub(new DateInterval('P15D'));

        $query = $this  ->createQueryBuilder('a')
                        ->leftjoin ('a.clients','c')
                        ->andWhere("a.dateDelivery < :date AND c is null")
                        ->setParameter(':date', $date);
        
        return $query->getQuery()->getResult();
    }

     /**
     * Récupère un tableau d'offres qui se sont passées 
     * @return Offer[]
     */
    public function findHistorical(User $user): array
    {
        $date = new DateTime();
        $dateWithoutHours = $date->format('Y-m-d');

        $query = $this  ->createQueryBuilder('p')
                        ->leftjoin ('p.clients','c')
                        ->andWhere("p.user = :user AND c is not null AND p.dateDelivery < :now")
                        ->setParameter(':now', $dateWithoutHours)
                        ->setParameter(':user', $user);
        
        return $query->getQuery()->getResult();
    }
 
}
