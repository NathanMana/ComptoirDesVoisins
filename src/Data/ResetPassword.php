<?php

namespace App\Data;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

class ResetPassword 
{
    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     * @Assert\Length(min="8", minMessage="Votre mot de passe doit avoir au minimum 8 caractères")
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