<?php

declare(strict_types=1);

namespace App\Service\Data;

use App\Service\Data;
use flight\database\SimplePdo;
use Throwable;

class Edges extends Data
{
    private const TABLE_NAME = 'edge_catalog';

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
                    'SELECT * FROM %s ORDER BY edge_catalog_category ASC',
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

    private function entryFromRow(mixed $row): array
    {
        return [
            'id' => (string) $row['edge_catalog_key'],
            'source' => (string) $row['edge_catalog_source'],
            'name' => (string) $row['edge_catalog_name'],
            'category' => (string) $row['edge_catalog_category'],
            'summary' => (string) $row['edge_catalog_summary'],
            'repeatable' => (bool) (int) $row['edge_catalog_repeatable'],
            'requirements' => $this->decodeJson($row['edge_catalog_requirements']),
            'effects' => $this->decodeJson($row['edge_catalog_effects']),
            'notes' => $this->decodeJson($row['edge_catalog_notes']),
            'source_pages' => $this->decodeJson($row['edge_catalog_source_pages']),
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
