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
    public function my_adverts(AdvertRepository $RepoAdvert){
        $myAdverts = $RepoAdvert->findBy(['user'=>$this->getUser()]);
        return $this->render("CDV/my_adverts.html.twig", [
            "myAdverts"=>$myAdverts
        ]);
    }

    /**
     * @Route("/annonces", name="adverts")
     */
    public function adverts(AdvertRepository $advertRepo){
        $adverts = $advertRepo->findBy(["deliverer"=>null]);
        return $this->render("CDV/adverts.html.twig", [
            "adverts"=>$adverts
        ]);
    }

    /**
     * @Route("/meslivraisons", name="my_deliveries")
     */
    public function my_deliveries(AdvertRepository $RepoAdvert){
        $myDeliveries= $RepoAdvert->findBy(['deliverer'=>$this->getUser()]);
        return $this->render("CDV/my_deliveries.html.twig", [
            "myDeliveries"=>$myDeliveries
        ]);
    }
}
