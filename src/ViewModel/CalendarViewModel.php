<?php

namespace App\ViewModel;

use DateTimeInterface;

class CalendarViewModel
{
    private $start;
    private $end;
    private $title;
    private $url;

    public function getStart(): DateTimeInterface
    {
        return $this->start;
    }

    public function setStart(DateTimeInterface $start): self
    {
        $this->start = $start;
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getEnd(): ?DateTimeInterface
    {
        return $this->end;
    }

    public function setEnd(?DateTimeInterface $end): ?self
    {
        $this->end = $end;
        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): ?self
    {
        $this->url = $url;
        return $this;
    }

}


?>