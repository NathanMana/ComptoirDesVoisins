<?php

namespace App\Controller;

use Exception;
use App\Entity\Offer;
use App\Service\GeoApi;
use App\Data\SearchData;
use App\Form\SearchType;
use App\Form\OfferEditType;
use App\Entity\Notification;
use App\Form\OfferCreationType;
use App\Repository\OfferRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OfferController extends AbstractController
{

    /**
     * @Route("/offres", name="offers")
     */
    public function offers(OfferRepository $offerRepository, Request $request){
        $search = new SearchData();

        $form= $this->createForm(SearchType::class, $search);
        $form->handleRequest($request);

        $offers = $offerRepository->findSearch($search);

        if($form->isSubmitted() && $form->isValid()){
            if(substr($search->q,-1) !== ")"){

                $apiGeo = new GeoApi();
                $response = $apiGeo->RequestApi("nom", $search->q)->toArray();    
    
                if(count($response) > 1){
                    return $this->render("cdv/offers/offers.html.twig", [
                        "offers"=>$offers,
                        "form"=>$form->createView(),
                        "propositionsVille"=>$response
                    ]);
                }
            }
        } 

        return $this->render("cdv/offers/offers.html.twig", [
            'form'=>$form->createView(),
            'offers'=>$offers,
            "propositionsVille"=>null
        ]);
    }

    /**
     * @Route("/mesoffres/creation", name="offer_creation")
     */
    public function offer_creation(Request $request, EntityManagerInterface $manager)
    {
        $offer = new Offer();

        $form = $this->createForm(OfferCreationType::class, $offer);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $apiGeo = new GeoApi();
            $response = $apiGeo->RequestApi("code", $offer->getCodeCities())->toArray();        

            if($response[0]['nom']." (".$response[0]['codeDepartement'].")" === $offer->getCitiesDelivery()){ //On vérifie si le front et le back ont les mêmes infos
                if(!$offer->getId()){                      //Si on créer l'annonce
                    $offer  ->setUser($this->getUser())
                            ->setAvailable(0)
                            ->setCreatedAt(new \DateTime());
                }
            } else {
                throw new \Exception("Veuillez sélectionner une ville valide");
            }

            $manager->persist($offer);
            $manager->flush();

            return $this->redirectToRoute("my_offers");
        }

        return $this->render('cdv/offers/offer_creation.html.twig', [
            "form"=>$form->createView(),
        ]);
    }

    /**
     * @Route("/mesoffres/modification/{id}", name="edit_offer")
     */
    public function edit_offer(Offer $offer, Request $request, EntityManagerInterface $manager){
        if($offer->getUser() === $this->getUser()){    //Si l'utilisateur qui veut modifier l'annonce n'est pas le proprio de l'annonce
            
            $form = $this->createForm(OfferEditType::class, $offer);
            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()){
                if($offer->getLimited() < $offer->getAvailable()){ //Si on modifie et que l'on baisse le nombre de personne à livrer (ex : je livre 2 personnes, je modifie pour livrer qu'une seule personne)
                    throw new \Exception("Vous ne pouvez pas mettre un nombre de livraison inférieur au nombre de personne que vous livrez déjà");
                }
                else {
                    $offer->setCreatedAt(new \Datetime);    

                    $manager->persist($offer);
                    $manager->flush();  

                    return $this->redirectToRoute("my_offers");
                }
            } 
            return $this->render('cdv/offers/edit_offer.html.twig', [
                "form"=>$form->createView(),
            ]);
        } else {
            throw $this->createNotFoundException('Cette annonce n\'existe pas');
        }   
    }
    
    /**
     * @Route("/mesoffres/supprimer/{id}", name="delete_offer")
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
            return $this->redirectToRoute("my_offers");
        } else if(in_array($this->getUser(), $offer->getClients()->toArray())){     //Si un client décide de supprimer l'offre, il la supprime seulement pour lui
            $offer->removeClient($this->getUser());
            $offer->setAvailable($offer->getAvailable() - 1);
            $manager->persist($offer);
            $manager->flush();

            return $this->redirectToRoute("my_deliveries");
        }
        else {
            throw $this->createNotFoundException('Cette annonce n\'existe pas');
        }
    }   

    /**
     * @Route("/offres/{id}", name="offer_information")
     */
    public function offer_information(Offer $offer){
        if($offer->getAvailable() === $offer->getLimited()){
            throw $this->createNotFoundException('Cette annonce n\'existe pas');
        } else {
            return $this->render("cdv/offers/offer_information.html.twig", [
                "offer"=>$offer
            ]);
        }
    }

    /**
     * @Route("/mesoffres/{id}", name="information_for_creator")
     */
    public function information_for_creator(Offer $offer)
    {
        if($this->getUser() === $offer->getUser()){
            return $this->render("cdv/offers/information_for_creator.html.twig", [
                'offer'=>$offer
            ]);
        } else {
            throw $this->createNotFoundException('Cette offre n\'existe pas');
        }
    }

    /**
     * @Route("/meslivraisons/offres/{id}", name="information_for_client")
     */
    public function information_for_client(Offer $offer)
    {
        if(in_array($this->getUser(), $offer->getClients()->toArray())){
            return $this->render("cdv/offers/information_for_client.html.twig", [
                'offer'=>$offer
            ]);
        } else {
            throw $this->createNotFoundException('Cette offre n\'existe pas');
        }
    }

    /**
     * @Route("/annonce/livraison/rejoindre/{id}", name="offer_coming")
     */
    public function offer_coming(Offer $offer, EntityManagerInterface $manager, OfferRepository $offerRepository){
        $UserInOffer = $offer->getClients()->toArray();
        if($offer->getAvailable() !== $offer->getLimited() && $offer->getUser() !== $this->getUser() && !in_array($this->getUser(), $UserInOffer)){
            $offer  ->setAvailable($offer->getAvailable() + 1)
                    ->addClient($this->getUser());
    
            $notification = new Notification();
            $notification   ->setObject("Votre annonce a trouvé preneur !")
                            ->setMessage($this->getUser()->getName() . " aurait besoin de votre aide !")
                            ->setSeen(false)
                            ->setUser($offer->getUser())
                            ->setCreatedAt(new \DateTime());
    
            $manager->persist($offer);
            $manager->persist($notification);
            $manager->flush();

            return $this->redirectToRoute("my_deliveries");
        } else {
            throw new Exception("Une erreur est intervenue");
        }
    }

    /**
     * @Route("/mesannonces/{id}/contact", name="contact_in_my_offer")
     */
    public function contact_in_my_offer(Offer $offer){
        if($this->getUser() === $offer->getUser()){
            return $this->render("cdv/offers/contact_in_my_offer.html.twig", [
                'myOffer'=>$offer
            ]);
        } else {
            throw $this->createNotFoundException('Cette annonce n\'existe pas');
        }
    }
}
