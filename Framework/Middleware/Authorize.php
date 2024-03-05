<?php

namespace Framework\Middleware;

use Framework\Session;

class Authorize
{
    /**
     * Check of user if authenticated
     *
     * @return bool
     */

    public function isAuth()
    {
        return Session::has('user');
    }

    /**
     * Handle user request
     * 
     * @param string $role
     * 
     * @return bool
     */

    public function handle($role)
    {
        if ($role === 'guest' && $this->isAuth()) {
            return header('Location: /');
        } elseif ($role === 'auth' && !$this->isAuth()) {
            return header('Location: /auth/login');
        }
    }
}
