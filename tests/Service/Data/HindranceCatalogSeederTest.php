<?php

declare(strict_types=1);

namespace Tests\Service\Data;

use App\Service\Data\HindranceCatalogSeeder;
use flight\database\SimplePdo;
use PDOStatement;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class HindranceCatalogSeederTest extends TestCase
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

        $count = new HindranceCatalogSeeder($pdo)->seedEntries([
            [
                'id' => 'all_thumbs',
                'name' => 'All Thumbs',
                'levels' => ['minor'],
                'summary' => 'The hero is bad with mechanical or electrical devices.',
                'requirements' => [],
                'effects' => [
                    [
                        'level' => 'minor',
                        'details' => 'Applies to Trait rolls made while using mechanical or electrical devices.',
                    ],
                ],
                'notes' => [],
                'source_pages' => [24],
            ],
        ]);

        self::assertSame(1, $count);
        self::assertStringContainsString('ON DUPLICATE KEY UPDATE', $capturedSql);
        self::assertSame('all_thumbs', $capturedParams[0]);
        self::assertSame('core', $capturedParams[1]);
        self::assertSame('All Thumbs', $capturedParams[2]);
        self::assertSame('["minor"]', $capturedParams[4]);
        self::assertSame('[24]', $capturedParams[8]);
    }

    public function testSeedEntriesRejectsDuplicateKeysBeforeWriting(): void
    {
        $pdo = $this->createMock(SimplePdo::class);
        $pdo->expects(self::never())->method('transaction');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Duplicate hindrance key in source: all_thumbs');

        new HindranceCatalogSeeder($pdo)->seedEntries([
            ['id' => 'all_thumbs', 'name' => 'All Thumbs'],
            ['id' => 'all_thumbs', 'name' => 'All Thumbs Again'],
        ]);
    }
}
