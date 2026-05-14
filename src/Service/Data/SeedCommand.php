<?php

declare(strict_types=1);

namespace App\Service\Data;

use RuntimeException;

class SeedCommand
{
    private const TYPES = [
        'edges' => EdgeCatalogSeeder::class,
        'hindrances' => HindranceCatalogSeeder::class,
        'skills' => SkillCatalogSeeder::class,
    ];

    public function resolve(?string $type, ?string $source, string $baseDir): array
    {
        if (!is_string($type) || '' === trim($type) || !is_string($source) || '' === trim($source)) {
            throw new RuntimeException('Usage: php scripts/seed.php <type> <source>');
        }

        $type = trim($type);
        $source = trim($source);
        $this->assertSlug('Type', $type);
        $this->assertSlug('Source', $source);

        if (!isset(self::TYPES[$type])) {
            throw new RuntimeException(sprintf('Unsupported seed type: %s', $type));
        }

        return [
            'type' => $type,
            'source' => $source,
            'filename' => sprintf('%s/data/%s/%s.php', rtrim($baseDir, '/'), $source, $type),
            'seeder' => self::TYPES[$type],
        ];
    }

    private function assertSlug(string $label, string $value): void
    {
        if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $value)) {
            throw new RuntimeException("{$label} must use lowercase letters, numbers, and hyphens only.");
        }
    }
}
