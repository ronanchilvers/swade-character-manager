<?php

declare(strict_types=1);

namespace App\Controller\Character;

use App\Dice;
use App\Entity;
use App\Entity\Factory\Character as FactoryCharacter;
use App\Entity\Factory\Skill as FactorySkill;
use App\Filter;
use App\Service\GameData;
use Flight;

class Skills
{
    public function __construct(
        private FactoryCharacter $factory,
        private FactorySkill $skillFactory,
        private GameData $gameData,
    ) {
    }

    public function index(string $hash): void
    {
        $entity = $this->factory->forHash($hash);
        if (!$entity instanceof Entity) {
            Flight::session()->error('Unable to find character');
            Flight::redirect(Flight::getUrl('home_page'));
            return;
        }

        $errors = [];

        if ('POST' === Flight::request()->getMethod()) {
            $selected = Filter::numberArray($_POST['skills']);
            $selected = array_filter($selected);
            $result = $this->skillFactory->syncForCharacter($entity, $selected);

            if ($result->isSuccess()) {
                Flight::session()->success(
                    sprintf('Saved character %s successfully', $entity->name)
                );
                Flight::redirect(Flight::getUrl('characters_skills', ['hash' => $entity->hash]));
                return;
            }
            Flight::session()->error(
                'Sorry! There was a problem!'
            );
        } else {
            $characterSkills = $this->skillFactory->forCharacter($entity);
            $selected = [];
            foreach ($characterSkills as $skill) {
                $selected[$skill->key] = $skill->die;
            }
        }
        $skills = $this->gameData->allSkills();
        $diceOptions = Dice::validSizes();
        array_unshift($diceOptions, 0);

        Flight::render('character/skills.twig', [
            'page_title' => 'Skills',
            'entity' => $entity,
            'errors' => $errors,
            'selected' => $selected,
            'skills' => $skills,
            'dice_options' => $diceOptions,
        ]);
    }
}
