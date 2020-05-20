<?php

namespace App\Controller;

use App\Entity\Offer;
use App\Entity\Notification;
use App\Repository\OfferRepository;
use App\Repository\AdvertRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AccountController extends AbstractController
{
    /**
     * @Route("/meslivraisons", name="myDeliveries")
     */
    public function myDeliveries(AdvertRepository $advertRepository, OfferRepository $offerRepository){
        $myAdvertsWithDeliverer = $advertRepository->findAdvertsWithDeliverer($this->getUser()); //getMyAdverts()->Where(id.user => $this->getUser) : Recherche toutes mes annonces qui ont un livreur
        $myDeliveriesForAdvert = $this->getUser()->getMyDeliveries(); //Recherche toutes mes livraisons pour des gens qui ont demandés
        $myOffersWithClient = $offerRepository->findOffersWithClient($this->getUser()); //Recherches toutes mes offres qui ont des clients
        $ClientOfOffer = $this->getUser()->getClientOffers();

        return $this->render("cdv/account/myDeliveries.html.twig", [
            "myDeliveriesForAdvert"=>$myDeliveriesForAdvert,    //Je livre qqun qui avait posté une annonce
            "myAdvertsWithDeliverer"=>$myAdvertsWithDeliverer,  //Mon annonce a trouvé preneur
            "myOffersWithClient"=>$myOffersWithClient,          //Mon offre possède des clients
            "ClientOfOffer"=>$ClientOfOffer                     //J'ai souscrit à une offre qui était disponible
        ]);
    }

    /**
     * @Route("/mesoffres", name="myOffers")
     */
    public function myOffers(OfferRepository $offerRepository){
        $myOffers = $offerRepository->findBy(['user'=>$this->getUser()], ['dateDelivery'=>'DESC']);
        return $this->render("cdv/account/myOffers.html.twig", [
            "myOffers"=>$myOffers
        ]);
    }

    /**
     * @Route("/mesdemandes", name="myAdverts")
     */
    public function myAdverts(AdvertRepository $advertRepository){
        $myAdverts = $advertRepository->findBy(['user'=>$this->getUser()], ['createdAt'=>'DESC']);
        
        return $this->render("cdv/account/myAdverts.html.twig", [
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

      /**
     * @Route("/meslivraisons/supprimer/{id}", name="deleteOffer")
     */
    public function delete_offer(Offer $offer, EntityManagerInterface $manager){

        if($this->getUser() === $offer->getUser()){
            if(!empty($offer->getClients())){

                $notification = new Notification();

                $clients = $offer->getClients();
                foreach($clients as $client){
                    $notification   ->setObject("Suppression de l'annonce")
                                    ->setMessage($this->getUser()->getName()." a supprimé l'annonce à laquelle vous vous étiez rattaché")
                                    ->setSeen(false)
                                    ->setUser($client)
                                    ->setCreatedAt(new \DateTime());
                    $manager->persist($notification);
                }
            }

            $manager->remove($offer);
            $manager->flush();

            return $this->redirectToRoute("myOffers");

        } else if(in_array($this->getUser(), $offer->getClients()->toArray())){     //Si un client décide de supprimer l'offre, il la supprime seulement pour lui
            
            $offer->removeClient($this->getUser());
            $offer->setAvailable($offer->getAvailable() - 1);
            $manager->persist($offer);
            $manager->flush();

            return $this->redirectToRoute("myDeliveries");
        }
        else {
            throw $this->createNotFoundException('Cette annonce n\'existe pas');
        }
    }   

    /**
     * @Route("/moncomptoir", name="myCounter")
     */
    public function myCounter(AdvertRepository $advertRepository, OfferRepository $offerRepository)
    {
        $myAdvertsWithDeliverer = $advertRepository->findAdvertsWithDeliverer($this->getUser()); //getMyAdverts()->Where(id.user => $this->getUser) : Recherche toutes mes annonces qui ont un livreur
        $myDeliveriesForAdvert = $this->getUser()->getMyDeliveries(); //Recherche toutes mes livraisons pour des gens qui ont demandés
        $myOffersWithClient = $offerRepository->findOffersWithClient($this->getUser()); //Recherches toutes mes offres qui ont des clients
        $ClientOfOffer = $this->getUser()->getClientOffers();

        /* NEW VERSION */
        //advert find all (ou presque)
        //offer same

        return $this->render("cdv/account/myCounter.html.twig", [
            "myDeliveriesForAdvert"=>$myDeliveriesForAdvert,    //Je livre qqun qui avait posté une annonce
            "myAdvertsWithDeliverer"=>$myAdvertsWithDeliverer,  //Mon annonce a trouvé preneur
            "myOffersWithClient"=>$myOffersWithClient,          //Mon offre possède des clients
            "ClientOfOffer"=>$ClientOfOffer                     //J'ai souscrit à une offre qui était disponible
        ]);
    }
}
