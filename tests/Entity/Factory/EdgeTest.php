<?php

declare(strict_types=1);

namespace Tests\Entity\Factory;

use App\Entity;
use App\Entity\Factory\Edge;
use App\Entity\Validator;
use flight\database\SimplePdo;
use PHPUnit\Framework\TestCase;

class EdgeTest extends TestCase
{
    public function testSyncForCharacterStoresSubmittedEdgeCounts(): void
    {
        $pdo = $this->createMock(SimplePdo::class);
        $pdo->expects(self::once())
            ->method('delete')
            ->with('edges', 'edge_character_id = ?', [10]);

        $insertedRows = [];
        $pdo->expects(self::once())
            ->method('insert')
            ->with(
                'edges',
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

        $factory = new Edge($pdo, new Validator());
        $result = $factory->syncForCharacter(
            new Entity(['id' => 10]),
            [
                'alertness' => 1,
                'new_powers' => 3,
            ]
        );

        self::assertTrue($result->isSuccess());
        self::assertSame(
            [
                [
                    'edge_character_id' => 10,
                    'edge_key' => 'alertness',
                    'edge_count' => 1,
                ],
                [
                    'edge_character_id' => 10,
                    'edge_key' => 'new_powers',
                    'edge_count' => 3,
                ],
            ],
            $insertedRows
        );
    }

    public function testSyncForCharacterAllowsClearingAllEdges(): void
    {
        $pdo = $this->createMock(SimplePdo::class);
        $pdo->expects(self::once())
            ->method('delete')
            ->with('edges', 'edge_character_id = ?', [10]);
        $pdo->expects(self::never())->method('insert');
        $pdo->expects(self::once())
            ->method('transaction')
            ->willReturnCallback(function (callable $callback) use ($pdo): void {
                $callback($pdo);
            });

        $factory = new Edge($pdo, new Validator());
        $result = $factory->syncForCharacter(
            new Entity(['id' => 10]),
            []
        );

        self::assertTrue($result->isSuccess());
        self::assertSame([], $result->errors());
    }
}
