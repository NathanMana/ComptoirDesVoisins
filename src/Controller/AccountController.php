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
        $myAdvertsWithDeliverer = $advertRepository->findAdvertsWithDeliverer($this->getUser());
        $myDeliveries = $this->getUser()->getMyDeliveries();
        $myOffersWithClient = $offerRepository->findOffersWithClient($this->getUser());

        return $this->render("cdv/deliveries/my_deliveries.html.twig", [
            "myDeliveries"=>$myDeliveries,
            "myAdvertsWithDeliverer"=>$myAdvertsWithDeliverer,
            "myOffersWithClient"=>$myOffersWithClient
        ]);
    }

    /**
     * @Route("/mesannonces", name="my_adverts")
     */
    public function my_adverts(){
        $myAdverts = $this->getUser()->getMyAdverts();
        $myOffers = $this->getUser()->getOffers();
        
        return $this->render("cdv/adverts/my_adverts.html.twig", [
            "myAdverts"=>$myAdverts,
            "myOffers"=>$myOffers
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
