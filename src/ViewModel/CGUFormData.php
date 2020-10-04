<?php

namespace App\ViewModel;

class CGUFormData
{
    private $CGUValidated;

    public function getCGUValidated()
    {
        return $this->CGUValidated;
    }

    public function setCGUValidated(bool $CGUValidated): self
    {
        $this->CGUValidated = $CGUValidated;
        return $this;
    }
}

?>