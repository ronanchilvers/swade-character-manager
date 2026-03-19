<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity;
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
        $entity = new Entity();
        if ("POST" == Flight::request()->getMethod()) {
            $entity->concept = htmlspecialchars(strip_tags($_POST['concept']));
            if (
                !($errors = $this->factory->validate($entity)) &&
                $this->factory->insert($entity)
            ) {
                Flight::redirect(
                    Flight::getUrl('characters_hindrances', ['hash' => $entity->hash])
                );
                return;
            }
        }

        Flight::render('character/create.twig', [
            'page_title' => 'Create a Character',
            'entity' => $entity,
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
