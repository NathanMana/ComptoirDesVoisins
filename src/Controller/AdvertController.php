<?php

namespace App\Controller;

use App\Entity\Advert;
use App\Data\SearchData;
use App\Form\SearchType;
use App\Entity\Notification;
use App\Form\AdvertCreationType;
use App\Repository\OfferRepository;
use App\Repository\AdvertRepository;
use App\Service\GeoApi;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdvertController extends AbstractController
{
    /**
     * @Route("/annonce/choix", name="choice_advert")
     */
    public function choice_advert(){
        return $this->render("cdv/adverts/choice_advert.html.twig");
    }

    /**
     * @Route("/annonces", name="adverts")
     */
    public function adverts(AdvertRepository $advertRepo, Request $request){
        
        $search = new SearchData();

        $form= $this->createForm(SearchType::class, $search);
        $form->handleRequest($request);

        $adverts = $advertRepo->findSearch($search);

        if($form->isSubmitted() && $form->isValid()){
            if(substr($search->q,-1) !== ")"){
                $apiGeo = new GeoApi();
                $response = $apiGeo->RequestApi("nom", $search->q)->toArray();
    
                if(count($response) > 1){
                    return $this->render("cdv/adverts/adverts.html.twig", [
                        "adverts"=>$adverts,
                        "form"=>$form->createView(),
                        "propositionsVille"=>$response
                    ]);
                }
            }
        } 

        return $this->render("cdv/adverts/adverts.html.twig", [
            "adverts"=>$adverts,
            "form"=>$form->createView(),
            "propositionsVille"=>null
        ]);
    }

    /**
     * @Route("/mesannonces/creation/sefairelivrer", name="create_my_advert")
     * @Route("/mesannonces/modification/sefairelivrer/{id}", name="edit_advert")
     */
    public function creation_or_edit_my_advert(Advert $advert = null, Request $request, EntityManagerInterface $manager){
        if(!$advert){
            $advert = new Advert();
            $checkUser = true;
            $advert ->setCity($this->getUser()->getCity())
                    ->setCodeCity($this->getUser()->getCodeCity()); //On bind d'entrée la ville de l'annonce avec la ville de l'utilisateur  
        } else {
            if($advert->getUser() !== $this->getUser()){    //Si l'utilisateur qui veut modifier l'annonce n'est pas le proprio de l'annonce
                $checkUser = false;
                throw $this->createNotFoundException('Cette annonce n\'existe pas');
            }  else {
                $checkUser = true;
            }
        }
        
        if($checkUser){
            $form = $this->createForm(AdvertCreationType::class, $advert);
            $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()){
                $apiGeo = new GeoApi();
                $response = $apiGeo->RequestApi("code", $advert->getCodeCity())->toArray();

                if($response[0]['nom']." (".$response[0]['codeDepartement'].")" === $advert->getCity()){ //On vérifie si le front et le bac ont les mêmes infos
                    if(!$advert->getId()){                      //Si on créer l'annonce
                        $advert->setCreatedAt(new \Datetime)
                            ->setUser($this->getUser())
                            ->setCancellation(false);
                    } else {            //Si on modifie l'annonce
                        $advert->setCreatedAt(new \Datetime);
                    }
                } else {
                    throw new \Exception("Veuillez sélectionner une ville valide");
                }

                $manager->persist($advert);
                $manager->flush();
    
                return $this->redirectToRoute('my_adverts');
            }
        }

        return $this->render("cdv/adverts/advert_creation.html.twig",[
            "form"=>$form->createView(),
            "editing"=>$advert->getId() !== null
        ]);
    }

    /**
     * @Route("/mesannonces/suppression/{id}", name="delete_advert")
     */
    public function delete_advert(Advert $advert, EntityManagerInterface $manager){
        if($this->getUser() === $advert->getUser() && !$advert->getDeliverer()){
            $manager->remove($advert);
            $manager->flush();
            return $this->redirectToRoute("my_adverts");
        } else {
            throw new \Exception("Vous n'avez pas les droits de modification pour cet article !");
        }
    }

    /**
     * @Route("/mesannonces/annulation/{id}", name="cancel_advert")
     */
    public function cancel_advert(Advert $advert, EntityManagerInterface $manager){
        if($this->getUser() === $advert->getUser() && $advert->getDeliverer()){
            $advert->setCancellation(true);

            $notification = new Notification();
            $notification->setObject($advert->getUser()->getName(). " veut annuler ça demande")
                        ->setMessage($advert->getUser()->getName(). " veut annuler cet échange, contactez le si vous n'étiez pas au courant.")
                        ->setSeen(false)
                        ->setUser($advert->getDeliverer())
                        ->setCreatedAt(new \DateTime());

            $manager->persist($advert);
            $manager->persist($notification);

            $manager->flush();
            return $this->redirectToRoute("my_adverts");
        } else {
            throw new \Exception("Vous n'avez pas les droits de modification pour cet article !");
        }
    }

    /**
     * @Route("/annonce/{id}", name="advert_information")
     */
    public function advert_information(Advert $advert){
        if($advert->getDeliverer() !== null){
            throw $this->createNotFoundException('Cette annonce n\'existe pas');
        } else {
            return $this->render("cdv/adverts/advert_information_layout.html.twig", [
                "advert"=>$advert
            ]);
        }
    }
    
    /**
     * @Route("/annonce/{id}/livraison", name="delivery")
     */
    public function delivery(Advert $advert, EntityManagerInterface $manager){
        if($advert->getDeliverer() === null && $advert->getUser() !== $this->getUser()){
            $advert->setDeliverer($this->getUser());
    
            $notification = new Notification();
            $notification->setObject("Votre annonce a trouvé preneur !")
                        ->setMessage("Votre annonce a été prise en charge par ". $this->getUser()->getName() . ". Elle rentrera bientôt en contact avec vous !")
                        ->setSeen(false)
                        ->setUser($advert->getUser())
                        ->setCreatedAt(new \DateTime());
    
            $manager->persist($advert);
            $manager->persist($notification);
    
            $manager->flush();
            return $this->redirectToRoute("my_deliveries");
        } else {
            throw $this->createNotFoundException('Cette annonce n\'existe pas');
        }
    }

    /**
     * @Route("/meslivraisons/annulation/{id}", name="confirm_cancellation_advert")
     */
    public function confirm_cancellation_advert(Advert $advert, EntityManagerInterface $manager){
        if($this->getUser() === $advert->getDeliverer()){
            $notification = new Notification();
            $notification->setObject("Suppression de l'annonce")
                        ->setMessage("Votre contact a confirmé l'annulation de l'annonce. Elle est donc supprimée")
                        ->setSeen(false)
                        ->setUser($advert->getUser())
                        ->setCreatedAt(new \DateTime());

            $manager->persist($notification);
            $manager->remove($advert);
            $manager->flush();
            return $this->redirectToRoute("my_deliveries");
        } else {
            throw $this->createNotFoundException('Cette annonce n\'existe pas');
        }
    }

    /**
     * @Route("/mesannonces/reception/{id}", name="given_advert")
     */
    public function given_advert(Advert $advert, EntityManagerInterface $manager){
        if($this->getUser() === $advert->getUser()){
            $points = $advert->getDeliverer()->getPoints();
            $points += 1;
            $advert->getDeliverer()->setPoints($points);
            $manager->remove($advert);
            $manager->flush();
            return $this->redirectToRoute("my_adverts");
        } else {
            throw $this->createNotFoundException('Cette annonce n\'existe pas');
        }
    }   
}
