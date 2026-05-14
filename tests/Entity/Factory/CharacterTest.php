<?php

declare(strict_types=1);

namespace Tests\Entity\Factory;

use App\Entity;
use App\Entity\Factory\Character as CharacterFactory;
use App\Entity\Factory\Result;
use App\Entity\Factory\Skill;
use App\Entity\Validator;
use App\Service\Data\Manager;
use App\Service\Data\Skills;
use Flight;
use flight\database\SimplePdo;
use flight\util\Collection;
use PHPUnit\Framework\TestCase;

class CharacterTest extends TestCase
{
    private const DATA_DIR = __DIR__ . '/../../../data';

    public function testValidAttributeDicePassValidation(): void
    {
        $factory = $this->factory();

        $entity = new Entity([
            'hash' => str_repeat('a', 32),
            'user' => 1,
            'name' => 'Mara',
            'agility' => 6,
            'smarts' => 8,
            'spirit' => 4,
            'strength' => 10,
            'vigor' => 12,
        ]);

        self::assertSame([], $factory->validate($entity));
    }

    public function testInvalidAttributeDiceAreRejected(): void
    {
        $factory = $this->factory();

        $entity = new Entity([
            'hash' => str_repeat('a', 32),
            'user' => 1,
            'name' => 'Mara',
            'agility' => 5,
            'smarts' => 8,
            'spirit' => 4,
            'strength' => 10,
            'vigor' => 12,
        ]);

        self::assertContains('agility', $factory->validate($entity));
    }

    public function testConceptOnlyEntityStillValidatesWithoutAttributes(): void
    {
        $factory = $this->factory();

        $entity = new Entity([
            'name' => 'Mara',
        ]);

        self::assertSame([], $factory->validate($entity));
    }

    public function testForUserHashFindsByUserAndHash(): void
    {
        $pdo = $this->createMock(SimplePdo::class);
        $pdo->expects(self::once())
            ->method('fetchRow')
            ->with(
                'SELECT * FROM characters WHERE character_user = ? AND character_hash = ?',
                [7, str_repeat('b', 32)]
            )
            ->willReturn(new Collection([
                'character_id' => 10,
                'character_user' => 7,
                'character_hash' => str_repeat('b', 32),
                'character_name' => 'Mara',
            ]));

        $factory = $this->factory($pdo);
        $entity = $factory->forUserHash(7, str_repeat('b', 32));

        self::assertInstanceOf(Entity::class, $entity);
        self::assertSame(10, $entity->id);
        self::assertSame(7, $entity->user);
        self::assertSame(str_repeat('b', 32), $entity->hash);
    }

    public function testDeleteRemovesCharacterById(): void
    {
        $pdo = $this->createMock(SimplePdo::class);
        $pdo->expects(self::once())
            ->method('delete')
            ->with('characters', 'character_id = ?', [10])
            ->willReturn(1);

        $factory = $this->factory($pdo);
        $result = $factory->delete(new Entity(['id' => 10]));

        self::assertTrue($result->isSuccess());
        self::assertSame([], $result->errors());
    }

    public function testDeleteReturnsErrorWhenDatabaseDeleteThrows(): void
    {
        $pdo = $this->createMock(SimplePdo::class);
        $pdo->expects(self::once())
            ->method('delete')
            ->willThrowException(new \RuntimeException('delete failed'));

        $factory = $this->factory($pdo);
        $result = $factory->delete(new Entity(['id' => 10]));

        self::assertFalse($result->isSuccess());
        self::assertSame(['delete failed'], $result->errors());
    }

    public function testCampaignValidationAcceptsNullOrPositiveInteger(): void
    {
        $entity = new Entity([
            'hash' => str_repeat('a', 32),
            'user' => 1,
            'name' => 'Mara',
            'campaign' => null,
        ]);

        self::assertSame([], $this->factory()->validate($entity));

        $entity->campaign = 4;
        self::assertSame([], $this->factory()->validate($entity));

        $entity->campaign = 0;
        self::assertContains('campaign', $this->factory()->validate($entity));
    }

    public function testJoinCampaignRejectsDifferentExistingCampaign(): void
    {
        $result = $this->factory()->joinCampaign(
            new Entity(['id' => 4]),
            new Entity(['id' => 10, 'campaign' => 9]),
        );

        self::assertFalse($result->isSuccess());
        self::assertSame(['Character already belongs to another campaign'], $result->errors());
    }

