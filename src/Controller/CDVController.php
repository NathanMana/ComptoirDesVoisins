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
        return $this->render("CDV/my_adverts.html.twig", [
            "myAdverts"=>$myAdverts
        ]);
    }

    /**
     * @Route("/meslivraisons", name="my_deliveries")
     */
    public function my_deliveries(AdvertRepository $RepoAdvert){
        $myDeliveries = $this->getUser()->getMyAdverts();
        return $this->render("CDV/my_deliveries.html.twig", [
            "myDeliveries"=>$myDeliveries
        ]);
    }
}
