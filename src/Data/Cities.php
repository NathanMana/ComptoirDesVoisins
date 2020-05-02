<?php

namespace App\Data;

use Symfony\Component\Validator\Constraints as Assert;

class Cities 
{
    /**
     * @Assert\Regex("/^[^<>#§µ]+$/",
     *                  message="Les caractères spéciaux autorisés sont les suivants : ^,<,>,#,§,µ")
     * @Assert\NotNull
     */
    private $city;

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): string
    {
        $this->city = $city;
        return $this->city;
    }
}

?>