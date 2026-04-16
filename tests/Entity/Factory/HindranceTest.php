<?php

declare(strict_types=1);

namespace Tests\Entity\Factory;

use App\Entity;
use App\Entity\Factory\Hindrance;
use App\Entity\Validator;
use flight\database\SimplePdo;
use PHPUnit\Framework\TestCase;

class HindranceTest extends TestCase
{
    public function testSyncForCharacterReplacesSelectionsWithSubmittedLevels(): void
    {
        $pdo = $this->createMock(SimplePdo::class);
        $pdo->expects(self::once())
            ->method('delete')
            ->with('hindrances', 'hindrance_character_id = ?', [10]);

        $insertedRows = [];
        $pdo->expects(self::once())
            ->method('insert')
            ->with(
                'hindrances',
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

        $factory = new Hindrance($pdo, new Validator());
        $result = $factory->syncForCharacter(
            new Entity(['id' => 10]),
            [
                'all_thumbs' => 'minor',
                'bad_eyes' => 'major',
            ]
        );

        self::assertTrue($result->isSuccess());
        self::assertSame(
            [
                [
                    'hindrance_character_id' => 10,
                    'hindrance_key' => 'all_thumbs',
                    'hindrance_level' => 'minor',
                ],
                [
                    'hindrance_character_id' => 10,
                    'hindrance_key' => 'bad_eyes',
                    'hindrance_level' => 'major',
                ],
            ],
            $insertedRows
        );
    }

    public function testSyncForCharacterAllowsClearingAllSelections(): void
    {
        $pdo = $this->createMock(SimplePdo::class);
        $pdo->expects(self::once())
            ->method('delete')
            ->with('hindrances', 'hindrance_character_id = ?', [10]);
        $pdo->expects(self::never())->method('insert');
        $pdo->expects(self::once())
            ->method('transaction')
            ->willReturnCallback(function (callable $callback) use ($pdo): void {
                $callback($pdo);
            });

        $factory = new Hindrance($pdo, new Validator());
        $result = $factory->syncForCharacter(
            new Entity(['id' => 10]),
            []
        );

        self::assertTrue($result->isSuccess());
        self::assertSame([], $result->errors());
    }
}
