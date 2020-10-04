<?php

namespace App\ViewModel\Account;

use App\Entity\User;

class HistoricalViewModel {

    private $title;
    private $type;
    private $date;
    private $user;
    private $id;
    private $clients;

    
    function getTitle(): ?string
    {
        return $this->title;
    }

    function setTitle(string $title): ?self
    {
        $this->title = $title;
        return $this;
    }

    function getType(): ?string
    {
        return $this->type;
    }

    function setType(string $type): ?self
    {
        $this->type = $type;
        return $this;
    }

    function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    function setDate(\DateTimeInterface $date): ?self
    {
        $this->date = $date;
        return $this;
    }

    function getId(): ?int
    {
        return $this->id;
    }

    function setId(int $id): ?self
    {
        $this->id = $id;
        return $this;
    }

    function getUser(): ?User
    {
        return $this->user;
    }

    function setUser(User $user): ?self
    {
        $this->user = $user;
        return $this;
    }

    function getClients():?array
    {
        return $this->clients;
    }

    function setClients(Array $clients): ?self
    {
        $this->clients = $clients;
        return $this;
    }
}

?>