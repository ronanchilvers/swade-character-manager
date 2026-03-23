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

class Skills
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
            $result = $this->characterSkills->processSubmission($entity, $_POST['skills'] ?? []);

            if (empty($result['errors']) && empty($result['form_errors'])) {
                Flight::redirect(Flight::getUrl('characters_skills', ['hash' => $entity->hash]));
                return;
            }
        } else {
            $result = $this->characterSkills->viewData($entity);
        }

        Flight::render('character/skills.twig', [
            'page_title' => 'Choose Skills',
            'entity' => $result['entity'],
            'errors' => $result['errors'],
            'form_errors' => $result['form_errors'],
            'skill_groups' => $result['skill_groups'],
            'allocation' => $result['allocation'],
        ]);
    }
}
