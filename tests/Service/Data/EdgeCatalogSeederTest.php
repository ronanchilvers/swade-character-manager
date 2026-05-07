<?php

declare(strict_types=1);

namespace Tests\Service\Data;

use App\Service\Data\EdgeCatalogSeeder;
use flight\database\SimplePdo;
use PDOStatement;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class EdgeCatalogSeederTest extends TestCase
{
    public function testSeedEntriesUpsertsRowsWithoutDeletingExistingData(): void
    {
        $pdo = $this->createMock(SimplePdo::class);
        $statement = $this->createStub(PDOStatement::class);

        $capturedSql = null;
        $capturedParams = [];
        $pdo->expects(self::never())->method('delete');
        $pdo->expects(self::once())
            ->method('runQuery')
            ->willReturnCallback(function (string $sql, array $params) use (&$capturedSql, &$capturedParams, $statement): PDOStatement {
                $capturedSql = $sql;
                $capturedParams = $params;

                return $statement;
            });
        $pdo->expects(self::once())
            ->method('transaction')
            ->willReturnCallback(function (callable $callback) use ($pdo): void {
                $callback($pdo);
            });

        $count = new EdgeCatalogSeeder($pdo)->seedEntries([
            [
                'id' => 'alertness',
                'name' => 'Alertness',
                'category' => 'background',
                'summary' => 'The hero is exceptionally observant.',
                'repeatable' => false,
                'requirements' => [
                    [
                        'type' => 'rank',
                        'target' => 'rank',
                        'value' => 'Novice',
                    ],
                ],
                'effects' => [
                    [
                        'level' => 'base',
                        'details' => '+2 to Notice rolls.',
                    ],
                ],
                'notes' => [],
                'source_pages' => [61],
            ],
        ]);

        self::assertSame(1, $count);
        self::assertStringContainsString('ON DUPLICATE KEY UPDATE', $capturedSql);
        self::assertSame('alertness', $capturedParams[0]);
        self::assertSame('core', $capturedParams[1]);
        self::assertSame('Alertness', $capturedParams[2]);
        self::assertSame('background', $capturedParams[3]);
        self::assertSame(0, $capturedParams[4]);
        self::assertSame('The hero is exceptionally observant.', $capturedParams[5]);
        self::assertSame('[{"type":"rank","target":"rank","value":"Novice"}]', $capturedParams[6]);
        self::assertSame('[61]', $capturedParams[9]);
    }

    public function testSeedEntriesRejectsDuplicateKeysBeforeWriting(): void
    {
        $pdo = $this->createMock(SimplePdo::class);
        $pdo->expects(self::never())->method('transaction');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Duplicate edge key in source: alertness');

        new EdgeCatalogSeeder($pdo)->seedEntries([
            [
                'id' => 'alertness',
                'name' => 'Alertness',
                'category' => 'background',
                'repeatable' => false,
            ],
            [
                'id' => 'alertness',
                'name' => 'Alertness Again',
                'category' => 'background',
                'repeatable' => false,
            ],
        ]);
    }
}
