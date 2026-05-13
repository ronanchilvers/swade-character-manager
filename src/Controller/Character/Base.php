<?php

declare(strict_types=1);

namespace App\Controller\Character;

use App\Entity;
use App\Entity\Factory\Character as FactoryCharacter;
use App\Filter;
use Flight;

class Base
{
    public function __construct(
        private FactoryCharacter $factory,
    ) {
    }

    public function index(string $hash): void
    {
        $entity = $this->factory->forHash($hash);
        if (!$entity instanceof Entity) {
            Flight::session()->error(
                'Unable to find character'
            );
            Flight::redirect(Flight::getUrl('home_page'));
        }

        $this->createOrConcept($entity);
    }

    public function create(): void
    {
        $this->createOrConcept(new Entity());
    }

    public function delete(string $hash): void
    {
        $entity = $this->factory->forUserHash(
            (int) Flight::session()->user->id,
            $hash
        );
        if (!$entity instanceof Entity) {
            Flight::session()->error('Unable to find character');
            Flight::redirect(Flight::getUrl('home_page'));
            return;
        }

        $confirmedName = trim((string) ($_POST['confirm_name'] ?? ''));
        if (0 !== strcasecmp($confirmedName, (string) $entity->name)) {
            Flight::session()->error('Type the character name to confirm deletion');
            Flight::redirect(Flight::getUrl('home_page'));
            return;
        }

        if ((int) ($entity->campaign ?? 0) > 0) {
            Flight::session()->error('This character must leave the campaign before deletion');
            Flight::redirect(Flight::getUrl('home_page'));
            return;
        }

        $result = $this->factory->delete($entity);
        if ($result->isSuccess()) {
            Flight::session()->success(
                sprintf('Deleted character %s successfully', $entity->name)
            );
        } else {
            Flight::session()->error('Sorry! There was a problem deleting that character');
        }

        Flight::redirect(Flight::getUrl('home_page'));
    }

    protected function createOrConcept(Entity $entity): void
    {
        $errors = [];
        if ("POST" == Flight::request()->getMethod()) {
            $entity->name = Filter::noTags($_POST['name']);
            $entity->concept = Filter::noTags($_POST['concept'] ?? '');
            $errors = $this->factory->validate($entity);
            if (!$errors) {
                $result = $this->factory->upsert($entity);
                $errors = $result->errors();
            }
            if (!$errors) {
                Flight::session()->success(
                    sprintf('Saved character %s successfully', $entity->name)
                );
                Flight::redirect(
                    Flight::getUrl('characters_hindrances', ['hash' => $entity->hash])
                );
                return;
            }
            Flight::session()->error(
                sprintf('Sorry! There was a problem!')
            );
        }

        Flight::render('character/concept.twig', [
            'page_title' => 'The Basics',
            'entity' => $entity,
            'errors' => $errors,
        ]);
        return;
    }
}
