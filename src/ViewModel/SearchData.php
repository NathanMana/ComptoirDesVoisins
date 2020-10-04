<?php

namespace App\ViewModel;

use Symfony\Component\Validator\Constraints as Assert;

class SearchData
{
    /**
     * @var string
     * @Assert\NotBlank
     *
     */
    public $q = '';

    /**
     * @var bool
     */
    public $type;

    /**
     * Radio buttons
     *
     * @var array
     */
    public $groceryType;

    /**
     * @var int
     */
    public $distance;

    /**
     * @var float
     */
    public $lon;

    /**
     * @var float
     */
    public $lat;

}


?>