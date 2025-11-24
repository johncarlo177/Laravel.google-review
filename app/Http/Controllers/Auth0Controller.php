<?php

namespace App\Http\Controllers;

use App\Support\Auth\Auth0\Auth0Manager;

class Auth0Controller extends Controller
{
    private Auth0Manager $auth0;

    public function __construct()
    {
        $this->auth0 = new Auth0Manager;
    }

    public function login()
    {
        return $this->auth0->redirectToAuth0Login();
    }

    public function logout()
    {
        return $this->auth0->redirectToAuth0Logout();
    }

    public function handleCallback()
    {
        return $this->auth0->handleCallback();
    }
}
