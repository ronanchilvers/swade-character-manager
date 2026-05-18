<?php

declare(strict_types=1);

namespace App\Controller\Character;

use App\Entity;
use App\Entity\Factory\Character as FactoryCharacter;
use Flight;

class Settings
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

        $errors = false;
        // if ("POST" == Flight::request()->getMethod()) {
        //     $entity->name = Filter::noTags($_POST['name']);
        //     $entity->concept = Filter::noTags($_POST['concept'] ?? '');
        //     $errors = $this->factory->validate($entity);
        //     if (!$errors) {
        //         $result = $this->factory->upsert($entity);
        //         $errors = $result->errors();
        //     }
        //     if (!$errors) {
        //         Flight::session()->success(
        //             sprintf('Saved character %s successfully', $entity->name)
        //         );
        //         Flight::redirect(
        //             Flight::getUrl('characters_concept', ['hash' => $entity->hash])
        //         );
        //         return;
        //     }
        //     Flight::session()->error(
        //         sprintf('Sorry! There was a problem!')
        //     );
        // }

        Flight::render('character/settings.twig', [
            'page_title' => 'Settings',
            'entity' => $entity,
            'errors' => $errors,
        ]);
    }
}
