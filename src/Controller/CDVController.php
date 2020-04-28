<?php

namespace App\Controller;

use App\Repository\AdvertRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\IsNull;

class CDVController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index()
    {
        return $this->render('CDV/index.html.twig', [
            'controller_name' => 'CDVController',
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
     * @Route("/meslivraisons", name="my_deliveries")
     */
    public function my_deliveries(AdvertRepository $RepoAdvert){
        $myDeliveries = $this->getUser()->getMyDeliveries();
        return $this->render("cdv/deliveries/my_deliveries.html.twig", [
            "myDeliveries"=>$myDeliveries
        ]);
    }

    /**
     * @Route("/connexion/motdepasse/envoi", name="email_sent")
     */
    public function email_sent(){
        return $this->render("cdv/account/email_sent.html.twig");
    }

    /**
     * @Route("/annonce/choix", name="choice_advert")
     */
    public function choice_advert(){
        return $this->render("cdv/adverts/choice_advert.html.twig");
    }
}
