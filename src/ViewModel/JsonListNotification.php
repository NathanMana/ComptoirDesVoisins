<?php

namespace App\ViewModel;

use App\Entity\Notification;
use Doctrine\Common\Collections\ArrayCollection;

class JsonListNotification
{
    /**
     * @var Notification[]
     */
    private $notifications;

    public function getNotifications(): ?array
    {
        return $this->notifications;
    }

    public function setNotifications(Array $notifications): ?self
    {
        $this->notifications = $notifications;
        return $this;
    }
}

?>