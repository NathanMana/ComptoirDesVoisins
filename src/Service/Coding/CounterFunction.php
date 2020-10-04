<?php

namespace App\Service\Coding;

use App\Entity\Advert;
use App\ViewModel\CounterViewModel;

class CounterFunction {

    private $listCounterVM = [];
    
    function sortByDate( $a, $b ) {
        return strtotime($b->getDate()->format("Y-m-j")) - strtotime($a->getDate()->format("Y-m-j"));
    }

    function main($myAdvertsWithDeliverer, $myDeliveriesForAdvert, $myOffersWithClient, $ClientOfOffer, $translator){

        $listCounterVM = [];

        foreach($myAdvertsWithDeliverer as $item){
            $counterVM = new CounterViewModel();
            $title = $item->getDeliverer()->getName() . " vous livre à " . $item->getCity()->getName();
            
            $counterVM  ->setTitle($title)
                        ->setType('myAdvert')
                        ->setDate($item->getDeadline())
                        ->setAdvert($item);
            array_push($this->listCounterVM, $counterVM);
        }
    
        foreach($myDeliveriesForAdvert as $item){
            $counterVM = new CounterViewModel();
            $title = "Vous livrez " . $item->getUser()->getName() . " à " . $item->getCity()->getName();
            
            $counterVM  ->setTitle($title)
                        ->setType('delivererForAdvert')
                        ->setDate($item->getDeadline())
                        ->setAdvert($item);
            array_push($this->listCounterVM, $counterVM);
        }
    
        foreach($myOffersWithClient as $item){
            $counterVM = new CounterViewModel();
            $title = "Vous livrez ";
            $month = $translator->trans($item->getDateDelivery()->format("F"));
            $date = $item->getDateDelivery()->format("j"). " " .$month. " " .$item->getDateDelivery()->format("Y");
            $count = 0;
            foreach($item->getClients() as $client){
                $count++;
                if($count === count($item->getClients()->toArray())){
                    $title .= $client->getName();
                } else if($count === count($item->getClients()->toArray() - 1)){
                    $title .= $client->getName() . " et ";
                } else {
                    $title .= $client->getName();
                }
            }
            $title .= " à";
            foreach($item->getCitiesDelivery() as $city){
                $title .= " " . $city->getName();
            }
    
            $title .= " le " . $date;
            $clients = $item->getClients()->toArray();
            $counterVM  ->setTitle($title)
                        ->setType('myOffer')
                        ->setDate($item->getDateDelivery())
                        ->setOffer($item);
            array_push($this->listCounterVM, $counterVM);
        }
    
        foreach($ClientOfOffer as $item){
            $counterVM = new CounterViewModel();
    
            $month = $translator->trans($item->getDateDelivery()->format("F"));
            $date = $item->getDateDelivery()->format("j"). " " .$month. " " .$item->getDateDelivery()->format("Y");
            $title =  $item->getUser()->getName() ." vous livre à ";
            foreach($item->getCitiesDelivery() as $city){
                $title .= " " . $city->getName();
            }
            $title .= " le " . $date;
            $counterVM  ->setTitle($title)
                        ->setType('clientOfOffer')
                        ->setDate($item->getDateDelivery())
                        ->setOffer($item);
            array_push($this->listCounterVM, $counterVM);
        }
        
        usort($this->listCounterVM, array($this, "sortByDate"));
        return $this->listCounterVM;
    }
}

?>