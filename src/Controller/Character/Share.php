<?php

declare(strict_types=1);

namespace App\Controller\Character;

use App\Character\Sheet as SheetPresenter;
use App\Entity;
use App\Entity\Factory\Character as FactoryCharacter;
use App\Entity\Factory\Edge as FactoryEdge;
use App\Entity\Factory\Gear as FactoryGear;
use App\Entity\Factory\Hindrance as FactoryHindrance;
use App\Entity\Factory\Skill as FactorySkill;
use App\Entity\Factory\Weapon as FactoryWeapon;
use App\Service\Data\Manager;
use Flight;

class Share
{
    public function __construct(
        private FactoryCharacter $factory,
        private FactoryHindrance $hindranceFactory,
        private FactorySkill $skillFactory,
        private FactoryEdge $edgeFactory,
        private FactoryGear $gearFactory,
        private FactoryWeapon $weaponFactory,
        private Manager $manager,
        private SheetPresenter $presenter,
    ) {
    }

    public function index(string $token): void
    {
        $entity = $this->factory->forShareToken($token);
        if (!$entity instanceof Entity) {
            Flight::notFound();
            return;
        }

        $hindrances = $this->hindranceFactory->forCharacter($entity);
        $skills     = $this->skillFactory->forCharacter($entity);
        $edges      = $this->edgeFactory->forCharacter($entity);
        $gear       = $this->gearFactory->forCharacter($entity);
        $weapons    = $this->weaponFactory->forCharacter($entity);

        $sheet = $this->presenter->build(
            $entity,
            $hindrances,
            $skills,
            $edges,
            $this->manager,
            $this->factory,
            $gear,
            $weapons,
        );

        Flight::render('character/share.twig', [
            'page_title' => $entity->name,
            'entity'     => $entity,
            'sheet'      => $sheet,
        ]);
    }
}
