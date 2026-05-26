<?php

declare(strict_types=1);

namespace App\Controller\Character;

use App\Dice;
use App\Entity;
use App\Entity\Factory\Character as FactoryCharacter;
use App\Entity\Factory\Skill as FactorySkill;
use App\Filter;
use App\Service\Data\Manager;
use App\Service\Data\Skills as SkillsData;
use App\Service\Sources;
use Exception;
use Flight;

class Skills
{
    public function __construct(
        private FactoryCharacter $factory,
        private FactorySkill $skillFactory,
        private Manager $manager,
        private Sources $sources,
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

        $skillService = $this->manager->getType(SkillsData::class);
        $characterSkills = $errors = [];
        if ('POST' === Flight::request()->getMethod()) {
            try {
                $selected = Filter::numberArray($_POST['skills'] ?? []);
                $selected = array_filter($selected);
                $result = $this->skillFactory->syncForCharacter(
                    $entity,
                    $selected,
                    $skillService
                );
                if (!$result->isSuccess()) {
                    throw new Exception('Unable to save character skills');
                }

                $result = $this->factory->update($entity);
                if (!$result->isSuccess()) {
                    throw new Exception('Unable to update character');
                }
                Flight::session()->success(
                    sprintf('Saved character %s successfully', $entity->name)
                );
                Flight::reload();
                // Flight::redirect(Flight::getUrl('characters_edges', ['hash' => $entity->hash]));
                return;
            } catch (Exception $e) {
                Flight::session()->error(
                    $e->getMessage() ?: 'Sorry! There was a problem'
                );
            }
        } else {
            $characterSkills = $this->skillFactory->forCharacter($entity);
            $selected = [];
            foreach ($characterSkills as $skill) {
                $selected[$skill->key] = $skill->die;
            }
        }
        $enabledSources = $this->sources->selectedFromString($entity->sources ?? null);
        $coreSkills = $skillService->coreForSources($enabledSources);
        $nonCoreSkills = $skillService->nonCoreForSources($enabledSources);
        $diceOptions = Dice::validSizes();
        array_unshift($diceOptions, 0);

        Flight::render('character/skills.twig', [
            'page_title' => 'Skills',
            'entity' => $entity,
            'errors' => $errors,
            'selected' => $selected,
            'skills' => $coreSkills + $nonCoreSkills,
            'dice_options' => $diceOptions,
        ]);
    }
}
