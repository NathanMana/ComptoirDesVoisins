<?php

namespace App\Service;

use App\Entity\User;

class UserManager
{
    public function checkAdmin(User $user)
    {
        if(in_array("ROLE_ADMIN", $user->getRoles()))
        {
            return true;
        }
        return false;
    }
}

?>