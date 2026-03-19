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
        $errors = [];
        if ("POST" == Flight::request()->getMethod()) {
            $data = [
               'concept' => htmlspecialchars(strip_tags($_POST['concept'])),
            ];
            if (
                !($errors = $this->factory->validate($data)) &&
                ($values = $this->factory->insert($data))
            ) {
                Flight::redirect(sprintf('/hindrances/%s', $values['hash']));
                return;
            }
        }

        Flight::render('character/create.twig', [
            'page_title' => 'Create a Character',
            'errors' => $errors,
        ]);
        return;
    }

    public function hindrances(string $hash): void
    {
        $errors = [];
        Flight::render('character/hindrances.twig', [
            'page_title' => 'Choose Hindrances',
            'errors' => $errors,
        ]);
        return;
    }
}
