<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity;
use App\Entity\Factory\Character as FactoryCharacter;
use App\Filter;
use Flight;

class Character
{
    public function __construct(private FactoryCharacter $factory)
    {
    }

    public function create(): void
    {
        $this->createOrConcept(new Entity());
    }

    public function concept(string $hash): void
    {
        $entity = $this->factory->forHash($hash);
        if (!$entity instanceof Entity) {
            Flight::session()->flash(
                'Unable to find character',
                'error'
            );
            Flight::redirect(Flight::getUrl('home_page'));
        }

        $this->createOrConcept($entity);
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

    protected function createOrConcept(Entity $entity): void
    {
        $errors = [];
        if ("POST" == Flight::request()->getMethod()) {
            $entity->name = Filter::noTags($_POST['name']);
            if (
                !($errors = $this->factory->validate($entity)) &&
                $this->factory->upsert($entity)
            ) {
                Flight::redirect(
                    Flight::getUrl('characters_hindrances', ['hash' => $entity->hash])
                );
                return;
            }
        }

        Flight::render('character/concept.twig', [
            'page_title' => 'Character Concept',
            'entity' => $entity,
            'errors' => $errors,
        ]);
        return;
    }
}
