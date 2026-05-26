<?php

declare(strict_types=1);

namespace Tests\Service;

use App\Service\Sources;
use flight\database\SimplePdo;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class SourcesTest extends TestCase
{
    public function testOptionsCanUseDatabaseBackedRows(): void
    {
        $pdo = $this->createMock(SimplePdo::class);
        $pdo->expects(self::once())
            ->method('fetchAll')
            ->willReturn([
                $this->row('core', 'Core Rules', '1', 0),
                $this->row('fantasy', 'Fantasy Companion', '0', 10),
            ]);

        $sources = new Sources($pdo);

        self::assertSame(
            [
                'core' => [
                    'key' => 'core',
                    'name' => 'Core Rules',
                    'always_enabled' => true,
                    'position' => 0,
                ],
                'fantasy' => [
                    'key' => 'fantasy',
                    'name' => 'Fantasy Companion',
                    'always_enabled' => false,
                    'position' => 10,
                ],
            ],
            $sources->options(),
        );
        self::assertSame(['core' => 'Core Rules', 'fantasy' => 'Fantasy Companion'], $sources->all());
    }

    public function testFallbackOptionsAreUsedWhenDatabaseRowsAreUnavailable(): void
    {
        $pdo = $this->createMock(SimplePdo::class);
        $pdo->expects(self::once())
            ->method('fetchAll')
            ->willThrowException(new RuntimeException('sources table missing'));

        $sources = new Sources($pdo);

        self::assertArrayHasKey('core', $sources->options());
        self::assertArrayNotHasKey('fantasy', $sources->options());
        self::assertTrue($sources->options()['core']['always_enabled']);
    }

    public function testFilterKeepsAlwaysEnabledSourcesAndDropsUnknownSources(): void
    {
        $sources = new Sources();

        self::assertSame(['core'], $sources->filter([]));
        self::assertSame(['core'], $sources->filter(['fantasy', 'unknown']));
        self::assertSame(['core'], $sources->selectedFromString('fantasy,unknown'));
    }

    private function row(string $key, string $name, string $alwaysEnabled, int $position): array
    {
        return [
            'catalog_source_key' => $key,
            'catalog_source_name' => $name,
            'catalog_source_always_enabled' => $alwaysEnabled,
            'catalog_source_position' => $position,
        ];
    }
}
