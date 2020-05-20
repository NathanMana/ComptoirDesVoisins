<?php

namespace App\Service;

use App\Entity\Advert;
use App\Entity\User;
use App\Entity\Notification;
use App\Entity\Offer;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Repository\RepositoryFactory;

class NotificationManager 
{

    public function offerClient(Offer $offer, User $user)
    {
        $notification = new Notification();

        $notification   ->setObject($user->getName(). " aurait besoin de votre aide !")
                        ->setMessage($user->getName() . " aurait besoin de votre aide !")
                        ->setUser($offer->getUser())
                        ->setSeen(0)
                        ->setCreatedAt(new \DateTime());

        return $notification;
    }

    public function advertDelete(Advert $advert, User $user)
    {
        $notification = new Notification();

        $notification   ->setObject($user->getName(). " veut annuler ça demande")
                        ->setMessage($user->getName(). " veut annuler cet échange, contactez le si vous n'étiez pas au courant.")
                        ->setUser($advert->getDeliverer())
                        ->setSeen(0)
                        ->setCreatedAt(new \DateTime());

        return $notification;
    }

    public function delivererAdvert(Advert $advert, User $user)
    {
        $notification = new Notification();

        $notification   ->setObject("Votre annonce a trouvé preneur !")
                        ->setMessage("Votre annonce a été prise en charge par ". $user->getName() . ". Elle rentrera bientôt en contact avec vous !")
                        ->setSeen(false)
                        ->setUser($advert->getUser())
                        ->setCreatedAt(new \DateTime());

        return $notification;
    }

    public function advertDeleteConfirmation(Advert $advert, User $user)
    {
        $notification = new Notification();

        $notification   ->setObject("Suppression de l'annonce")
                        ->setMessage("Votre contact a confirmé l'annulation de l'annonce. Elle est donc supprimée")
                        ->setSeen(false)
                        ->setUser($advert->getUser())
                        ->setCreatedAt(new \DateTime());

        return $notification;
    }

    public function sendToEveryone(string $object, string $message, UserRepository $repository, EntityManagerInterface $manager)
    {
        $users = $repository->findAll();

        foreach($users as $user)
        {
            $notification = new Notification();
    
            $notification   ->setObject($object)
            ->setMessage($message)
            ->setSeen(false)
            ->setUser($user)
            ->setCreatedAt(new \DateTime());

            $manager->persist($notification);
        }
        $manager->flush();
    }
    
}




?>