    public function testJoinCampaignIsIdempotentForSameCampaign(): void
    {
        $pdo = $this->createMock(SimplePdo::class);
        $pdo->expects(self::never())
            ->method('update');

        $result = $this->factory($pdo)->joinCampaign(
            new Entity(['id' => 4]),
            new Entity(['id' => 10, 'campaign' => 4]),
        );

        self::assertTrue($result->isSuccess());
    }

    public function testJoinCampaignAssignsCampaignColumn(): void
    {
        $pdo = $this->createMock(SimplePdo::class);
        $pdo->expects(self::once())
            ->method('update')
            ->with('characters', ['character_campaign' => 4], 'character_id = ?', [10])
            ->willReturn(1);

        $character = new Entity(['id' => 10]);
        $result = $this->factory($pdo)->joinCampaign(new Entity(['id' => 4]), $character);

        self::assertTrue($result->isSuccess());
        self::assertSame(4, $character->campaign);
    }

    public function testLeaveCampaignClearsCampaignColumn(): void
    {
        $pdo = $this->createMock(SimplePdo::class);
        $pdo->expects(self::once())
            ->method('update')
            ->with('characters', ['character_campaign' => null], 'character_id = ?', [10])
            ->willReturn(1);

        $character = new Entity(['id' => 10, 'campaign' => 4]);
        $result = $this->factory($pdo)->leaveCampaign($character);

        self::assertTrue($result->isSuccess());
        self::assertNull($character->campaign);
    }

    public function testDeleteIsBlockedWhenCharacterBelongsToCampaign(): void
    {
        $pdo = $this->createMock(SimplePdo::class);
        $pdo->expects(self::never())
            ->method('delete');

        $result = $this->factory($pdo)->delete(new Entity(['id' => 10, 'campaign' => 4]));

        self::assertFalse($result->isSuccess());
        self::assertSame(['Character must leave the campaign before deletion'], $result->errors());
    }

    public function testInsertCreatesCoreSkillsAfterCharacterIdIsAssigned(): void
    {
        $this->mapSessionUser(7);
        $skillService = new Skills(self::DATA_DIR);

        $manager = $this->createMock(Manager::class);
        $manager->expects(self::once())
            ->method('getType')
            ->with(Skills::class)
            ->willReturn($skillService);

        $skillFactory = $this->createMock(Skill::class);
        $skillFactory->expects(self::once())
            ->method('insertCoreForCharacter')
            ->with(
                self::callback(function (Entity $entity): bool {
                    return '10' === $entity->id;
                }),
                $skillService,
            )
            ->willReturn(new Result());

        $pdo = $this->createMock(SimplePdo::class);
        $pdo->expects(self::once())
            ->method('insert')
            ->with(
                'characters',
                self::callback(function (array $values): bool {
                    return 7 === $values['character_user']
                        && 'Mara' === $values['character_name']
                        && is_string($values['character_hash'])
                        && 32 === strlen($values['character_hash']);
                })
            )
            ->willReturn('10');
        $pdo->expects(self::never())
            ->method('transaction');

        $entity = new Entity(['name' => 'Mara']);
        $result = $this->factory($pdo, $skillFactory, $manager)->insert($entity);

        self::assertTrue($result->isSuccess());
        self::assertSame('10', $entity->id);
    }

    public function testInsertReturnsErrorWhenCoreSkillCreationFails(): void
    {
        $this->mapSessionUser(7);

        $manager = $this->createMock(Manager::class);
        $manager->expects(self::once())
            ->method('getType')
            ->with(Skills::class)
            ->willReturn(new Skills(self::DATA_DIR));

        $skillFactory = $this->createStub(Skill::class);
        $skillFactory->method('insertCoreForCharacter')
            ->willReturn(new Result(['skill seed failed']));

        $pdo = $this->createMock(SimplePdo::class);
        $pdo->method('insert')
            ->willReturn('10');
        $pdo->expects(self::never())
            ->method('transaction');

        $result = $this->factory($pdo, $skillFactory, $manager)->insert(
            new Entity(['name' => 'Mara'])
        );

        self::assertFalse($result->isSuccess());
        self::assertSame(['skill seed failed'], $result->errors());
    }

    private function factory(
        ?SimplePdo $pdo = null,
        ?Skill $skillFactory = null,
        ?Manager $manager = null,
    ): CharacterFactory {
        return new CharacterFactory(
            $pdo ?? $this->createStub(SimplePdo::class),
            new Validator(),
            $skillFactory ?? $this->createStub(Skill::class),
            $manager ?? $this->createStub(Manager::class),
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

        Flight::map('session', function () use ($session) {
            return $session;
        });
    }
}
