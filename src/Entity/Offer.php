<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\OfferRepository")
 */
class Offer
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\City", inversedBy="offers")
     */
    private $citiesDelivery;

    /**
     * @Assert\Regex("/^[^<>#§µ]+$/", message="Les caractères spéciaux suivant ne sont pas autorisés : ^,<,>,#,§,µ")
     */
    private $citiesDeliveryName;

    /**
     * @Assert\Length(
     *                 min="5", max="5", 
     *                 minMessage = "Rentrez une ville valide",
     *                 maxMessage = "Rentrez une ville valide"
     * )
     * @Assert\Regex(
     *              "/^[0-9]+$/",
     *              message="Rentrez une ville valide"
     * )
     */
    private $codeCities;

    /**
     * @ORM\Column(type="string", length=600)
     */
    private $groceryType;

    /**
     * @ORM\Column(type="text")
     * @Assert\Regex("/^[^<>#§µ]+$/", message = "Les caractères spéciaux suivants ne sont pas autorisés : <,>,#,§,µ")
     */
    private $message;

    /**
     * @ORM\Column(type="date")
     */
    private $dateDelivery;

    /**
     * @ORM\Column(type="smallint")
     */
    private $available;

    /**
     * @ORM\Column(type="smallint")
     */
    private $limited;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="offers")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $user;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="clientOffers")
     */
    private $clients;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Regex("/^[^<>#§µ]+$/", message="Les caractères spéciaux suivant ne sont pas autorisés : ^,<,>,#,§,µ")
     */
    private $title;


    public function __construct()
    {
        $this->clients = new ArrayCollection();
        $this->citiesDelivery = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCitiesDelivery(): Collection
    {
        return $this->citiesDelivery;
    }

    public function addCitiesDelivery(City $citiesDelivery): self
    {
        if (!$this->citiesDelivery->contains($citiesDelivery)) {
            $this->citiesDelivery[] = $citiesDelivery;
        }

        return $this;
    }

    public function removeCitiesDelivery(City $citiesDelivery): self
    {
        if ($this->citiesDelivery->contains($citiesDelivery)) {
            $this->citiesDelivery->removeElement($citiesDelivery);
        }

        return $this;
    }

    public function getCitiesDeliveryName(): ?string
    {
        return $this->citiesDeliveryName;
    }

    public function setCitiesDeliveryName(string $citiesDeliveryName): self
    {
        $this->citiesDeliveryName = $citiesDeliveryName;

        return $this;
    }

    public function getCodeCities(): ?string
    {
        return $this->codeCities;
    }

    public function setCodeCities(string $codeCities): self
    {
        $this->codeCities = $codeCities;

        return $this;
    }

    public function getGroceryType(): ?string
    {
        return $this->groceryType;
    }

    public function setGroceryType(string $groceryType): self
    {
        $this->groceryType = $groceryType;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getDateDelivery(): ?\DateTimeInterface
    {
        return $this->dateDelivery;
    }

    public function setDateDelivery(\DateTimeInterface $dateDelivery): self
    {
        $this->dateDelivery = $dateDelivery;

        return $this;
    }

    public function getAvailable(): ?int
    {
        return $this->available;
    }

    public function setAvailable(int $available): self
    {
        $this->available = $available;

        return $this;
    }

    public function getLimited(): ?int
    {
        return $this->limited;
    }

    public function setLimited(int $limited): self
    {
        $this->limited = $limited;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getClients(): Collection
    {
        return $this->clients;
    }

    public function addClient(User $client): self
    {
        if (!$this->clients->contains($client)) {
            $this->clients[] = $client;
        }

        return $this;
    }

    public function removeClient(User $client): self
    {
        if ($this->clients->contains($client)) {
            $this->clients->removeElement($client);
        }

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }


    /* PAS EN BDD */
    private $timezone;
    private $groceryTypeArray;

    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    public function setTimezone(string $timezone): self
    {
        $this->timezone = $timezone;

        return $this;
    }

    public function getGroceryTypeArray(): ?array
    {
        return $this->groceryTypeArray;
    }

    public function setGroceryTypeArray(array $groceryTypeArray): self
    {
        $this->groceryTypeArray = $groceryTypeArray;

        return $this;
    }

}
