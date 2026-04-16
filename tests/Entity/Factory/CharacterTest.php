<?php

declare(strict_types=1);

namespace Tests\Entity\Factory;

use App\Entity;
use App\Entity\Factory\Character as CharacterFactory;
use App\Entity\Validator;
use flight\database\SimplePdo;
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
}
