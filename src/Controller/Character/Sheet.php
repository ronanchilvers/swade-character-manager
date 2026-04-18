<?php

declare(strict_types=1);

namespace App\Controller\Character;

use App\Character\Sheet as SheetPresenter;
use App\Entity;
use App\Entity\Factory\Character as FactoryCharacter;
use App\Entity\Factory\Edge as FactoryEdge;
use App\Entity\Factory\Gear as FactoryGear;
use App\Entity\Factory\Hindrance as FactoryHindrance;
use App\Entity\Factory\Result;
use App\Entity\Factory\Skill as FactorySkill;
use App\Entity\Factory\Weapon as FactoryWeapon;
use App\Service\Data\Manager;
use Flight;

class Sheet
{
    private const STATE_FIELDS = ['wounds', 'fatigue', 'bennies', 'conviction'];

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

    public function index(string $hash): void
    {
        $entity = $this->resolve($hash);
        if (!$entity instanceof Entity) {
            return;
        }

        $hindrances = $this->hindranceFactory->forCharacter($entity);
        $skills = $this->skillFactory->forCharacter($entity);
        $edges = $this->edgeFactory->forCharacter($entity);
        $gear = $this->gearFactory->forCharacter($entity);
        $weapons = $this->weaponFactory->forCharacter($entity);

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

        Flight::render('character/sheet.twig', [
            'page_title' => $entity->name,
            'entity' => $entity,
            'sheet' => $sheet,
        ]);
    }

    public function updateState(string $hash): void
    {
        $entity = $this->resolveForJson($hash);
        $payload = $this->jsonBody();

        foreach (self::STATE_FIELDS as $field) {
            if (!array_key_exists($field, $payload)) {
                continue;
            }
            $entity->{$field} = max(0, (int) $payload[$field]);
        }

        $this->respond($this->factory->update($entity));
    }

    public function updateNotes(string $hash): void
    {
        $entity = $this->resolveForJson($hash);
        $payload = $this->jsonBody();
        $entity->notes = (string) ($payload['notes'] ?? '');

        $this->respond($this->factory->update($entity));
    }

    public function updateGear(string $hash): void
    {
        $entity = $this->resolveForJson($hash);
        $rows = $this->jsonBody()['rows'] ?? [];
        $this->respond($this->gearFactory->syncForCharacter($entity, is_array($rows) ? $rows : []));
    }

    public function updateWeapons(string $hash): void
    {
        $entity = $this->resolveForJson($hash);
        $rows = $this->jsonBody()['rows'] ?? [];
        $this->respond($this->weaponFactory->syncForCharacter($entity, is_array($rows) ? $rows : []));
    }

    private function resolve(string $hash): ?Entity
    {
        $entity = $this->factory->forHash($hash);
        if (!$entity instanceof Entity) {
            Flight::session()->error('Unable to find character');
            Flight::redirect(Flight::getUrl('home_page'));
            return null;
        }

        return $entity;
    }

    private function resolveForJson(string $hash): Entity
    {
        $entity = $this->factory->forHash($hash);
        if (!$entity instanceof Entity) {
            Flight::response()
                ->status(404)
                ->header('Content-Type', 'application/json')
                ->write(json_encode(['ok' => false, 'errors' => ['Not found']]))
                ->send();
            exit;
        }

        return $entity;
    }

    private function jsonBody(): array
    {
        $raw = Flight::request()->getBody();
        $data = json_decode($raw, true);

        return is_array($data) ? $data : [];
    }

    private function respond(Result $result): void
    {
        Flight::response()->header('Content-Type', 'application/json');
        if ($result->isSuccess()) {
            Flight::json(['ok' => true]);
            return;
        }

        Flight::response()->status(422);
        Flight::json(['ok' => false, 'errors' => $result->errors()]);
    }
}
