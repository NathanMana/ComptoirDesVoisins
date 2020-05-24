<?php

namespace App\Service;

use App\Entity\Notification;

class MailManager
{

    private $mailer;
    private $message;
    private $To;
    private $from = 'comptoirdesvoisins@gmail.com';

    public function __construct(string $To, \Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
        $this->To = $To;
    }

    public function registration()
    {
        $this->message = (new \Swift_Message('Récupération du mot de passe'))
                        ->setFrom($this->from)
                        ->setTo($this->To)
                        ->setBody(
                            "L'équipe Comptoir des Voisins est ravie de vous accueillir au sein de sa plateforme. Nous sommes là pour vous aider, n'hésitez pas à nous envoyer un message en cas de problème.
                            
                            Cordialement,
                            
                            L'équipe Comptoir des Voisins"
                        );

        $this->mailer->send($this->message);
    }

    public function passwordRecuperation(string $token)
    {

        $this->message = (new \Swift_Message('Récupération du mot de passe'))
                        ->setFrom($this->from)
                        ->setTo($this->To)
                        ->setBody(
                            "Suivez ce lien pour réinitialiser votre mot de passe : ". " https://localhost:8000/connexion/motdepasse/reinitialisation/".$token
                        );

        $this->mailer->send($this->message);
    }

    public function notifIntoMail(Notification $notif)
    {

        $this->message = (new \Swift_Message())
                        ->setFrom($this->from)
                        ->setTo($this->To)
                        ->setBody(
                            "<h1>". $notif->getObject() ."</h1><p>". $notif->getMessage() ."</p>"
                        );

        $this->mailer->send($this->message);
    }

}

?>