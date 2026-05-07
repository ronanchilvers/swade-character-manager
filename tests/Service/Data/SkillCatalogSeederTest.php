<?php

declare(strict_types=1);

namespace Tests\Service\Data;

use App\Service\Data\SkillCatalogSeeder;
use flight\database\SimplePdo;
use PDOStatement;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class SkillCatalogSeederTest extends TestCase
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

        $count = new SkillCatalogSeeder($pdo)->seedEntries([
            [
                'id' => 'faith',
                'name' => 'Faith',
                'linked_attribute' => 'spirit',
                'core_skill' => false,
                'arcane_background' => 'Miracles',
                'summary' => 'Arcane skill used by Arcane Background (Miracles).',
                'requirements' => [],
                'effects' => [],
                'notes' => [],
                'source_pages' => [60],
            ],
        ]);

        self::assertSame(1, $count);
        self::assertStringContainsString('ON DUPLICATE KEY UPDATE', $capturedSql);
        self::assertSame('faith', $capturedParams[0]);
        self::assertSame('core', $capturedParams[1]);
        self::assertSame('Faith', $capturedParams[2]);
        self::assertSame('spirit', $capturedParams[3]);
        self::assertSame(0, $capturedParams[4]);
        self::assertSame('Miracles', $capturedParams[5]);
        self::assertSame('[60]', $capturedParams[10]);
    }

    public function testSeedEntriesRejectsDuplicateKeysBeforeWriting(): void
    {
        $pdo = $this->createMock(SimplePdo::class);
        $pdo->expects(self::never())->method('transaction');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Duplicate skill key in source: faith');

        new SkillCatalogSeeder($pdo)->seedEntries([
            [
                'id' => 'faith',
                'name' => 'Faith',
                'linked_attribute' => 'spirit',
                'core_skill' => false,
            ],
            [
                'id' => 'faith',
                'name' => 'Faith Again',
                'linked_attribute' => 'spirit',
                'core_skill' => false,
            ],
        ]);
    }
}
