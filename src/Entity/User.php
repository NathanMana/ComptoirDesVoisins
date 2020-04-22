<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;

use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(
 *  fields= {"email"},
 *  message= "L'email que vous avez indiqué est déjà utilisé"
 * )
 * @Vich\Uploadable()
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Email(message = "Veuillez rentrer un email valide")
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Regex("/^[A-Za-z0-9_~\-!\?@#\$%\^&\*\(\)\s]+$/")
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Regex("/^[A-Za-z0-9_~\-!\?@#\$%\^&\*\(\)\s]+$/")
     */
    private $lastname;

    private $code_postal;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Regex("/^[A-Za-z0-9_~\-!\?@#\$%\^&\*\(\)\s]+$/")
     */
    private $city;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Regex("/^[0-9\s]+$/")
     * @Assert\Length(min="10",
     *                max="10", 
     *                minMessage="Veuillez entrer un numéro de téléphone valide sous la forme XXXXXXXXXX",
     *                maxMessage="Veuillez entrer un numéro de téléphone valide sous la forme XXXXXXXXXX",
     * )
     */
    private $phone;

    /**
     * @var string|null
     * @ORM\Column(type="string",length=255)
     */
    private $filename;

    /**
     * @var File|null
     * @Vich\UploadableField(mapping="profile_image", fileNameProperty="filename")
     */
    private $imageFile;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min="8", minMessage="Votre mot de passe doit avoir au minimum 8 caractères")
     */
    private $password;

    /**
     * @Assert\EqualTo(propertyPath="password", message="Tu n'as pas tapé le même mot de passe")
     */
    private $confirm_password;

    private $old_password;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $resetPassword;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Advert", mappedBy="idDeliverer")
     */
    private $myDeliveries;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Advert", mappedBy="idUser", orphanRemoval=true)
     */
    private $myAdverts;

    public function __construct()
    {
        $this->myDeliveries = new ArrayCollection();
        $this->myAdverts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

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

    public function getCodePostal(): ?int
    {
        return $this->code_postal;
    }

    public function setCodePostal(string $code_postal): self
    {
        $this->code_postal = $code_postal;

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
     * @return string|null
     */
    public function getFilename(): ?string
    {
        return $this->filename;
    }

    /**
     * @param null|string $filename
     * @return User
     */
    public function setFilename(?string $filename): User
    {
        $this->filename = $filename;
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
    public function setImageFile(?File $imageFile): User
    {
        $this->imageFile = $imageFile;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getResetPassword(): ?string
    {
        return $this->resetPassword;
    }

    public function setResetPassword(string $resetPassword): self
    {
        $this->resetPassword = $resetPassword;

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

    public function getOldPassword(): ?string
    {
        return $this->old_password;
    }

    public function setOldPassword(string $old_password): self
    {
        $this->old_password = $old_password;

        return $this;
    }


    public function eraseCredentials()
    {
        
    }

    public function getSalt()
    {
        
    }

    public function getRoles():array
    {   
        return ['ROLE_USER'];
    }

    public function getUsername()
    {
        return $this->name;
    }

    /**
     * @return Collection|Advert[]
     */
    public function getMyDeliveries(): Collection
    {
        return $this->myDeliveries;
    }

    public function addMyDeliveries(Advert $myDeliveries): self
    {
        if (!$this->myDeliveries->contains($myDeliveries)) {
            $this->myDeliveries[] = $myDeliveries;
            $myDeliveries->setDeliverer($this);
        }

        return $this;
    }

    public function removeMyDeliveries(Advert $myDeliveries): self
    {
        if ($this->myDeliveries->contains($myDeliveries)) {
            $this->myDeliveries->removeElement($myDeliveries);
            // set the owning side to null (unless already changed)
            if ($myDeliveries->getDeliverer() === $this) {
                $myDeliveries->setDeliverer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Advert[]
     */
    public function getMyAdverts(): Collection
    {
        return $this->myAdverts;
    }

    public function addMyAdvert(Advert $myAdvert): self
    {
        if (!$this->myAdverts->contains($myAdvert)) {
            $this->myAdverts[] = $myAdvert;
            $myAdvert->setUser($this);
        }

        return $this;
    }

    public function removeMyAdvert(Advert $myAdvert): self
    {
        if ($this->myAdverts->contains($myAdvert)) {
            $this->myAdverts->removeElement($myAdvert);
            // set the owning side to null (unless already changed)
            if ($myAdvert->getUser() === $this) {
                $myAdvert->setUser(null);
            }
        }

        return $this;
    }
}
