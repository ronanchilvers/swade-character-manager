<?php

declare(strict_types=1);

namespace Tests\Entity\Factory;

use App\Entity;
use App\Entity\Factory\Character as CharacterFactory;
use App\Entity\Validator;
use flight\database\SimplePdo;
use flight\util\Collection;
use PHPUnit\Framework\TestCase;

class CharacterTest extends TestCase
{
    public function testValidAttributeDicePassValidation(): void
    {
        $factory = new CharacterFactory(
            $this->createStub(SimplePdo::class),
            new Validator()
        );

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
        $factory = new CharacterFactory(
            $this->createStub(SimplePdo::class),
            new Validator()
        );

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
        $factory = new CharacterFactory(
            $this->createStub(SimplePdo::class),
            new Validator()
        );

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

        $factory = new CharacterFactory($pdo, new Validator());
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

        $factory = new CharacterFactory($pdo, new Validator());
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

        $factory = new CharacterFactory($pdo, new Validator());
        $result = $factory->delete(new Entity(['id' => 10]));

        self::assertFalse($result->isSuccess());
        self::assertSame(['delete failed'], $result->errors());
    }
}
