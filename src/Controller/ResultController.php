<?php

namespace App\Controller;

use Exception;
use Swift_Mailer;
use App\Entity\Help;
use App\Form\Search;
use App\Entity\Offer;
use App\Service\API\GeoApi;
use App\Service\MailManager;
use App\ViewModel\SearchData;
use App\Repository\HelpRepository;
use App\Repository\OfferRepository;
use App\Service\NotificationManager;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Constraints\Date;

class ResultController extends AbstractController
{
    /**
     * @Route("/rechercher", name="search")
     */
    public function search(Request $request)
    {

        $data = new SearchData();
        $form = $this->createForm(Search::class, $data);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            return $this->redirectToRoute("result", [
                "type" => $data->type,
                "q" => $data->q,
                "groceryType" => $data->groceryType
            ]);
        }

        return $this->render("cdv/result/search.html.twig", [
            "form" => $form->createView()
        ]);
    }

    /**
     * @Route("/rechercher/resultats", name="result")
     */
    public function resultats(Request $request, OfferRepository $offerRepository, HelpRepository $helpRepository)
    {
        $search = new SearchData();
        $form= $this->createForm(Search::class, $search);
        $form->handleRequest($request);

        if($form->isSubmitted()){
            $searchString = "";
            
            if($request->get('distance')){
                $search->distance = $request->get('distance');
            }
            if($request->get('lon')){
                $search->lon = $request->get('lon');
            }
            if($request->get('lat')){
                $search->lat = $request->get('lat');
            }
            
            if($search->type === "true"){
                $ad = $helpRepository->findSearch($search);
                $type = "help";
                $searchString .= "Je souhaite aider une personne à " . $search->q;
                
            } else {
                $ad = $offerRepository->findSearch($search);
                $type = "offer";
                $searchString .= "Je veux me faire livrer à " . $search->q;
            } 
            
            //dd($search);
            //On donne a la vue la liste des groceries
            $listGrocery = $search->groceryType;

            if(count($listGrocery) > 0){
                $searchString .= " des produits de ";
                foreach($listGrocery as $key => $item){
                    if($key === count($listGrocery) - 1){
                        $searchString .= $item;
                    } elseif($key === count($listGrocery) - 2){
                        $searchString .= $item . " et ";
                    } else {
                        $searchString .= $item . ", ";
                    }
                }
            }

            $apiGeo = new GeoApi();
            $response = $apiGeo->RequestApi("nom", $search->q)->toArray();

            if(count($response) === 1){
                $response = null;
            }

            return $this->render("cdv/result/result.html.twig", [
                "type"=>$type,
                "ad" => $ad,
                "form"=>$form->createView(),
                "propositionsVille"=>$response,
                "listGrocery" => $listGrocery,
                "search" => $searchString
            ]);
        }
        
        return $this->render("cdv/result/result.html.twig", [
            "type"=>null,
            "ad" => null,
            "form"=>$form->createView(),
            "propositionsVille"=>null,
            "listGrocery" => null
        ]);
    }

    /**
     * @Route("/rechercher/resultats/aide/{id}", name="helpInformation")
     */
    public function helpInformation(Help $help){
        if($help->getDeliverer() !== null){
            throw $this->createNotFoundException('Cette demande n\'existe pas');
        } else {
            return $this->render("cdv/result/helpInformation.html.twig", [
                "help"=>$help
            ]);
        }
    }

     /**
     * @Route("/rechercher/resultats/proposition/{id}", name="offerInformation")
     */
    public function offerInformation(Offer $offer, Request $request){

        $data = new SearchData();
        $form = $this->createForm(Search::class, $data);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            return $this->redirectToRoute("result", [
                "type" => $data->type,
                "q" => $data->q,
                "groceryType" => $data->groceryType
            ]);
        }

        if($offer->getAvailable() === $offer->getLimited()){
            throw $this->createNotFoundException('Cette annonce n\'existe pas');
        } else {
            return $this->render("cdv/result/offerInformation.html.twig", [
                "offer"=>$offer,
                "form" => $form->createView()
            ]);
        }
    }

    /**
     * @Route("/rechercher/proposition/{id}/contact", name="offerAcceptation")
     */
    public function offerAcceptation(Offer $offer, EntityManagerInterface $manager, OfferRepository $offerRepository, Swift_Mailer $mailer){
        $UserInOffer = $offer->getClients()->toArray();
        if($offer->getAvailable() !== $offer->getLimited() && $offer->getUser() !== $this->getUser() && !in_array($this->getUser(), $UserInOffer)){
            $offer  ->setAvailable($offer->getAvailable() + 1)
                    ->addClient($this->getUser());
           
            $notif = new NotificationManager();
            $notif = $notif->offerClient($offer, $this->getUser());

            if($offer->getUser()->getMailAuthorization()){
                $mailManager = new MailManager($offer->getUser()->getEmail(),$mailer);
                $mailManager->notifIntoMail($notif);
            }

            $manager->persist($notif);
            $manager->flush();

            return $this->redirectToRoute("informationForClient", [
                'id' => $offer->getId()
            ]);
        } else if($offer->getUser() === $this->getUser()){
            throw new Exception("Vous ne pouvez pas vous livrer");
        } else if(in_array($this->getUser(), $UserInOffer)){
            throw new Exception("Vous vous faîtes déjà livrer par cette personne !");
        } else {
            throw new Exception("Une erreur est intervenue");
        }
    }


    /**
     * @Route("/rechercher/aide/{id}/contact", name="helpAcceptation")
     */
    public function helpAcceptation(Help $help, EntityManagerInterface $manager, HelpRepository $helpRepository, Swift_Mailer $mailer){

        if($help->getDeliverer() || $help->getDateHelp() < new DateTime()){
            throw new Exception("Une erreur est survenue");
        }

        $user = $this->getUser();

        if($user){

            $help->setDeliverer($user);

            $manager->persist($help);
            $manager->flush();

            return $this->redirectToRoute("informationForClient", [
                'id' => $help->getId()
            ]);

        } else {
            throw new Exception("Vous n'avez pas l'autorisation");
        }
    }
}
