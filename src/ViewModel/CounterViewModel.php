<?php

namespace App\ViewModel;

use App\Entity\Offer;
use App\Entity\Advert;
use App\Entity\User;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;

class CounterViewModel {
    private $title;
    private $type;
    private $date;
    private $clients;
    private $offer;
    private $advert;

    function getDate(): ?DateTime
    {
        return $this->date;
    }

    function setDate(DateTime $date): ?self
    {
        $this->date = $date;
        return $this;
    }

    function getOffer(): ?Offer
    {
        return $this->offer;
    }

    function setOffer(Offer $offer): ?self
    {
        $this->offer = $offer;
        return $this;
    }

    function getAdvert(): ?Advert
    {
        return $this->advert;
    }

    function setAdvert(Advert $advert): ?self
    {
        $this->advert = $advert;
        return $this;
    }

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