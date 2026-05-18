<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Factory\Campaign;
use App\Entity\Factory\Character;
use Flight;

class Home
{
    public function __construct(
        private Character $factory,
        private Campaign $campaignFactory
    ) {
    }

    public function index(): void
    {
        $user = Flight::user();
        $characters = $this->factory->forUser(
            (int) $user->id,
        );
        $campaignIds = [];
        foreach ($characters as $character) {
            $campaignIds[(int) $character->campaign] = (int) $character->campaign;
        }
        $campaigns = $this->campaignFactory->namesForIds(
            $campaignIds
        );

        Flight::render('home/index.twig', [
            'page_title' => 'Characters',
            'characters' => $characters,
            'campaigns' => $campaigns,
        ]);
    }
}
