<?php

declare(strict_types=1);

namespace App\Entity\Factory\Campaign;

use App\Entity;
use App\Entity\Factory;
use App\Entity\Factory\Result;
use Respect\Validation\ValidatorBuilder as v;

class Member extends Factory
{
    public const ROLE_OWNER = 'owner';
    public const ROLE_MEMBER = 'member';

    public static function roles(): array
    {
        return [
            static::ROLE_OWNER,
            static::ROLE_MEMBER,
        ];
    }

    protected function getTableName(): string
    {
        return 'campaign_members';
    }

    protected function getPrefix(): string
    {
        return 'campaign_member_';
    }

    public function forCampaign(Entity $campaign): array
    {
        return $this->find(
            $this->prefix('campaign_id') . ' = ?',
            [(int) $campaign->id],
            $this->prefix('role') . ' ASC, ' . $this->prefix('created') . ' ASC',
        );
    }

    public function forUser(int $userId): array
    {
        return $this->find(
            $this->prefix('user_id') . ' = ?',
            [$userId],
            $this->prefix('created') . ' DESC',
        );
    }

    public function isMember(Entity $campaign, int $userId): bool
    {
        return $this->one(
            $this->prefix('campaign_id') . ' = ? AND ' . $this->prefix('user_id') . ' = ?',
            [(int) $campaign->id, $userId],
        ) instanceof Entity;
    }

    public function leaveCampaign(Entity $campaign, int $userId): Result
    {
        if ($this->hasAssignedCharacters($campaign, $userId)) {
            return new Result(['Remove your characters from the campaign before leaving']);
        }

        try {
            $this->pdo->delete(
                $this->getTableName(),
                $this->prefix('campaign_id') . ' = ? AND ' . $this->prefix('user_id') . ' = ?',
                [(int) $campaign->id, $userId],
            );

            return new Result();
        } catch (\Exception $ex) {
            return new Result()->addError($ex->getMessage());
        }
    }

    public function ensureMember(Entity $campaign, int $userId, string $role = self::ROLE_MEMBER): Result
    {
        if ($this->isMember($campaign, $userId)) {
            return new Result();
        }

        $entity = new Entity([
            'campaign_id' => (int) $campaign->id,
            'user_id' => $userId,
            'role' => $role,
        ]);
        $errors = $this->validate($entity);
        if ($errors) {
            return new Result($errors);
        }

        return $this->insert($entity);
    }

    public function getValidationRules(): array
    {
        return [
            'campaign_id' => v::intVal()->greaterThan(0),
            'user_id' => v::intVal()->greaterThan(0),
            'role' => v::in(static::roles()),
        ];
    }

    private function hasAssignedCharacters(Entity $campaign, int $userId): bool
    {
        $row = $this->pdo->fetchRow(
            'SELECT COUNT(*) AS assigned_count FROM characters WHERE character_campaign = ? AND character_user = ?',
            [(int) $campaign->id, $userId],
        );

        return $row !== null && (int) $row['assigned_count'] > 0;
    }
}
