<?php

namespace Framework;

use Framework\Session;

class Ownership
{
    /**
     * Check if current user owns the listings(should not be able to delete/edit listings that he didnt post)
     * 
     * @param int $id
     * 
     * @return bool
     */

    public static function isOwner($id)
    {
        $userSession = Session::get('user');
        if ($userSession && isset($userSession['id'])) {
            return $userSession['id'] === $id;
        }

        return false;
    }
}
