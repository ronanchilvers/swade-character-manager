<?php

declare(strict_types=1);

namespace App\Entity\Factory;

use App\Entity;
use App\Entity\Factory;
use App\Entity\Validator;
use App\Service\Data\Manager;
use App\Service\Data\Skills;
use Flight;
use flight\database\SimplePdo;
use Respect\Validation\ValidatorBuilder as v;
use Ronanchilvers\Utility\Str;
use RuntimeException;

class Character extends Factory
{
    private const ATTRIBUTE_FIELDS = [
        'agility' => [
            'name' => 'Agility',
            'description' => 'A measure of a character’s nimbleness,
            dexterity, and general coordination.'
        ],
        'smarts' => [
            'name' => 'Smarts',
            'description' => 'Measures raw intelligence, mental
            acuity, and how fast a heroine thinks on her
            feet. '
        ],
        'spirit' => [
            'name' => 'Spirit',
            'description' => 'Self-confidence, backbone, and
            willpower, used to resist social attacks and fear.'
        ],
        'strength' => [
            'name' => 'Strength',
            'description' => 'Physical power and fitness. It’s
            also used as the basis of a warrior’s damage in
            hand-to-hand combat, and to determine how
            much he can wear or carry'
        ],
        'vigor' => [
            'name' => 'Vigor',
            'description' => 'An individual’s endurance,
            resistance to disease, poison, or toxins, and
            how much physical damage she can take
            before she can’t go on'
        ],
    ];
    private const DEFAULT_PACE = 6;

    public function __construct(
        SimplePdo $pdo,
        Validator $validator,
        private Skill $skillFactory,
        private Manager $manager,
    ) {
        parent::__construct($pdo, $validator);
    }

    public function attributeFields(): array
    {
        return static::ATTRIBUTE_FIELDS;
    }

    public function forUser(int $id)
    {
        return $this->find(
            $this->prefix('user') . ' = ?',
            [$id],
        );
    }

    public function forHash(string $hash)
    {
        return $this->one(
            $this->prefix('hash') . ' = ?',
            [$hash],
        );
    }

    public function forUserHash(int $userId, string $hash): ?Entity
    {
        return $this->one(
            $this->prefix('user') . ' = ? AND ' . $this->prefix('hash') . ' = ?',
            [$userId, $hash],
        );
    }

    public function forCampaign(Entity $campaign): array
    {
        return $this->find(
            $this->prefix('campaign') . ' = ?',
            [(int) $campaign->id],
            $this->prefix('name') . ' ASC',
        );
    }

    public function forCampaignAndUser(Entity $campaign, int $userId): array
    {
        return $this->find(
            $this->prefix('campaign') . ' = ? AND ' . $this->prefix('user') . ' = ?',
            [(int) $campaign->id, $userId],
            $this->prefix('name') . ' ASC',
        );
    }

    public function forUserWithoutCampaign(int $userId): array
    {
        return $this->find(
            $this->prefix('user') . ' = ? AND ' . $this->prefix('campaign') . ' IS NULL',
            [$userId],
            $this->prefix('name') . ' ASC',
        );
    }

    public function joinCampaign(Entity $campaign, Entity $character): Result
    {
        $campaignId = (int) $campaign->id;
        $currentCampaignId = (int) ($character->campaign ?? 0);
        if ($currentCampaignId > 0 && $currentCampaignId !== $campaignId) {
            return new Result(['Character already belongs to another campaign']);
        }
        if ($currentCampaignId === $campaignId) {
            return new Result();
        }

        return $this->updateCampaign($character, $campaignId);
    }

    public function leaveCampaign(Entity $character): Result
    {
        return $this->updateCampaign($character, null);
    }

    public function delete(Entity $entity): Result
    {
        if ((int) ($entity->campaign ?? 0) > 0) {
            return new Result(['Character must leave the campaign before deletion']);
        }

        try {
            $deleted = $this->pdo->delete(
                $this->getTableName(),
                $this->prefix('id') . ' = ?',
                [(int) $entity->id]
            );

            if (1 !== $deleted) {
                return new Result()->addError('Unable to delete character');
            }

            return new Result();
        } catch (\Exception $ex) {
            return new Result()->addError($ex->getMessage());
        }
    }

    public function getValidationRules(): array
    {
        return [
            'hash' => v::not(v::blank()),
            'user' => v::intVal()->greaterThan(0),
            'name' => v::not(v::blank()),
            'agility' => v::intVal()->in([4, 6, 8, 10, 12]),
            'smarts' => v::intVal()->in([4, 6, 8, 10, 12]),
            'spirit' => v::intVal()->in([4, 6, 8, 10, 12]),
            'strength' => v::intVal()->in([4, 6, 8, 10, 12]),
            'vigor' => v::intVal()->in([4, 6, 8, 10, 12]),
            'campaign' => v::oneOf(v::nullType(), v::intVal()->greaterThan(0)),
            'sharing' => v::intVal()->in([0,1]),
        ];
    }

    protected function beforeInsert(Entity $entity): void
    {
        $entity->user = Flight::session()->user->id;
        $entity->hash = Str::token(32);
    }

    protected function afterInsert(Entity $entity): void
    {
        $result = $this->skillFactory->insertCoreForCharacter(
            $entity,
            $this->manager->getType(Skills::class)
        );

        if (!$result->isSuccess()) {
            throw new RuntimeException(implode('; ', $result->errors()));
        }
    }

    protected function beforeUpdate(Entity $entity): void
    {
        $entity->pace = static::DEFAULT_PACE;
        $entity->toughness = 2 + ceil($entity->vigor / 2);

        // Update character parry
        $skill = $this->skillFactory->forCharacterAndKey(
            $entity,
            Skill::SKILL_FIGHTING
        );
        if ($skill instanceof Entity) {
            $entity->parry = 2 + ceil($skill->die / 2);
        }
    }

    private function updateCampaign(Entity $character, ?int $campaignId): Result
    {
        if ((int) ($character->id ?? 0) <= 0) {
            return new Result(['Unable to update character campaign']);
        }

        try {
            $this->pdo->update(
                $this->getTableName(),
                [$this->prefix('campaign') => $campaignId],
                $this->prefix('id') . ' = ?',
                [(int) $character->id],
            );
            $character->campaign = $campaignId;

            return new Result();
        } catch (\Exception $ex) {
            return new Result()->addError($ex->getMessage());
        }
    }
}
