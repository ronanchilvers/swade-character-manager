<?php

declare(strict_types=1);

namespace App\Service;

use flight\database\SimplePdo;
use Throwable;

class Sources
{
    private const TABLE_NAME = 'catalog_sources';

    private const FALLBACK_SOURCES = [
        [
            'key' => 'core',
            'name' => 'Core Rules (Always Enabled)',
            'always_enabled' => true,
            'position' => 0,
        ],
    ];

    private ?array $sources = null;

    public function __construct(
        private ?SimplePdo $pdo = null,
    ) {
    }

    public function all(): array
    {
        $sources = [];
        foreach ($this->options() as $key => $source) {
            $sources[$key] = $source['name'];
        }

        return $sources;
    }

    public function options(): array
    {
        if (is_array($this->sources)) {
            return $this->sources;
        }

        $sources = $this->databaseOptions();
        if (!is_array($sources)) {
            $sources = $this->fallbackOptions();
        }

        $this->sources = $sources;

        return $this->sources;
    }

    public function selectedFromString(mixed $stored): array
    {
        if (!is_string($stored) || '' === trim($stored)) {
            return $this->filter([]);
        }

        return $this->filter(explode(',', $stored));
    }

    public function filter(array $input): array
    {
        $requested = [];
        foreach ($input as $key) {
            $key = trim((string) $key);
            if ('' !== $key) {
                $requested[$key] = true;
            }
        }

        $filtered = [];
        foreach ($this->options() as $key => $source) {
            if ($source['always_enabled'] || isset($requested[$key])) {
                $filtered[] = $key;
            }
        }

        return $filtered;
    }

    private function databaseOptions(): ?array
    {
        if (is_null($this->pdo)) {
            return null;
        }

        try {
            $rows = $this->pdo->fetchAll(
                sprintf(
                    'SELECT * FROM %s ORDER BY catalog_source_position ASC, catalog_source_name ASC',
                    self::TABLE_NAME,
                ),
            );
        } catch (Throwable) {
            return null;
        }

        if (empty($rows)) {
            return null;
        }

        $sources = [];
        foreach ($rows as $row) {
            $key = (string) $row['catalog_source_key'];
            $sources[$key] = [
                'key' => $key,
                'name' => (string) $row['catalog_source_name'],
                'always_enabled' => (bool) (int) $row['catalog_source_always_enabled'],
                'position' => (int) $row['catalog_source_position'],
            ];
        }

        return $sources;
    }

    private function fallbackOptions(): array
    {
        $sources = [];
        foreach (self::FALLBACK_SOURCES as $source) {
            $sources[$source['key']] = $source;
        }

        return $sources;
    }
}
