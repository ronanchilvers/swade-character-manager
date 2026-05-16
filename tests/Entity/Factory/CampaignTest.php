<?php

declare(strict_types=1);

namespace Tests\Entity\Factory;

use App\Entity;
use App\Entity\Factory\Campaign;
use App\Entity\Factory\Campaign\Member;
use App\Entity\Factory\Result;
use App\Entity\Validator;
use Flight;
use flight\database\SimplePdo;
use flight\util\Collection;
use PHPUnit\Framework\TestCase;

class CampaignTest extends TestCase
{
    public function testValidationAcceptsRequiredFields(): void
    {
        $campaign = new Entity([
            'hash' => str_repeat('a', 32),
            'user' => 7,
            'name' => 'The Flood',
            'description' => '',
        ]);

        self::assertSame([], $this->factory()->validate($campaign));
    }

    public function testForHashFindsCampaignByHash(): void
    {
        $hash = str_repeat('b', 32);
        $pdo = $this->createMock(SimplePdo::class);
        $pdo->expects(self::once())
            ->method('fetchRow')
            ->with(
                'SELECT * FROM campaigns WHERE campaign_hash = ?',
                [$hash],
            )
            ->willReturn(new Collection([
                'campaign_id' => 12,
                'campaign_hash' => $hash,
                'campaign_user' => 7,
                'campaign_name' => 'The Flood',
            ]));

        $campaign = $this->factory($pdo)->forHash($hash);

        self::assertInstanceOf(Entity::class, $campaign);
        self::assertSame(12, $campaign->id);
        self::assertSame($hash, $campaign->hash);
    }

    public function testByIdFindsCampaignById(): void
    {
        $pdo = $this->createMock(SimplePdo::class);
        $pdo->expects(self::once())
            ->method('fetchRow')
            ->with(
                'SELECT * FROM campaigns WHERE campaign_id = ?',
                [12],
            )
            ->willReturn(new Collection([
                'campaign_id' => 12,
                'campaign_hash' => 'testhash',
                'campaign_user' => 7,
                'campaign_name' => 'The Flood',
            ]));

        $campaign = $this->factory($pdo)->byId(12);

        self::assertInstanceOf(Entity::class, $campaign);
        self::assertSame(12, $campaign->id);
        self::assertSame('testhash', $campaign->hash);
    }

    public function testForUserAndForMemberUserUseExpectedQueries(): void
    {
        $pdo = $this->createMock(SimplePdo::class);
        $pdo->expects(self::exactly(2))
            ->method('fetchAll')
            ->willReturnCallback(function (string $sql, array $params): array {
                if (str_contains($sql, 'campaign_user = ?')) {
                    self::assertSame([7], $params);
                    self::assertStringContainsString('ORDER BY campaign_created DESC', $sql);

                    return [new Collection(['campaign_id' => 1, 'campaign_user' => 7])];
                }

                self::assertSame(
                    'SELECT * FROM campaigns WHERE campaign_id IN (SELECT campaign_member_campaign_id FROM campaign_members WHERE campaign_member_user_id = ?) ORDER BY campaign_created DESC',
                    $sql,
                );
                self::assertSame([7], $params);

                return [new Collection(['campaign_id' => 2, 'campaign_user' => 9])];
            });

        $factory = $this->factory($pdo);

        self::assertSame(1, $factory->forUser(7)[0]->id);
        self::assertSame(2, $factory->forMemberUser(7)[0]->id);
    }

    public function testInvitePathUsesCampaignJoinRoute(): void
    {
        Flight::map('getUrl', fn (string $alias, array $params = []): string => $alias . ':' . $params['hash']);

        self::assertSame(
            'campaigns_join:abc123',
            $this->factory()->invitePath(new Entity(['hash' => 'abc123'])),
        );
    }

    public function testAllWithSummaryMapsCampaignAndSummaryColumns(): void
    {
        $pdo = $this->createMock(SimplePdo::class);
        $pdo->expects(self::once())
            ->method('fetchAll')
            ->with(self::callback(fn (string $sql): bool => str_contains($sql, 'COUNT(DISTINCT cm.campaign_member_id)')))
            ->willReturn([
                new Collection([
                    'campaign_id' => 4,
                    'campaign_hash' => 'hash',
                    'campaign_user' => 7,
                    'campaign_name' => 'The Flood',
                    'owner_email' => 'owner@example.com',
                    'member_count' => 3,
                    'character_count' => 5,
                ]),
            ]);

        $campaign = $this->factory($pdo)->allWithSummary()[0];

        self::assertSame(4, $campaign->id);
        self::assertSame('The Flood', $campaign->name);
        self::assertSame('owner@example.com', $campaign->owner_email);
        self::assertSame(3, $campaign->member_count);
        self::assertSame(5, $campaign->character_count);
    }

    public function testBeforeUpdateRegeneratesHashWhenReset(): void
    {
        $pdo = $this->createMock(SimplePdo::class);
        $pdo->expects(self::once())
            ->method('update')
            ->with(
                'campaigns',
                self::callback(fn (array $values): bool => isset($values['campaign_hash'])
                    && 32 === strlen((string) $values['campaign_hash'])),
                'campaign_id = ?',
                [4],
            )
            ->willReturn(1);

        $campaign = new Entity(['id' => 4, 'hash' => '', 'user' => 7, 'name' => 'The Flood']);
        $result = $this->factory($pdo)->update($campaign);

        self::assertTrue($result->isSuccess());
        self::assertSame(32, strlen((string) $campaign->hash));
    }

    public function testInsertSetsCurrentUserHashAndOwnerMembership(): void
    {
        $this->mapSessionUser(7);

        $memberFactory = $this->getMockBuilder(Member::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['ensureMember'])
            ->getMock();
        $memberFactory->expects(self::once())
            ->method('ensureMember')
            ->with(
                self::callback(fn (Entity $entity): bool => '22' === $entity->id && 7 === $entity->user),
                7,
                Member::ROLE_OWNER,
            )
            ->willReturn(new Result());

        $pdo = $this->createMock(SimplePdo::class);
        $pdo->expects(self::once())
            ->method('insert')
            ->with(
                'campaigns',
                self::callback(function (array $values): bool {
                    return 7 === $values['campaign_user']
                        && 'The Flood' === $values['campaign_name']
                        && is_string($values['campaign_hash'])
                        && 32 === strlen($values['campaign_hash']);
                }),
            )
            ->willReturn('22');
        $pdo->expects(self::never())
            ->method('transaction');

        $campaign = new Entity(['name' => 'The Flood']);
        $result = $this->factory($pdo, $memberFactory)->insert($campaign);

        self::assertTrue($result->isSuccess());
        self::assertSame('22', $campaign->id);
        self::assertSame(7, $campaign->user);
        self::assertSame(32, strlen((string) $campaign->hash));
    }

    private function factory(?SimplePdo $pdo = null, ?Member $memberFactory = null): Campaign
    {
        return new Campaign(
            $pdo ?? $this->createStub(SimplePdo::class),
            new Validator(),
            $memberFactory ?? $this->createStub(Member::class),
        );
    }

    private function mapSessionUser(int $id): void
    {
        $session = new class ($id) {
            public object $user;

            public function __construct(int $id)
            {
                $this->user = (object) ['id' => $id];
            }
        };

        Flight::map('session', fn () => $session);
    }
}
