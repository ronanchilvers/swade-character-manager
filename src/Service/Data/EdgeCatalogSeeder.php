<?php

declare(strict_types=1);

namespace App\Service\Data;

use flight\database\SimplePdo;
use JsonException;
use RuntimeException;

class EdgeCatalogSeeder
{
    private const TABLE_NAME = 'edge_catalog';

    public function __construct(
        private SimplePdo $pdo,
    ) {
    }

    public function seedFile(string $filename, string $source = 'core'): int
    {
        if (!is_file($filename)) {
            throw new RuntimeException(sprintf('Unable to find edge source file: %s', $filename));
        }

        $data = require $filename;
        if (!is_array($data) || !isset($data['entries']) || !is_array($data['entries'])) {
            throw new RuntimeException('Edge source file must return an array with an entries array');
        }

        return $this->seedEntries($data['entries'], $source);
    }

    public function seedEntries(array $entries, string $source = 'core'): int
    {
        $source = trim($source);
        if ('' === $source) {
            throw new RuntimeException('Edge source cannot be blank');
        }

        $seen = [];
        $rows = [];
        foreach ($entries as $entry) {
            $row = $this->rowFromEntry($entry, $source);
            $key = $row['edge_catalog_key'];
            if (isset($seen[$key])) {
                throw new RuntimeException(sprintf('Duplicate edge key in source: %s', $key));
            }
            $seen[$key] = true;
            $rows[] = $row;
        }

        $this->pdo->transaction(function (SimplePdo $pdo) use ($rows): void {
            foreach ($rows as $row) {
                $pdo->runQuery($this->upsertSql(), array_values($row));
            }
        });

        return count($rows);
    }

    private function rowFromEntry(mixed $entry, string $source): array
    {
        if (!is_array($entry)) {
            throw new RuntimeException('Each edge entry must be an array');
        }

        $key = $this->requiredString($entry, 'id');
        $name = $this->requiredString($entry, 'name');
        $category = $this->requiredString($entry, 'category');
        $repeatable = $this->requiredBool($entry, 'repeatable');

        return [
            'edge_catalog_key' => $key,
            'edge_catalog_source' => $source,
            'edge_catalog_name' => $name,
            'edge_catalog_category' => $category,
            'edge_catalog_repeatable' => $repeatable ? 1 : 0,
            'edge_catalog_summary' => (string) ($entry['summary'] ?? ''),
            'edge_catalog_requirements' => $this->encodeJson($entry['requirements'] ?? []),
            'edge_catalog_effects' => $this->encodeJson($entry['effects'] ?? []),
            'edge_catalog_notes' => $this->encodeJson($entry['notes'] ?? []),
            'edge_catalog_source_pages' => $this->encodeJson($entry['source_pages'] ?? []),
        ];
    }

    private function requiredString(array $entry, string $field): string
    {
        $value = $entry[$field] ?? null;
        if (!is_string($value) || '' === trim($value)) {
            throw new RuntimeException(sprintf('Edge entry must include a non-blank %s', $field));
        }

        return $value;
    }

    private function requiredBool(array $entry, string $field): bool
    {
        $value = $entry[$field] ?? null;
        if (!is_bool($value)) {
            throw new RuntimeException(sprintf('Edge entry must include a boolean %s', $field));
        }

        return $value;
    }

    private function encodeJson(mixed $value): string
    {
        try {
            return json_encode($value, JSON_THROW_ON_ERROR);
        } catch (JsonException $ex) {
            throw new RuntimeException('Unable to encode edge catalog data as JSON', 0, $ex);
        }
    }

    private function upsertSql(): string
    {
        return sprintf(
            "INSERT INTO %s (
                edge_catalog_key,
                edge_catalog_source,
                edge_catalog_name,
                edge_catalog_category,
                edge_catalog_repeatable,
                edge_catalog_summary,
                edge_catalog_requirements,
                edge_catalog_effects,
                edge_catalog_notes,
                edge_catalog_source_pages
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                edge_catalog_source = VALUES(edge_catalog_source),
                edge_catalog_name = VALUES(edge_catalog_name),
                edge_catalog_category = VALUES(edge_catalog_category),
                edge_catalog_repeatable = VALUES(edge_catalog_repeatable),
                edge_catalog_summary = VALUES(edge_catalog_summary),
                edge_catalog_requirements = VALUES(edge_catalog_requirements),
                edge_catalog_effects = VALUES(edge_catalog_effects),
                edge_catalog_notes = VALUES(edge_catalog_notes),
                edge_catalog_source_pages = VALUES(edge_catalog_source_pages),
                edge_catalog_updated = CURRENT_TIMESTAMP(6)",
            self::TABLE_NAME,
        );
    }
}
