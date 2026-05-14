<?php

declare(strict_types=1);

namespace Tests\Service\Data;

use App\Service\Data\EdgeCatalogSeeder;
use App\Service\Data\SeedCommand;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class SeedCommandTest extends TestCase
{
    public function testResolveRejectsMissingArguments(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Usage: php scripts/seed.php <type> <source>');

        (new SeedCommand())->resolve(null, 'core', '/app');
    }

    public function testResolveRejectsUnsupportedTypes(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unsupported seed type: powers');

        (new SeedCommand())->resolve('powers', 'core', '/app');
    }

    public function testResolveRejectsInvalidTypeOrSourceFormat(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Source must use lowercase letters, numbers, and hyphens only.');

        (new SeedCommand())->resolve('edges', 'Core_Set', '/app');
    }

    public function testResolveReturnsCatalogFilenameAndSeederClass(): void
    {
        self::assertSame(
            [
                'type' => 'edges',
                'source' => 'core',
                'filename' => '/app/data/core/edges.php',
                'seeder' => EdgeCatalogSeeder::class,
            ],
            (new SeedCommand())->resolve(' edges ', ' core ', '/app/'),
        );
    }
}
