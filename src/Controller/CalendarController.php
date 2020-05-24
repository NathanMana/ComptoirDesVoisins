<?php

namespace App\Controller;

use App\ViewModel\CalendarViewModel;
use App\Data\JsonListCalendarModel;
use App\Repository\OfferRepository;
use App\Repository\AdvertRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CalendarController extends AbstractController
{

    /**
     * @Route("/calendrier/evenement", name="calendarEvent")
     */
    public function event(OfferRepository $offerRepository, AdvertRepository $advertRepository)
    {
        $myOffers = $this->getUser()->getOffers()->toArray();
        $myOffersAsClient = $this->getUser()->getClientOffers()->toArray();

        $countElementMyOffers = count($myOffers);
        $countElementMyOffersAsClient = count($myOffersAsClient);

        $jsonResponse = new JsonListCalendarModel();

        if($countElementMyOffers > 0 && $countElementMyOffersAsClient === 0){
            foreach($myOffers as $item) //Mes offres
            {
                $id = $item->getId();
                $calendarModel = new CalendarViewModel();
                $calendarModel  ->setStart($item->getDateDelivery())
                                ->setUrl('/meslivraisons/offres/'.$id);
    
                $nberClient = count($item->getClients()->toArray());
    
                if($nberClient > 1){
                    $title = "Vous livrez ". $nberClient . " personnes";
                } else if($nberClient === 1) {
                    foreach($item->getClients() as $client){
                        $title = "Vous livrez ".$client->getName();
                    }
                } else {
                    $title = "Course à " . $item->getCitiesDelivery();
                }
    
                $calendarModel->setTitle($title);
                $jsonResponse->addCalendarModel($calendarModel);
            }

            return $this->json($jsonResponse, 200, ['Content-Type' => 'application/json']);
            
        } else if ($countElementMyOffersAsClient > 0 && $countElementMyOffers === 0){ 

            foreach($myOffersAsClient as $item) //Livré d'une offre
            {
                $id = $item->getId();
                $calendarModel = new CalendarViewModel();
                $calendarModel  ->setStart($item->getDateDelivery())
                                ->setTitle($item->getUser()->getName(). " vous livre")
                                ->setUrl('/meslivraisons/offres/' .$id);

                $jsonResponse->addCalendarModel($calendarModel);
            }

            return $this->json($jsonResponse, 200, ['Content-Type' => 'application/json']);

        } else if($countElementMyOffersAsClient > 0 && $countElementMyOffers > 0){

            foreach($myOffersAsClient as $item)
            {
                $id = $item->getId();
                $calendarModel = new CalendarViewModel();
                $calendarModel  ->setStart($item->getDateDelivery())
                                ->setTitle($item->getUser()->getName(). " vous livre")
                                ->setUrl('/meslivraisons/offres/' . $id);

                $jsonResponse->addCalendarModel($calendarModel);
            }

            foreach($myOffers as $item)
            {
                $calendarModel = new CalendarViewModel();
                $calendarModel  ->setStart($item->getDateDelivery())
                                ->setUrl("/mesoffres/". $item->getId());
    
                $nberClient = count($item->getClients()->toArray());
    
                if($nberClient > 1){
                    $title = "Vous livrez ". $nberClient . " personnes";
                } else if($nberClient === 1) {
                    foreach($item->getClients() as $client){
                        $title = "Vous livrez ".$client->getName();
                    }
                } else {
                    $title = "Course à " . $item->getCitiesDelivery();
                }
    
                $calendarModel->setTitle($title);
                $jsonResponse->addCalendarModel($calendarModel);
            }
            return $this->json($jsonResponse, 200, ['Content-Type' => 'application/json']);
        } else if($countElementMyOffers === 0 && $countElementMyOffersAsClient === 0){
            return $this->json($jsonResponse, 200, ['Content-Type' => 'application/json']);
        }
        else {
            return $this->json(null, 500, ['Content-Type' => 'application/json']);
        }
        
    }
}
