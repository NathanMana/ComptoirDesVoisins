<?php

namespace App\Controller;

use App\Repository\OfferRepository;
use App\Repository\AdvertRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AccountController extends AbstractController
{
    /**
     * @Route("/meslivraisons", name="my_deliveries")
     */
    public function my_deliveries(AdvertRepository $advertRepository, OfferRepository $offerRepository){
        $myAdvertsWithDeliverer = $advertRepository->findAdvertsWithDeliverer($this->getUser()); //getMyAdverts()->Where(id.user => $this->getUser) : Recherche toutes mes annonces qui ont un livreur
        $myDeliveriesForAdvert = $this->getUser()->getMyDeliveries(); //Recherche toutes mes livraisons pour des gens qui ont demandés
        $myOffersWithClient = $offerRepository->findOffersWithClient($this->getUser()); //Recherches toutes mes offres qui ont des clients
        $ClientOfOffer = $this->getUser()->getClientOffers();

        return $this->render("cdv/account/my_deliveries.html.twig", [
            "myDeliveriesForAdvert"=>$myDeliveriesForAdvert,    //Je livre qqun qui avait posté une annonce
            "myAdvertsWithDeliverer"=>$myAdvertsWithDeliverer,  //Mon annonce a trouvé preneur
            "myOffersWithClient"=>$myOffersWithClient,          //Mon offre possède des clients
            "ClientOfOffer"=>$ClientOfOffer                     //J'ai souscrit à une offre qui était disponible
        ]);
    }

    /**
     * @Route("/mesoffres", name="my_offers")
     */
    public function my_offers(){
        $myOffers = $this->getUser()->getOffers();
        return $this->render("cdv/account/my_offers.html.twig", [
            "myOffers"=>$myOffers
        ]);
    }

    /**
     * @Route("/mesdemandes", name="my_adverts")
     */
    public function my_adverts(){
        $myAdverts = $this->getUser()->getMyAdverts();
        
        
        return $this->render("cdv/account/my_adverts.html.twig", [
            "myAdverts"=>$myAdverts
        ]);
    }

    /**
     * @Route("/profil/notification", name="notifications")
     */
    public function notifications(){
        $notifications = $this->getUser()->getNotifications();
        return $this->render("cdv/account/notifications.html.twig", [
            'notifications'=>$notifications
        ]);
    }
}
