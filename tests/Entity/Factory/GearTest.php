<?php

declare(strict_types=1);

namespace Tests\Entity\Factory;

use App\Entity;
use App\Entity\Factory\Gear;
use App\Entity\Validator;
use flight\database\SimplePdo;
use flight\util\Collection;
use PHPUnit\Framework\TestCase;

class GearTest extends TestCase
{
    public function testForCharacterSortsRowsByPosition(): void
    {
        $pdo = $this->createMock(SimplePdo::class);
        $pdo->expects(self::once())
            ->method('fetchAll')
            ->with('SELECT * FROM gear WHERE gear_character_id = ?', [10])
            ->willReturn([
                new Collection(['gear_id' => 2, 'gear_character_id' => 10, 'gear_position' => 2, 'gear_name' => 'Lantern']),
                new Collection(['gear_id' => 1, 'gear_character_id' => 10, 'gear_position' => 1, 'gear_name' => 'Rope']),
            ]);

        $rows = (new Gear($pdo, new Validator()))->forCharacter(new Entity(['id' => 10]));

        self::assertSame(['Rope', 'Lantern'], array_map(fn (Entity $row): string => $row->name, $rows));
    }

    public function testSyncForCharacterPreservesOrderAndAssignsPositions(): void
    {
        $pdo = $this->createMock(SimplePdo::class);
        $pdo->expects(self::once())
            ->method('delete')
            ->with('gear', 'gear_character_id = ?', [10]);

        $insertedRows = [];
        $pdo->expects(self::once())
            ->method('insert')
            ->with(
                'gear',
                self::callback(function (array $rows) use (&$insertedRows): bool {
                    $insertedRows = $rows;

                    return true;
                })
            )
            ->willReturn('1');

        $pdo->expects(self::once())
            ->method('transaction')
            ->willReturnCallback(function (callable $callback) use ($pdo): void {
                $callback($pdo);
            });

        $factory = new Gear($pdo, new Validator());
        $result = $factory->syncForCharacter(
            new Entity(['id' => 10]),
            [
                ['name' => 'Rope', 'notes' => '50ft'],
                ['name' => 'Lantern'],
            ]
        );

        self::assertTrue($result->isSuccess());
        self::assertSame(
            [
                [
                    'gear_character_id' => 10,
                    'gear_position'     => 0,
                    'gear_name'         => 'Rope',
                    'gear_notes'        => '50ft',
                ],
                [
                    'gear_character_id' => 10,
                    'gear_position'     => 1,
                    'gear_name'         => 'Lantern',
                    'gear_notes'        => null,
                ],
            ],
            $insertedRows
        );
    }

    public function testSyncForCharacterDropsBlankNameRowsAndSkipsInsertWhenEmpty(): void
    {
        $pdo = $this->createMock(SimplePdo::class);
        $pdo->expects(self::once())
            ->method('delete')
            ->with('gear', 'gear_character_id = ?', [10]);
        $pdo->expects(self::never())->method('insert');
        $pdo->expects(self::once())
            ->method('transaction')
            ->willReturnCallback(function (callable $callback) use ($pdo): void {
                $callback($pdo);
            });

        $factory = new Gear($pdo, new Validator());
        $result = $factory->syncForCharacter(
            new Entity(['id' => 10]),
            [
                ['name' => '   '],
                ['name' => '', 'notes' => 'ignored'],
            ]
        );

        self::assertTrue($result->isSuccess());
        self::assertSame([], $result->errors());
    }
}
