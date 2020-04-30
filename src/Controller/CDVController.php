<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\AdvertRepository;
use App\Repository\OfferRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\IsNull;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
     * @Route("/connexion/motdepasse/envoi", name="email_sent")
     */
    public function email_sent(){
        return $this->render("cdv/account/email_sent.html.twig");
    }

    
}
