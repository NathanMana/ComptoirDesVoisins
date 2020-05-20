<?php

namespace App\Controller;

use Exception;
use App\Entity\User;
use App\Entity\Offer;
use App\Data\SearchData;
use App\Form\SearchType;
use App\Form\OfferEditType;
use App\Service\API\GeoApi;
use App\Entity\Notification;
use App\Form\OfferCreationType;
use App\Repository\OfferRepository;
use App\Service\NotificationManager;
use DateTime;
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
            if(substr($search->q,-1) !== ")"){          //Proposition de ville en cas de plusieurs cas possibles
                if(empty($search->q)){
                    $search->q = "";
                } else {

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
        } 

        return $this->render("cdv/offers/offers.html.twig", [
            'form'=>$form->createView(),
            'offers'=>$offers,
            "propositionsVille"=>null
        ]);
    }

    /**
     * @Route("/mesoffres/creation", name="offerCreation")
     */
    public function offerCreation(Request $request, EntityManagerInterface $manager)
    {
        $offer = new Offer();

        $form = $this->createForm(OfferCreationType::class, $offer);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $todayWithHours = new DateTime();
            $today = $todayWithHours->format("Y-m-d");
            $dateOffer = $offer->getDateDelivery()->format("Y-m-d");
            
            if($dateOffer >= $today){
                $apiGeo = new GeoApi();
                $response = $apiGeo->RequestApi("code", $offer->getCodeCities())->toArray();  
                
                if($response && $response[0]['nom']." (".$response[0]['codeDepartement'].")" === $offer->getCitiesDelivery()){
                    $offer  ->setUser($this->getUser())
                            ->setAvailable(0)
                            ->setCreatedAt(new \DateTime());
                } else {
                    throw new Exception("Veuillez entrer une ville valide");
                }

                $manager->persist($offer);
                $manager->flush();

                return $this->redirectToRoute("myOffers");
            } else {
                throw new Exception("Veuillez rentrer une date valide");
            }

        }

        return $this->render('cdv/offers/offerCreation.html.twig', [
            "form"=>$form->createView(),
        ]);
    }

    /**
     * @Route("/mesoffres/modification/{id}", name="editOffer")
     */
    public function editOffer(Offer $offer, Request $request, EntityManagerInterface $manager){
        if($offer->getUser() === $this->getUser()){    //Si l'utilisateur qui veut modifier l'annonce n'est pas le proprio de l'annonce
            
            $form = $this->createForm(OfferEditType::class, $offer);
            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()){
                if($offer->getLimited() < $offer->getAvailable()){ //Si on modifie et que l'on baisse le nombre de personne à livrer (ex : je livre deja 2 personnes, je modifie pour livrer qu'une seule personne)
                    throw new \Exception("Vous ne pouvez pas mettre un nombre de livraison inférieur au nombre de personne que vous livrez déjà");
                }
                else {
                    if($offer->getDateDelivery() >= new DateTime('now')){
                        $offer->setCreatedAt(new \Datetime);    
    
                        $manager->persist($offer);
                        $manager->flush();  
    
                        return $this->redirectToRoute("myOffers");
                    } else {
                        throw new Exception("Veuillez rentrer une date valide");
                    }
                }
            } 
            return $this->render('cdv/offers/editOffer.html.twig', [
                "form"=>$form->createView(),
            ]);
        } else {
            throw $this->createNotFoundException('Cette annonce n\'existe pas');
        }   
    }

    /**
    * @Route("/mescourses/client/retirer/{id}/{user}", name="removeClient")
    */
    public function removeClient(Offer $offer, User $user, EntityManagerInterface $manager)
    {
        $clients = $offer->getClients()->toArray();
        $userInClients = in_array($user, $clients);
        if($this->getUser() === $offer->getUser() && $userInClients){

            $el = array_search($user,$clients,true);
            if($el !== null){

                $offer->setAvailable($offer->getAvailable()-1);
                $user->removeClientOffer($offer);
                $manager->flush();

                return $this->redirectToRoute("informationForCreator", [
                    'id'=>$offer->getId()
                ]);
            } else {

                throw new Exception("Une erreur est intervenue"); 

            }

        } else {
            return $this->createNotFoundException();
        }
    }

    /**
     * @Route("/offres/{id}", name="offerInformation")
     */
    public function offerInformation(Offer $offer){
        if($offer->getAvailable() === $offer->getLimited()){
            throw $this->createNotFoundException('Cette annonce n\'existe pas');
        } else {
            return $this->render("cdv/offers/offerInformation.html.twig", [
                "offer"=>$offer
            ]);
        }
    }

    /**
     * @Route("/mesoffres/{id}", name="informationForCreator")
     */
    public function informationForCreator(Offer $offer)
    {
        if($this->getUser() === $offer->getUser()){
            return $this->render("cdv/offers/informationForCreator.html.twig", [
                'offer'=>$offer
            ]);
        } else {
            throw $this->createNotFoundException('Cette offre n\'existe pas');
        }
    }

    /**
     * @Route("/meslivraisons/offres/{id}", name="informationForClient")
     */
    public function informationForClient(Offer $offer)
    {
        if(in_array($this->getUser(), $offer->getClients()->toArray())){
            return $this->render("cdv/offers/informationForClient.html.twig", [
                'offer'=>$offer
            ]);
        } else {
            throw $this->createNotFoundException('Cette offre n\'existe pas');
        }
    }

    /**
     * @Route("/annonce/livraison/rejoindre/{id}", name="offerComing")
     */
    public function offerComing(Offer $offer, EntityManagerInterface $manager, OfferRepository $offerRepository){
        $UserInOffer = $offer->getClients()->toArray();
        if($offer->getAvailable() !== $offer->getLimited() && $offer->getUser() !== $this->getUser() && !in_array($this->getUser(), $UserInOffer)){
            $offer  ->setAvailable($offer->getAvailable() + 1)
                    ->addClient($this->getUser());
           
            $notif = new NotificationManager();
            $notif = $notif->offerClient($offer, $this->getUser());
            $manager->persist($notif);
            $manager->persist($offer);
            $manager->flush();

            return $this->redirectToRoute("myDeliveries");
        } else {
            throw new Exception("Une erreur est intervenue");
        }
    }

    /**
     * @Route("/mesannonces/{id}/contact", name="contactInMyOffer")
     */
    public function contactInMyOffer(Offer $offer){
        if($this->getUser() === $offer->getUser()){
            return $this->render("cdv/offers/contactInMyOffer.html.twig", [
                'myOffer'=>$offer
            ]);
        } else {
            throw $this->createNotFoundException('Cette annonce n\'existe pas');
        }
    }
}
