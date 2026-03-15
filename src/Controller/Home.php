<?php

declare(strict_types=1);

namespace App\Controller;

use Flight;

class Home
{
    public function index(): void
    {
        Flight::render('home/index.twig', [
            'page_title' => 'Characters',
        ]);
    }
}
