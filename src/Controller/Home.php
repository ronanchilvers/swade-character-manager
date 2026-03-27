<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Factory\Character;
use App\Service\Data\Hindrances;
use App\Service\Data\Manager;
use Flight;

class Home
{
    public function __construct(
        private Character $factory
    ) {
    }

    public function index(): void
    {
        $characters = $this->factory->forUser(
            (int) Flight::session()->user->id
        );
        Flight::render('home/index.twig', [
            'page_title' => 'Characters',
            'characters' => $characters,
        ]);
    }
}
