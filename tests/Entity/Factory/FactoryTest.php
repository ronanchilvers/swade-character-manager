<?php

declare(strict_types=1);

namespace Tests\Entity\Factory;

use App\Entity;
use App\Entity\Factory;
use App\Entity\Validator;
use flight\database\SimplePdo;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    public function testInsertRunsAfterInsertWithAssignedId(): void
    {
        $pdo = $this->createMock(SimplePdo::class);
        $pdo->expects(self::once())
            ->method('insert')
            ->with('test_entities', ['test_entity_name' => 'Mara'])
            ->willReturn('123');
        $pdo->expects(self::never())
            ->method('transaction');

        $factory = $this->factory($pdo);
        $entity = new Entity(['name' => 'Mara']);
        $result = $factory->insert($entity);

        self::assertTrue($result->isSuccess());
        self::assertSame('123', $entity->id);
        self::assertSame('123', $factory->afterInsertId);
    }

    public function testInsertReturnsErrorsFromAfterInsertFailures(): void
    {
        $pdo = $this->createMock(SimplePdo::class);
        $pdo->method('insert')
            ->willReturn('123');
        $pdo->expects(self::never())
            ->method('transaction');

        $factory = $this->factory($pdo, true);
        $result = $factory->insert(new Entity(['name' => 'Mara']));

        self::assertFalse($result->isSuccess());
        self::assertSame(['after insert failed'], $result->errors());
    }

    private function factory(SimplePdo $pdo, bool $failAfterInsert = false): Factory
    {
        return new class ($pdo, new Validator(), $failAfterInsert) extends Factory {
            public mixed $afterInsertId = null;

            public function __construct(
                SimplePdo $pdo,
                Validator $validator,
                private bool $failAfterInsert,
            ) {
                parent::__construct($pdo, $validator);
            }

            protected function getValidationRules(): array
            {
                return [];
            }

            protected function getTableName(): string
            {
                return 'test_entities';
            }

            protected function getPrefix(): string
            {
                return 'test_entity_';
            }

            protected function afterInsert(Entity $entity): void
            {
                $this->afterInsertId = $entity->id;

                if ($this->failAfterInsert) {
                    throw new \RuntimeException('after insert failed');
                }
            }
        };
    }
}
