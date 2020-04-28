<?php

namespace App\Controller;

use App\Entity\Offer;
use App\Data\SearchData;
use App\Form\SearchType;
use App\Entity\Notification;
use App\Form\OfferCreationType;
use App\Repository\OfferRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OfferController extends AbstractController
{

    /**
     * @Route("/livrer", name="offers")
     */
    public function offers(OfferRepository $offerRepository, Request $request){
        $search = new SearchData();

        $form= $this->createForm(SearchType::class, $search);
        $form->handleRequest($request);

        $offers = $offerRepository->findSearch($search);

        if($form->isSubmitted() && $form->isValid()){
            if(substr($search->q,-1) !== ")"){
                $APIURL = "https://geo.api.gouv.fr/";
                $httpClient = HttpClient::create();
                $response = $httpClient->request('GET',"https://geo.api.gouv.fr/communes?nom=".$search->q."&format=json");
                $response = $response->toArray();
    
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
     * @Route("/mesannonces/creation/livrer", name="offer_creation")
     * @Route("/mesannonces/modification/livrer/{id}", name="edit_offer")
     */
    public function offer_creation(Offer $offer = null, Request $request, EntityManagerInterface $manager)
    {
        if(!$offer){
            $offer = new Offer();
            $checkUser = true;
        } else {
            if($offer->getUser() !== $this->getUser()){    //Si l'utilisateur qui veut modifier l'annonce n'est pas le proprio de l'annonce
                $checkUser = false;
                throw $this->createNotFoundException('Cette annonce n\'existe pas');
            }  else {
                $checkUser = true;
            }
        }

        if($checkUser){
            $form = $this->createForm(OfferCreationType::class, $offer);
            $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()){
                
                $APIURL = "https://geo.api.gouv.fr/";
                $httpClient = HttpClient::create();
                $response = $httpClient->request('GET',"https://geo.api.gouv.fr/communes?code=".$offer->getCodeCities()."&format=json");
                $response = $response->toArray();

                if($response[0]['nom']." (".$response[0]['codeDepartement'].")" === $offer->getCitiesDelivery()){ //On vérifie si le front et le bac ont les mêmes infos
                    if(!$offer->getId()){                      //Si on créer l'annonce
                        $offer  ->setUser($this->getUser())
                                ->setAvailable($offer->getLimited())
                                ->setCreatedAt(new \DateTime());
                    } else {            //Si on modifie l'annonce
                        $offer->setCreatedAt(new \Datetime);
                    }
                } else {
                    throw new \Exception("Veuillez sélectionner une ville valide");
                }
                $manager->persist($offer);
                $manager->flush();

                return $this->redirectToRoute("my_adverts");
            }
        }

        return $this->render('cdv/offers/offer_creation.html.twig', [
            "form"=>$form->createView(),
        ]);
    }

    /**
     * @Route("/mesannonces/offres/supprimer/{id}", name="delete_offer")
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
            return $this->redirectToRoute("my_adverts");
        } else {
            throw $this->createNotFoundException('Cette annonce n\'existe pas');
        }
    }   
}
