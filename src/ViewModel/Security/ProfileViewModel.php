<?php

namespace App\ViewModel\Security;

use App\Entity\User;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints\File as ConstraintsFile;
use Symfony\Component\Validator\Constraints\Regex;

class ProfileViewModel {
    
    private $name;
    private $lastname;
    private $email;
    private $city;
    private $imageFile;
    private $codeCity;
    private $phone;

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @return File|null
     */
    public function getImageFile(): ?File 
    {
        return $this->imageFile;
    }

    /**
     * @param File|null $imageFile
     * @return User
     */
    public function setImageFile(?File $imageFile): ProfileViewModel
    {
        $this->imageFile = $imageFile;
        if($this->imageFile instanceof UploadedFile){
            $this->updatedAt = new \DateTime('now');
        }
        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;
        return $this;
    }

    public function getCodeCity(): ?string
    {
        return $this->codeCity;
    }

    public function setCodeCity(string $codeCity): self
    {
        $this->codeCity = $codeCity;
        return $this;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('name', new Regex("/^[^<>#§µ]+$/", ["message" => "Les caractères spéciaux autorisés sont les suivants : ^,<,>,#,§,µ"]));
        $metadata->addPropertyConstraint('lastname', new Regex("/^[^<>#§µ]+$/", ["message" => "Les caractères spéciaux autorisés sont les suivants : ^,<,>,#,§,µ"]));
        $metadata->addPropertyConstraint('imageFile', new ConstraintsFile(array('maxSize' => "160M", "maxSizeMessage"=>"L'image insérée est trop lourde. Taille maximale autorisée = 20Mo", "mimeTypes"=> ["image/jpeg","image/png","image/svg"], "mimeTypesMessage" => "L'image n'est pas valide. Les extensions acceptées sont jpg, jpeg, png et svg")));
        $metadata->addPropertyConstraint('phone',  new Length(array('min' => 10, 'max' => 10, "minMessage" =>"Veuillez entrer un numéro de téléphone valide sous la forme XXXXXXXXXX", "maxMessage" =>"Veuillez entrer un numéro de téléphone valide sous la forme XXXXXXXXXX")));

    }
}



?>