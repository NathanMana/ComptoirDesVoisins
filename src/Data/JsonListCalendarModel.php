<?php

namespace App\Data;

use App\ViewModel\CalendarViewModel;
use Doctrine\Common\Collections\ArrayCollection;

class JsonListCalendarModel
{
    /**
     * Objet Calendar
     *
     * @var Collection|CalendarViewModel[]
     */
    private $calendarModels;
    
    public function __construct()
    {
        $this->calendarModels = new ArrayCollection();
    }

    public function getCalendarModels()
    {
        return $this->calendarModels;
    }

    public function addCalendarModel(CalendarViewModel $calendarModel): self
    {
        if (!$this->calendarModels->contains($calendarModel)) {
            $this->calendarModels[] = $calendarModel;
        }
        return $this;
    }

    public function RemoveCalendarModel(CalendarViewModel $calendarModel): self
    {
        if ($this->calendarModels->contains($calendarModel)) {
            $this->calendarModels->removeElement($calendarModel);
        }
        return $this;
    }
}

?>