<?php

namespace App\EventListener;


use DateTime;
use App\Entity\Advert;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class OutOfDateListener {

    public function postPersist(LifecycleEventArgs $args){
        $entity = $args->getObject();

        if(!$entity instanceof Advert){
            return;
        } else {
            $date = new DateTime();
            $dateWithoutHours = $date->format("Y-m-j");
            $deadline = $entity->getDeadline();

            if($deadline < $dateWithoutHours){
                $manager = $args->getObjectManager();
                $manager->remove($entity);
            }

        }

        

    }

}


?>