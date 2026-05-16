<?php

declare(strict_types=1);

namespace App\Controller\Character;

use App\Character\Sheet as SheetPresenter;
use App\Entity;
use App\Entity\Factory\Campaign as FactoryCampaign;
use App\Entity\Factory\Campaign\Member as FactoryMember;
use App\Entity\Factory\Character as FactoryCharacter;
use App\Entity\Factory\Edge as FactoryEdge;
use App\Entity\Factory\Gear as FactoryGear;
use App\Entity\Factory\Hindrance as FactoryHindrance;
use App\Entity\Factory\Result;
use App\Entity\Factory\Skill as FactorySkill;
use App\Entity\Factory\Weapon as FactoryWeapon;
use App\Entity\Factory\User as FactoryUser;
use App\Service\Data\Manager;
use Flight;

class Sheet
{
    private const STATE_FIELDS = ['wounds', 'fatigue', 'incapacitated', 'bennies'];

    public function __construct(
        private FactoryCharacter $factory,
        private FactoryHindrance $hindranceFactory,
        private FactorySkill $skillFactory,
        private FactoryEdge $edgeFactory,
        private FactoryGear $gearFactory,
        private FactoryWeapon $weaponFactory,
        private FactoryUser $userFactory,
        private Manager $manager,
        private SheetPresenter $presenter,
        private FactoryCampaign $campaignFactory,
        private FactoryMember $memberFactory,
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

        $currentUser = Flight::user();
        $isOwner = Flight::isSuperUser($currentUser) || ($entity->user == $currentUser->id);
        $readOnly = false;

        if (!$isOwner) {
            if (!$this->canViewAsCampaignMember($entity, (int) $currentUser->id)) {
                Flight::session()->error('Unable to find character');
                Flight::redirect(Flight::getUrl('home_page'));
                return;
            }
            $readOnly = true;
        }

        $user = false;
        if (Flight::isSuperSession()) {
            if (Flight::user() && (Flight::user()->id != $entity->user)) {
                $user = $this->userFactory->byId((int) $entity->user);
            }
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
            'user' => $user,
            'sheet' => $sheet,
            'read_only' => $readOnly,
        ]);
    }

    public function updateState(string $hash): void
    {
        $entity = $this->resolveForJson($hash);
        if (!$entity instanceof Entity) {
            return;
        }
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
        if (!$entity instanceof Entity) {
            return;
        }
        $payload = $this->jsonBody();
        $entity->notes = (string) ($payload['notes'] ?? '');

        $this->respond($this->factory->update($entity));
    }

    public function updateGear(string $hash): void
    {
        $entity = $this->resolveForJson($hash);
        if (!$entity instanceof Entity) {
            return;
        }
        $rows = $this->jsonBody()['rows'] ?? [];
        $this->respond($this->gearFactory->syncForCharacter($entity, is_array($rows) ? $rows : []));
    }

    public function updateWeapons(string $hash): void
    {
        $entity = $this->resolveForJson($hash);
        if (!$entity instanceof Entity) {
            return;
        }
        $rows = $this->jsonBody()['rows'] ?? [];
        $this->respond($this->weaponFactory->syncForCharacter($entity, is_array($rows) ? $rows : []));
    }

    private function resolveForJson(string $hash): ?Entity
    {
        $entity = $this->factory->forHash($hash);
        if (!$entity instanceof Entity) {
            Flight::json(['ok' => false, 'errors' => ['Not found']], 404);
            return null;
        }
        $user = Flight::user();
        if (!Flight::isSuperUser($user) && ($entity->user != $user->id)) {
            Flight::json(['ok' => false, 'errors' => ['Not found']], 404);
            return null;
        }

        return $entity;
    }

    private function canViewAsCampaignMember(Entity $character, int $userId): bool
    {
        if (!$character->campaign) {
            return false;
        }
        $campaign = $this->campaignFactory->byId((int) $character->campaign);
        if (!$campaign instanceof Entity) {
            return false;
        }
        return $this->memberFactory->isMember($campaign, $userId);
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

        Flight::json(['ok' => false, 'errors' => $result->errors()], 422);
    }
}
