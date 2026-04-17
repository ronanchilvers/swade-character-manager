<?php

declare(strict_types=1);

namespace App\Controller\Character;

use App\Character\Sheet as SheetPresenter;
use App\Entity;
use App\Entity\Factory\Character as FactoryCharacter;
use App\Entity\Factory\Hindrance as FactoryHindrance;
use App\Entity\Factory\Skill as FactorySkill;
use App\Service\Data\Manager;
use Flight;

class Sheet
{
    public function __construct(
        private FactoryCharacter $factory,
        private FactoryHindrance $hindranceFactory,
        private FactorySkill $skillFactory,
        private Manager $manager,
        private SheetPresenter $presenter,
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

        $hindrances = $this->hindranceFactory->forCharacter($entity);
        $skills = $this->skillFactory->forCharacter($entity);

        $sheet = $this->presenter->build(
            $entity,
            $hindrances,
            $skills,
            [],
            $this->manager,
            $this->factory,
        );

        Flight::render('character/sheet.twig', [
            'page_title' => $entity->name,
            'entity' => $entity,
            'sheet' => $sheet,
        ]);
    }
}
