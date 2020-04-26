<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AdvertRepository")
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
     * @Assert\Regex("/^[^<>#§µ]+$/",
     *              message = "Les caractères spéciaux suivants ne sont pas autorisés : <,>,#,§,µ")
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
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="boolean")
     */
    private $communication;

    /**
     * @ORM\Column(type="boolean")
     */
    private $Cancellation;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Regex("/^[^<>#§µ]+$/",
     *                  message="Les caractères spéciaux autorisés sont les suivants : ^,<,>,#,§,µ")
     */
    private $City;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $code_city;

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

    public function getCommunication(): ?bool
    {
        return $this->communication;
    }

    public function setCommunication(bool $communication): self
    {
        $this->communication = $communication;

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

    public function getCity(): ?string
    {
        return $this->City;
    }

    public function setCity(string $City): self
    {
        $this->City = $City;

        return $this;
    }

    public function getCodeCity(): ?int
    {
        return $this->code_city;
    }

    public function setCodeCity(string $code_city): self
    {
        $this->code_city = $code_city;

        return $this;
    }
}
