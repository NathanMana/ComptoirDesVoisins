<?php

namespace App\ViewModel;

use Symfony\Component\Validator\Constraints as Assert;

class ForgottenPassword
{
    /**
     * @var string
     * @Assert\Email(message = "Veuillez rentrer un email valide")
     */

    private $email;


    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email) :self
    {
        $this->email = $email;
        return $this;
    }

}



?>