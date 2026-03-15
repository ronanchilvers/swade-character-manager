<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Factory\Character as FactoryCharacter;
use Flight;

class Character
{
    public function __construct(private FactoryCharacter $factory)
    {
    }

    public function index(): void
    {
        Flight::render(
            'character/create.twig',
            [
                'page_title' => 'Create a Character'
            ]
        );
    }

    public function create(): void
    {
        $data = [
           'concept' => htmlspecialchars(strip_tags($_POST['concept'])),
        ];
        $this->factory->insert($data);

        Flight::redirect('/');
    }
}
