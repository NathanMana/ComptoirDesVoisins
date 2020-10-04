<?php

namespace App\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;

class LoginListener {
    private $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    public function onSecurityAuthenticationSuccess(AuthenticationEvent $event){
        $user = $event->getAuthenticationToken()->getUser();
        if($user instanceof User){
            $user->setLastLogin(new \DateTime());
            
            $this->manager->flush();
        }
    }
}

?>