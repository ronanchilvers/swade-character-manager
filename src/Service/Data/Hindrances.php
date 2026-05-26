<?php

declare(strict_types=1);

namespace App\Service\Data;

use App\Service\Data;
use flight\database\SimplePdo;
use Throwable;

class Hindrances extends Data
{
    private const TABLE_NAME = 'hindrance_catalog';

    private ?array $databaseEntries = null;

    public function __construct(
        string $dataDir,
        private ?SimplePdo $pdo = null,
    ) {
        parent::__construct(rtrim($dataDir, '/') . '/core');
    }

    public function all(): array
    {
        $entries = $this->databaseEntries();
        if (is_array($entries)) {
            return $entries;
        }

        return parent::all();
    }

    public function forBuilder(array $sources = []): array
    {
        return array_map(
            fn (array $hindrance): array => $hindrance + [
                'effects_by_level' => $this->groupEffectsByLevel(
                    $hindrance['effects'] ?? []
                ),
            ],
            empty($sources) ? $this->all() : $this->forSources($sources),
        );
    }

    private function groupEffectsByLevel(array $effects): array
    {
        $grouped = [];

        foreach ($effects as $effect) {
            $level = $effect['level'] ?? null;
            $details = $effect['details'] ?? null;

            if (!is_string($level) || !is_string($details) || '' === $details) {
                continue;
            }

            $grouped[$level][] = $details;
        }

        return $grouped;
    }

    private function databaseEntries(): ?array
    {
        if (is_null($this->pdo)) {
            return null;
        }
        if (is_array($this->databaseEntries)) {
            return $this->databaseEntries;
        }

        try {
            $rows = $this->pdo->fetchAll(
                sprintf(
                    'SELECT * FROM %s ORDER BY hindrance_catalog_name ASC',
                    self::TABLE_NAME,
                ),
            );
        } catch (Throwable) {
            return null;
        }

        if (empty($rows)) {
            return null;
        }

        $this->databaseEntries = array_map(
            fn (mixed $row): array => $this->entryFromRow($row),
            $rows,
        );

        return $this->databaseEntries;
    }

    protected function entryFromRow(mixed $row): array
    {
        $effects = $this->decodeJson($row['hindrance_catalog_effects']);
        return [
            'id' => (string) $row['hindrance_catalog_key'],
            'source' => (string) $row['hindrance_catalog_source'],
            'name' => (string) $row['hindrance_catalog_name'],
            'levels' => $this->decodeJson($row['hindrance_catalog_levels']),
            'summary' => (string) $row['hindrance_catalog_summary'],
            'requirements' => $this->decodeJson($row['hindrance_catalog_requirements']),
            'effects' => $effects,
            'effects_by_level' => $this->groupEffectsByLevel(
                $effects ?? []
            ),
            'notes' => $this->decodeJson($row['hindrance_catalog_notes']),
            'source_pages' => $this->decodeJson($row['hindrance_catalog_source_pages']),
        ];
    }

    private function decodeJson(mixed $value): array
    {
        if (!is_string($value) || '' === $value) {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }
}
