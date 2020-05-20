<?php

namespace App\Service;

class MailManager
{

    private $mailer;
    private $message;
    private $To;
    private $token;
    private $from = 'contact@comptoirdesvoisins.fr';

    public function __construct(string $To, ?string $token, \Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
        $this->To = $To;
        $this->token = $token;
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

    public function passwordRecuperation()
    {

        $this->message = (new \Swift_Message('Récupération du mot de passe'))
                        ->setFrom($this->from)
                        ->setTo($this->To)
                        ->setBody(
                            "Suivez ce lien pour réinitialiser votre mot de passe : ". " https://localhost:8000/connexion/motdepasse/reinitialisation/".$this->token
                        );

        $this->mailer->send($this->message);
    }

}

?>