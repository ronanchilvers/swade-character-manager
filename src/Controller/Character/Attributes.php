<?php

declare(strict_types=1);

namespace App\Controller\Character;

use App\Entity;
use App\Entity\Factory\Character as FactoryCharacter;
use App\Service\GameData;
use Flight;

class Attributes
{
    public function __construct(
        private FactoryCharacter $factory,
        private GameData $gameData,
    ) {
    }

    public function index(string $hash): void
    {
        $entity = $this->factory->forHash($hash);
        if (!$entity instanceof Entity) {
            Flight::session()->flash('Unable to find character', 'error');
            Flight::redirect(Flight::getUrl('home_page'));
            return;
        }

        if ('POST' === Flight::request()->getMethod()) {
            $result = $this->characterAttributes->processSubmission($entity, $_POST);

            if (empty($result['errors']) && empty($result['form_errors'])) {
                Flight::redirect(Flight::getUrl('characters_skills', ['hash' => $entity->hash]));
                return;
            }
        } else {
            $result = $this->characterAttributes->viewData($entity);
        }

        Flight::render('character/attributes.twig', [
            'page_title' => 'Choose Attributes',
            'entity' => $result['entity'],
            'errors' => $result['errors'],
            'form_errors' => $result['form_errors'],
            'attribute_fields' => $result['attribute_fields'],
            'attribute_options' => $result['attribute_options'],
            'allocation' => $result['allocation'],
        ]);
    }
}
