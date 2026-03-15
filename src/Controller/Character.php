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

    public function create(): void
    {
        Flight::render('character/create.twig', [
            'page_title' => 'Create a Character'
        ]);
    }

    public function createPost(): void
    {
        $data = [
           'concept' => htmlspecialchars(strip_tags($_POST['concept'])),
        ];
        if ($errors = $this->factory->validate($data) || !$this->factory->insert($data)) {
            Flight::render('character/create.twig', [
                'page_title' => 'Create a Character',
                'errors' => $errors,
            ]);
            return;
        }

        Flight::redirect('/');
    }
}
