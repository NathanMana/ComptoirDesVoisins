<?php

namespace App\Entity;

use Serializable;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\File\File;

use Doctrine\Common\Collections\ArrayCollection;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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
class User implements UserInterface, Serializable
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
     * @Assert\Regex("/^[^<>#§µ]+$/",
     *                  message="Les caractères spéciaux autorisés sont les suivants : ^,<,>,#,§,µ")
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Regex("/^[A-Za-z0-9\-\&\/\s]+$/",
     *              message="Vous ne pouvez pas rentrer de caractères spéciaux dans ce champ")
     */
    private $lastname;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Regex("/^[^<>#§µ]+$/",
     *                  message="Les caractères spéciaux autorisés sont les suivants : ^,<,>,#,§,µ")
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
     * @ORM\Column(type="string",length=255, nullable=true)
     */
    private $filename;

    /**
     * @var File|null
     * @Vich\UploadableField(mapping="profile_image", fileNameProperty="filename")
     * @Assert\Image(
     *    mimeTypes = {"image/jpeg",
     *                 "image/png",
     *                 "image/svg"
     *                },
     *    mimeTypesMessage = "L'image n'est pas valide. Les extensions acceptées sont jpg, jpeg, png et svg"
     * )
     * @Assert\File(
     *  maxSize = "20M",
     *  maxSizeMessage = "L'image insérée est trop lourde. Taille maximale autorisée = 20Mo"
     * )
     */
    private $imageFile;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Regex("/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*\W)[a-zA-Z0-9\S]{8,}$/",
     *                 message="Votre mot de passe doit contenir, une lettre minuscule, une lettre majuscule, un caractère spécial, un nombre et doit faire au moins 8 caractères")
     * @Assert\NotCompromisedPassword
     */
    private $password;

    /**
     * @Assert\EqualTo(propertyPath="password", message="Tu n'as pas tapé le même mot de passe")
     */
    private $confirm_password;

    private $new_password;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $resetPassword;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Advert", mappedBy="deliverer")
     */
    private $myDeliveries;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Advert", mappedBy="user", orphanRemoval=true)
     */
    private $myAdverts;

    /**
     * @ORM\Column(type="integer")
     */
    private $Points;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Notification", mappedBy="user", orphanRemoval=true)
     */
    private $notifications;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $codeCity;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Offer", mappedBy="user", orphanRemoval=true)
     */
    private $offers;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Offer", mappedBy="clients")
     */
    private $clientOffers;

    private $username;
    private $salt;

    public function __construct()
    {
        $this->myDeliveries = new ArrayCollection();
        $this->myAdverts = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->offers = new ArrayCollection();
        $this->clientOffers = new ArrayCollection();
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
        if($this->imageFile instanceof UploadedFile){
            $this->updatedAt = new \DateTime('now');
        }
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

    public function setResetPassword(?string $resetPassword): self
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

    public function getNewPassword(): ?string
    {
        return $this->new_password;
    }

    public function setNewPassword(string $new_password): self
    {
        $this->new_password = $new_password;

        return $this;
    }


    public function eraseCredentials()
    {
        
    }

    public function getSalt()
    {
        $this->salt = null;
        return  $this->salt;
    }

    public function getRoles():array
    {   
        return ['ROLE_USER'];
    }

    public function getUsername()
    {
        $this->username = $this->getName();
        return $this->username;
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

    public function getPoints(): ?int
    {
        return $this->Points;
    }

    public function setPoints(int $Points): self
    {
        $this->Points = $Points;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection|Notification[]
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): self
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications[] = $notification;
            $notification->setUser($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): self
    {
        if ($this->notifications->contains($notification)) {
            $this->notifications->removeElement($notification);
            // set the owning side to null (unless already changed)
            if ($notification->getUser() === $this) {
                $notification->setUser(null);
            }
        }

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

    /**
     * @return Collection|Offer[]
     */
    public function getOffers(): Collection
    {
        return $this->offers;
    }

    public function addOffer(Offer $offer): self
    {
        if (!$this->offers->contains($offer)) {
            $this->offers[] = $offer;
            $offer->setUser($this);
        }

        return $this;
    }

    public function removeOffer(Offer $offer): self
    {
        if ($this->offers->contains($offer)) {
            $this->offers->removeElement($offer);
            // set the owning side to null (unless already changed)
            if ($offer->getUser() === $this) {
                $offer->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Offer[]
     */
    public function getClientOffers(): Collection
    {
        return $this->clientOffers;
    }

    public function addClientOffer(Offer $clientOffer): self
    {
        if (!$this->clientOffers->contains($clientOffer)) {
            $this->clientOffers[] = $clientOffer;
            $clientOffer->addClient($this);
        }

        return $this;
    }

    public function removeClientOffer(Offer $clientOffer): self
    {
        if ($this->clientOffers->contains($clientOffer)) {
            $this->clientOffers->removeElement($clientOffer);
            $clientOffer->removeClient($this);
        }

        return $this;
    }

    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->name,
            $this->salt,
            $this->password
        ));
    }

    /**
     * @see \Serializable::unserialize()
     */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->name,
            $this->salt,
            $this->password
        ) = unserialize($serialized, ['allowed_classes' => false]);
    }
}
