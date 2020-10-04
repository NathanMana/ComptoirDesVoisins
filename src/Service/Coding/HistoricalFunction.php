<?php

namespace App\Service\Coding;

use App\Entity\User;
use App\ViewModel\Account\HistoricalViewModel;

class HistoricalFunction {

    public function main(Array $advert, Array $offer, User $user): Array
    {
        // A CHANGER AVEC LE TITRE SUR LES ANNONCES
        $result = [];

        foreach($advert as $item){
            $historicalVM = new HistoricalViewModel();

            if($item->getUser()->getId() != $user->getId()){
                $title = "Votre échange avec ". $item->getDeliverer()->getName();
                $user = $item->getDeliverer();
            } else {
                $title = "Votre échange avec ". $item->getUser()->getName();
                $user = $item->getUser();
            }

            $type = "advert";
            $id = $item->getId();
            
            $historicalVM   ->setTitle($title)
                            ->setUser($user)
                            ->setDate($item->getDeadline())
                            ->setType($type)
                            ->setId($id);

            array_push($result, $historicalVM);
        }

        // foreach($offer as $item){
        //     $historicalVM = new HistoricalViewModel();

        //     if($item->getUser()->getId() != $user->getId()){
        //         $title = "Votre échange avec ". $item->getDeliverer()->getName();
        //         $user = $item->getDeliverer();
        //     } else {
        //         $title = "Votre échange avec ". $item->getUser()->getName();
        //         $user = $item->getUser();
        //     }

        //     $type = "advert";
        //     $id = $item->getId();
            
        //     $historicalVM   ->setTitle("test")
        //                     ->setUser($user)
        //                     ->setDate($item->getDeadline())
        //                     ->setType($type)
        //                     ->setId($id);

        //     array_push($result, $historicalVM);
        // }

        return $result;
    }
}

?>