<?php

declare(strict_types=1);

namespace App\Controller;

use Flight;

class Auth
{
    public function index()
    {
        Flight::render('auth/index.twig', [
        ]);
    }
}
