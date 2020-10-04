<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\HelpRepository")
 */
class Advert
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     * @Assert\Regex("/^[^<>#§µ]+$/", message = "Les caractères spéciaux suivants ne sont pas autorisés : <,>,#,§,µ")
     */
    private $message;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="myDeliveries")
     */
    private $deliverer;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="myAdverts")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * 
     */
    private $user;

    /**
     * @ORM\Column(type="boolean")
     */
    private $Cancellation;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\City", inversedBy="adverts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $city;

    /**
     * @Assert\Regex("/^[^<>#§µ]+$/", message="Les caractères spéciaux autorisés sont les suivants : ^,<,>,#,§,µ")
     */
    private $_cityName;

    /**
     * @Assert\Length(
    *                 min="5", max="5", 
    *                 minMessage = "Rentrez une ville valide",
    *                 maxMessage = "Rentrez une ville valide"
     * )
     * @Assert\Regex("/^[0-9]+$/", message="Rentrez une ville valide")
     */
    private $codeCity;

    /**
     * @ORM\Column(type="datetime")
     */
    private $deadline;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isDelivered;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Regex("/^[^<>#§µ]+$/", message="Les caractères spéciaux suivant ne sont pas autorisés : ^,<,>,#,§,µ")
     */
    private $title;


    /* PAS EN BDD */
    private $timezone;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getDeliverer(): ?User
    {
        return $this->deliverer;
    }

    public function setDeliverer(?User $deliverer): self
    {
        $this->deliverer = $deliverer;

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

    public function getCancellation(): ?bool
    {
        return $this->Cancellation;
    }

    public function setCancellation(bool $Cancellation): self
    {
        $this->Cancellation = $Cancellation;

        return $this;
    }

    public function getCityName(): ?string
    {
        return $this->_cityName;
    }

    public function setCityName(?string $cityName): self
    {
        $this->_cityName = $cityName;

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

    public function getDeadline(): ?\DateTimeInterface
    {
        return $this->deadline;
    }

    public function setDeadline(\DateTimeInterface $deadline): self
    {
        $this->deadline = $deadline;

        return $this;
    }

    public function getIsDelivered(): ?bool
    {
        return $this->isDelivered;
    }

    public function setIsDelivered(bool $isDelivered): self
    {
        $this->isDelivered = $isDelivered;

        return $this;
    }

    public function getCity(): ?City
    {
        return $this->city;
    }

    public function setCity(?City $city): self
    {
        $this->city = $city;

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
    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    public function setTimezone(string $timezone): self
    {
        $this->timezone = $timezone;

        return $this;
    }
}
