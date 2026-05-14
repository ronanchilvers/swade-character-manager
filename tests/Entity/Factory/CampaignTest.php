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
