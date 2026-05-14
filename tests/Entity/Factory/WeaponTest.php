<?php

declare(strict_types=1);

namespace Tests\Entity\Factory;

use App\Entity;
use App\Entity\Factory\Weapon;
use App\Entity\Validator;
use flight\database\SimplePdo;
use flight\util\Collection;
use PHPUnit\Framework\TestCase;

class WeaponTest extends TestCase
{
    public function testForCharacterSortsRowsByPosition(): void
    {
        $pdo = $this->createMock(SimplePdo::class);
        $pdo->expects(self::once())
            ->method('fetchAll')
            ->with('SELECT * FROM weapons WHERE weapon_character_id = ?', [10])
            ->willReturn([
                new Collection(['weapon_id' => 2, 'weapon_character_id' => 10, 'weapon_position' => 2, 'weapon_name' => 'Knife']),
                new Collection(['weapon_id' => 1, 'weapon_character_id' => 10, 'weapon_position' => 1, 'weapon_name' => 'Winchester']),
            ]);

        $rows = (new Weapon($pdo, new Validator()))->forCharacter(new Entity(['id' => 10]));

        self::assertSame(['Winchester', 'Knife'], array_map(fn (Entity $row): string => $row->name, $rows));
    }

    public function testSyncForCharacterWritesAllStringFieldsAndAssignsPositions(): void
    {
        $pdo = $this->createMock(SimplePdo::class);
        $pdo->expects(self::once())
            ->method('delete')
            ->with('weapons', 'weapon_character_id = ?', [10]);

        $insertedRows = [];
        $pdo->expects(self::once())
            ->method('insert')
            ->with(
                'weapons',
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

        $factory = new Weapon($pdo, new Validator());
        $result = $factory->syncForCharacter(
            new Entity(['id' => 10]),
            [
                [
                    'name'   => 'Winchester',
                    'range'  => '24/48/96',
                    'damage' => '2d8',
                    'ap'     => '2',
                    'rof'    => '1',
                    'weight' => '8',
                    'notes'  => 'Lever-action',
                ],
                [
                    'name' => 'Knife',
                ],
            ]
        );

        self::assertTrue($result->isSuccess());
        self::assertSame(
            [
                [
                    'weapon_character_id' => 10,
                    'weapon_position'     => 0,
                    'weapon_name'         => 'Winchester',
                    'weapon_range'        => '24/48/96',
                    'weapon_damage'       => '2d8',
                    'weapon_ap'           => '2',
                    'weapon_rof'          => '1',
                    'weapon_weight'       => '8',
                    'weapon_notes'        => 'Lever-action',
                ],
                [
                    'weapon_character_id' => 10,
                    'weapon_position'     => 1,
                    'weapon_name'         => 'Knife',
                    'weapon_range'        => '',
                    'weapon_damage'       => '',
                    'weapon_ap'           => '',
                    'weapon_rof'          => '',
                    'weapon_weight'       => '',
                    'weapon_notes'        => null,
                ],
            ],
            $insertedRows
        );
    }

    public function testSyncForCharacterDropsBlankNameRows(): void
    {
        $pdo = $this->createMock(SimplePdo::class);
        $pdo->expects(self::once())
            ->method('delete')
            ->with('weapons', 'weapon_character_id = ?', [10]);
        $pdo->expects(self::never())->method('insert');
        $pdo->expects(self::once())
            ->method('transaction')
            ->willReturnCallback(function (callable $callback) use ($pdo): void {
                $callback($pdo);
            });

        $factory = new Weapon($pdo, new Validator());
        $result = $factory->syncForCharacter(
            new Entity(['id' => 10]),
            [
                ['name' => '', 'range' => '12', 'damage' => '2d6'],
            ]
        );

        self::assertTrue($result->isSuccess());
        self::assertSame([], $result->errors());
    }
}
