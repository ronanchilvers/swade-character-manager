<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Factory\Character;
use Flight;

class Home
{
    public function __construct(private Character $factory)
    {
    }

    public function index(): void
    {
        Flight::session()->name = uniqid();

        $characters = $this->factory->all();
        Flight::render('home/index.twig', [
            'page_title' => 'Characters',
            'characters' => $characters,
        ]);
    }
}
