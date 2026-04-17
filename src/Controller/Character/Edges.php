<?php

declare(strict_types=1);

namespace App\Controller\Character;

use App\Budget\HindranceBudget;
use App\Budget\SkillBudget;
use App\Entity;
use App\Entity\Factory\Character as FactoryCharacter;
use App\Entity\Factory\Edge as FactoryEdge;
use App\Entity\Factory\Hindrance as FactoryHindrances;
use App\Entity\Factory\Skill as FactorySkill;
use App\Filter;
use App\Service\Data\Edges as DataEdges;
use App\Service\Data\Manager;
use Flight;

class Edges
{
    public function __construct(
        private FactoryCharacter $factory,
        private FactoryEdge $edgeFactory,
        private FactorySkill $skillFactory,
        private FactoryHindrances $hindrancesFactory,
        private Manager $manager,
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

        $characterEdges = $errors = [];
        if ('POST' === Flight::request()->getMethod()) {
            $selected = Filter::numberArray($_POST['edges'] ?? []);
            $selected = array_filter(
                $selected,
                static fn (int $count): bool => $count > 0
            );
            $result = $this->edgeFactory->syncForCharacter(
                $entity,
                $selected
            );

            if ($result->isSuccess()) {
                Flight::session()->success(
                    sprintf('Saved character %s successfully', $entity->name)
                );
                Flight::redirect(Flight::getUrl('characters_edges', ['hash' => $entity->hash]));
                return;
            }

            $errors = $result->errors();
            Flight::session()->error(
                'Sorry! There was a problem!'
            );
        } else {
            $characterEdges = $this->edgeFactory->forCharacter($entity);
            $selected = [];
            foreach ($characterEdges as $edge) {
                $selected[$edge->key] = $edge->count;
            }
        }

        $characterSkills = $this->skillFactory->forCharacter($entity);
        $characterHindrances = $this->hindrancesFactory->forCharacter($entity);
        $budgets = [
            new SkillBudget($entity, $characterSkills),
            new HindranceBudget($entity, $characterHindrances)->setLabel('Hindrance Points'),
        ];

        Flight::render('character/edges.twig', [
            'page_title' => 'Edges',
            'entity' => $entity,
            'edges_by_category' => $this->groupByCategory(
                $this->manager->getType(DataEdges::class)->all()
            ),
            'errors' => $errors,
            'selected' => $selected,
            'budgets' => $budgets,
        ]);
    }

    private function groupByCategory(array $edges): array
    {
        $grouped = [];
        foreach ($edges as $edge) {
            $grouped[$edge['category']][] = $edge;
        }

        return $grouped;
    }
}
