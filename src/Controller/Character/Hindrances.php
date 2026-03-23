<?php

declare(strict_types=1);

namespace App\Controller\Character;

use App\Entity;
use App\Entity\Factory\Character as FactoryCharacter;
use App\Service\CharacterAttributes;
use App\Service\CharacterHindrances;
use App\Service\CharacterSkills;
use App\Service\GameData;
use Flight;

class Hindrances
{
    public function __construct(
        private FactoryCharacter $factory,
        private CharacterHindrances $characterHindrances,
        private CharacterAttributes $characterAttributes,
        private CharacterSkills $characterSkills,
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
            $result = $this->characterHindrances->processSubmission(
                (int) $entity->id,
                $_POST['hindrances'] ?? []
            );

            if (empty($result['errors'])) {
                Flight::redirect(Flight::getUrl('characters_attributes', ['hash' => $entity->hash]));
                return;
            }

            $selected = $result['selected'];
            $errors = $result['errors'];
        } else {
            $selected = $this->characterHindrances->selectedForCharacter((int) $entity->id);
            $errors = [];
        }

        Flight::render('character/hindrances.twig', [
            'page_title' => 'Choose Hindrances',
            'entity'     => $entity,
            'hindrances' => $this->gameData->allHindrances(),
            'selected'   => $selected,
            'errors' => $errors,
            'remaining_points' => $this->characterHindrances->remainingPoints($selected),
            'max_points' => $this->characterHindrances->maxPoints(),
        ]);
    }
}
