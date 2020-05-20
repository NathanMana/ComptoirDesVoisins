<?php

namespace App\Controller;

use Exception;
use App\Entity\Advert;
use App\Service\API\GeoApi;
use App\Data\SearchData;
use App\Form\SearchAdvertType;
use App\Form\AdvertCreationType;
use App\Repository\AdvertRepository;
use App\Service\NotificationManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdvertController extends AbstractController
{
    /**
     * @Route("/annonce/choix", name="choiceAdvert")
     */
    public function choiceAdvert(){
        return $this->render("cdv/adverts/choiceAdvert.html.twig");
    }

    /**
     * @Route("/demandes", name="adverts")
     */
    public function adverts(AdvertRepository $advertRepo, Request $request){
        
        $search = new SearchData();

        $form= $this->createForm(SearchAdvertType::class, $search);
        $form->handleRequest($request);

        $adverts = $advertRepo->findSearch($search);

        if($form->isSubmitted() && $form->isValid()){
            if(substr($search->q,-1) !== ")"){
                if(empty($search->q)){
                    $search->q = "";
                } else {
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
        } 

        return $this->render("cdv/adverts/adverts.html.twig", [
            "adverts"=>$adverts,
            "form"=>$form->createView(),
            "propositionsVille"=>null
        ]);
    }

    /**
     * @Route("/mesdemandes/creation/sefairelivrer", name="createAdvert")
     * @Route("/mesdemandes/modification/sefairelivrer/{id}", name="editAdvert")
     */
    public function creationOrEditMyAdvert(Advert $advert = null, Request $request, EntityManagerInterface $manager){
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

                if($response && $response[0]['nom']." (".$response[0]['codeDepartement'].")" === $advert->getCity()) {

                    if(!$advert->getId()){     
                                         //Si on créer l'annonce
                        $advert ->setCreatedAt(new \Datetime)
                                ->setUser($this->getUser())
                                ->setCancellation(false);

                    } else {                                    //Si on modifie l'annonce
                        $advert ->setCreatedAt(new \Datetime);
                    }

                } else {
                    throw new Exception('Veuillez entrer une ville valide');
                }
                
                $manager->persist($advert);
                $manager->flush();

                return $this->redirectToRoute('myAdverts');

            }
    
            return $this->render("cdv/adverts/advertCreation.html.twig",[
                "form"=>$form->createView(),
                "editing"=>$advert->getId() !== null
            ]);
        } else {
            throw $this->createNotFoundException();
        }

    }

    /**
     * @Route("/mesdemandes/suppression/{id}", name="deleteAdvert")
     */
    public function deleteAdvert(Advert $advert, EntityManagerInterface $manager){
        if($this->getUser() === $advert->getUser() && !$advert->getDeliverer()){
            $manager->remove($advert);
            $manager->flush();
            return $this->redirectToRoute("myAdverts");
        } else {
            throw new \Exception("Vous n'avez pas les droits de modification pour cet article !");
        }
    }

    /**
     * @Route("/mesdemandes/annulation/{id}", name="cancelAdvert")
     */
    public function cancelAdvert(Advert $advert, EntityManagerInterface $manager){
        if($this->getUser() === $advert->getUser() && $advert->getDeliverer()){
            $advert->setCancellation(true);

            $notification = new NotificationManager();
            $notification = $notification->advertDelete($advert, $this->getUser());

            $manager->persist($notification);
            $manager->persist($advert);
            $manager->flush();

            return $this->redirectToRoute("myAdverts");
        } else {
            throw new \Exception("Vous n'avez pas les droits de modification pour cet article !");
        }
    }

    /**
     * @Route("/demandes/{id}", name="advertInformation")
     */
    public function advertInformation(Advert $advert){
        if($advert->getDeliverer() !== null){
            throw $this->createNotFoundException('Cette annonce n\'existe pas');
        } else {
            return $this->render("cdv/adverts/advertInformationLayout.html.twig", [
                "advert"=>$advert
            ]);
        }
    }

    /**
     * @Route("/mesdemandes/{id}", name="informationForCreatorAdvert")
     */
    public function informationForCreator(Advert $advert)
    {
        if($this->getUser() === $advert->getUser()){
            return $this->render("cdv/adverts/informationForCreator.html.twig", [
                'advert'=>$advert
            ]);
        } else {
            throw $this->createNotFoundException('Cette demande n\'existe pas');
        }
    }

    /**
     * @Route("/meslivraisons/demandes/{id}", name="informationForDelivererAdvert")
     */
    public function informationForDeliverer(Advert $advert)
    {
        if($this->getUser() === $advert->getDeliverer()){
            return $this->render("cdv/adverts/informationForDeliverer.html.twig", [
                'advert'=>$advert
            ]);
        } else {
            throw $this->createNotFoundException('Cette demande n\'existe pas');
        }
    }

    
    /**
     * @Route("/demandes/{id}/livraison", name="delivery")
     */
    public function delivery(Advert $advert, EntityManagerInterface $manager){
        if($advert->getDeliverer() === null && $advert->getUser() !== $this->getUser()){
            $advert->setDeliverer($this->getUser());

            $notification = new NotificationManager();
            $notification = $notification->delivererAdvert($advert, $this->getUser());

            $manager->persist($advert);
            $manager->persist($notification);
    
            $manager->flush();
            return $this->redirectToRoute("myDeliveries");
        } else {
            throw $this->createNotFoundException('Cette annonce n\'existe pas');
        }
    }

    /**
     * @Route("/mesdemandes/annulation/{id}", name="confirmCancellationAdvert")
     */
    public function confirmCancellationAdvert(Advert $advert, EntityManagerInterface $manager){
        if($this->getUser() === $advert->getDeliverer()){

            $notification = new NotificationManager();
            $notification = $notification->advertDeleteConfirmation($advert, $this->getUser());
           
            $manager->persist($notification);
            $manager->remove($advert);
            $manager->flush();
            return $this->redirectToRoute("myDeliveries");
        } else {
            throw $this->createNotFoundException('Cette annonce n\'existe pas');
        }
    }

    /**
     * @Route("/mesdemandes/reception/{id}", name="givenAdvert")
     */
    public function givenAdvert(Advert $advert, EntityManagerInterface $manager){
        if($this->getUser() === $advert->getUser()){
            $points = $advert->getDeliverer()->getPoints();
            $points += 1;
            $advert->getDeliverer()->setPoints($points);
            $manager->remove($advert);
            $manager->flush();
            return $this->redirectToRoute("myDeliveries");
        } else {
            throw $this->createNotFoundException('Cette annonce n\'existe pas');
        }
    }   
}
