<?php

namespace App\Data;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

class ChangePassword 
{
    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     * @Assert\NotCompromisedPassword
     * @Assert\Regex("/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*\W)[a-zA-Z0-9\S]{8,}$/",
     *                 message="Votre mot de passe doit contenir au moins, une lettre minuscule, une lettre majuscule, un caractère spécial, un nombre et doit faire au moins 8 caractères")
     */
    private $new_password;

    /**
     * @var string
     * @Assert\EqualTo(propertyPath="new_password", message="Tu n'as pas tapé le même mot de passe")
     */
    private $confirm_password;

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }
    
    public function getConfirmPassword(): ?string
    {
        return $this->confirm_password;
    }

    public function setConfirmPassword(string $confirm_password): self
    {
        $this->confirm_password = $confirm_password;

        return $this;
    }

    public function getNewPassword(): ?string
    {
        return $this->new_password;
    }

    public function setNewPassword(string $new_password): self
    {
        $this->new_password = $new_password;

        return $this;
    }
}

?>