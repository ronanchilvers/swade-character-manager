<?php

declare(strict_types=1);

namespace App\Entity\Factory;

use App\Entity;
use App\Entity\Factory;
use App\Entity\Factory\Campaign\Member;
use Flight;
use Respect\Validation\ValidatorBuilder as v;
use Ronanchilvers\Utility\Str;
use RuntimeException;

class Campaign extends Factory
{
    public function __construct(
        \flight\database\SimplePdo $pdo,
        \App\Entity\Validator $validator,
        private Member $memberFactory,
    ) {
        parent::__construct($pdo, $validator);
    }

    public function byId(int $id): ?Entity
    {
        return $this->one(
            $this->prefix('id') . ' = ?',
            [$id],
        );
    }

    public function forHash(string $hash): ?Entity
    {
        return $this->one(
            $this->prefix('hash') . ' = ?',
            [$hash],
        );
    }

    public function forUser(int $userId): array
    {
        return $this->find(
            $this->prefix('user') . ' = ?',
            [$userId],
            $this->prefix('created') . ' DESC',
        );
    }

    public function forMemberUser(int $userId): array
    {
        return $this->find(
            'campaign_id IN (SELECT campaign_member_campaign_id FROM campaign_members WHERE campaign_member_user_id = ?)',
            [$userId],
            $this->prefix('created') . ' DESC',
        );
    }

    public function invitePath(Entity $campaign): string
    {
        return Flight::getUrl('campaigns_join', ['hash' => $campaign->hash]);
    }

    public function allWithSummary(): array
    {
        $rows = $this->pdo->fetchAll(
            implode(' ', [
                'SELECT c.*,',
                'u.user_email AS owner_email,',
                'u.user_firstname AS owner_firstname,',
                'u.user_lastname AS owner_lastname,',
                'COUNT(DISTINCT cm.campaign_member_id) AS member_count,',
                'COUNT(DISTINCT ch.character_id) AS character_count',
                'FROM campaigns c',
                'LEFT JOIN users u ON u.user_id = c.campaign_user',
                'LEFT JOIN campaign_members cm ON cm.campaign_member_campaign_id = c.campaign_id',
                'LEFT JOIN characters ch ON ch.character_campaign = c.campaign_id',
                'GROUP BY c.campaign_id, u.user_id',
                'ORDER BY c.campaign_created DESC',
            ])
        );

        return array_map(fn ($row) => $this->entityFromSummaryRow($row), $rows);
    }

    public function getValidationRules(): array
    {
        return [
            'hash' => v::not(v::blank()),
            'user' => v::intVal()->greaterThan(0),
            'name' => v::not(v::blank()),
            'description' => v::oneOf(v::nullType(), v::stringType()),
        ];
    }

    protected function beforeInsert(Entity $entity): void
    {
        $entity->user = Flight::session()->user->id;
        $entity->hash = Str::token(32);
    }

    protected function beforeUpdate(Entity $entity): void
    {
        if (0 == strlen($entity->hash)) {
            $entity->hash = Str::token(32);
        }
    }

    protected function afterInsert(Entity $entity): void
    {
        $result = $this->memberFactory->ensureMember(
            $entity,
            (int) $entity->user,
            Member::ROLE_OWNER,
        );

        if (!$result->isSuccess()) {
            throw new RuntimeException(implode('; ', $result->errors()));
        }
    }

    private function entityFromSummaryRow(iterable $row): Entity
    {
        $data = [];
        foreach ($row as $key => $value) {
            if (str_starts_with((string) $key, $this->getPrefix())) {
                $data[$this->unprefix((string) $key)] = $value;
                continue;
            }
            $data[$key] = $value;
        }

        return new Entity($data);
    }
}
