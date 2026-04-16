<?php

declare(strict_types=1);

namespace Tests\Entity\Factory;

use App\Entity;
use App\Entity\Factory\Skill;
use App\Entity\Validator;
use App\Service\Data\Skills;
use flight\database\SimplePdo;
use PHPUnit\Framework\TestCase;

class SkillTest extends TestCase
{
    public function testSyncForCharacterStoresAttributeAndCoreMetadataFromCatalog(): void
    {
        $pdo = $this->createMock(SimplePdo::class);
        $pdo->expects(self::once())
            ->method('delete')
            ->with('skills', 'skill_character_id = ?', [10]);

        $insertedRows = [];
        $pdo->expects(self::once())
            ->method('insert')
            ->with(
                'skills',
                self::callback(function (array $rows) use (&$insertedRows): bool {
                    $insertedRows = $rows;

                    return true;
                })
            )
            ->willReturn('2');

        $pdo->expects(self::once())
            ->method('transaction')
            ->willReturnCallback(function (callable $callback) use ($pdo): void {
                $callback($pdo);
            });

        $factory = new Skill($pdo, new Validator());
        $result = $factory->syncForCharacter(
            new Entity(['id' => 10]),
            [
                'athletics' => 4,
                'fighting' => 8,
            ],
            new Skills(__DIR__ . '/../../../data')
        );

        self::assertTrue($result->isSuccess());
        self::assertSame(
            [
                [
                    'skill_character_id' => 10,
                    'skill_key' => 'athletics',
                    'skill_die' => 4,
                    'skill_attribute' => 'agility',
                    'skill_core' => 'yes',
                ],
                [
                    'skill_character_id' => 10,
                    'skill_key' => 'fighting',
                    'skill_die' => 8,
                    'skill_attribute' => 'agility',
                    'skill_core' => 'no',
                ],
            ],
            $insertedRows
        );
    }
}
