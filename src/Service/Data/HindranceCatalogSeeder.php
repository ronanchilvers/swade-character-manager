<?php

declare(strict_types=1);

namespace App\Service\Data;

use flight\database\SimplePdo;
use JsonException;
use RuntimeException;

class HindranceCatalogSeeder
{
    private const TABLE_NAME = 'hindrance_catalog';

    public function __construct(
        private SimplePdo $pdo,
    ) {
    }

    public function seedFile(string $filename, string $source = 'core'): int
    {
        if (!is_file($filename)) {
            throw new RuntimeException(sprintf('Unable to find hindrance source file: %s', $filename));
        }

        $data = require $filename;
        if (!is_array($data) || !isset($data['entries']) || !is_array($data['entries'])) {
            throw new RuntimeException('Hindrance source file must return an array with an entries array');
        }

        return $this->seedEntries($data['entries'], $source);
    }

    public function seedEntries(array $entries, string $source = 'core'): int
    {
        $source = trim($source);
        if ('' === $source) {
            throw new RuntimeException('Hindrance source cannot be blank');
        }

        $seen = [];
        $rows = [];
        foreach ($entries as $entry) {
            $row = $this->rowFromEntry($entry, $source);
            $key = $row['hindrance_catalog_key'];
            if (isset($seen[$key])) {
                throw new RuntimeException(sprintf('Duplicate hindrance key in source: %s', $key));
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
            throw new RuntimeException('Each hindrance entry must be an array');
        }

        $key = $this->requiredString($entry, 'id');
        $name = $this->requiredString($entry, 'name');

        return [
            'hindrance_catalog_key' => $key,
            'hindrance_catalog_source' => $source,
            'hindrance_catalog_name' => $name,
            'hindrance_catalog_summary' => (string) ($entry['summary'] ?? ''),
            'hindrance_catalog_levels' => $this->encodeJson($entry['levels'] ?? []),
            'hindrance_catalog_requirements' => $this->encodeJson($entry['requirements'] ?? []),
            'hindrance_catalog_effects' => $this->encodeJson($entry['effects'] ?? []),
            'hindrance_catalog_notes' => $this->encodeJson($entry['notes'] ?? []),
            'hindrance_catalog_source_pages' => $this->encodeJson($entry['source_pages'] ?? []),
        ];
    }

    private function requiredString(array $entry, string $field): string
    {
        $value = $entry[$field] ?? null;
        if (!is_string($value) || '' === trim($value)) {
            throw new RuntimeException(sprintf('Hindrance entry must include a non-blank %s', $field));
        }

        return $value;
    }

    private function encodeJson(mixed $value): string
    {
        try {
            return json_encode($value, JSON_THROW_ON_ERROR);
        } catch (JsonException $ex) {
            throw new RuntimeException('Unable to encode hindrance catalog data as JSON', 0, $ex);
        }
    }

    private function upsertSql(): string
    {
        return sprintf(
            "INSERT INTO %s (
                hindrance_catalog_key,
                hindrance_catalog_source,
                hindrance_catalog_name,
                hindrance_catalog_summary,
                hindrance_catalog_levels,
                hindrance_catalog_requirements,
                hindrance_catalog_effects,
                hindrance_catalog_notes,
                hindrance_catalog_source_pages
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                hindrance_catalog_source = VALUES(hindrance_catalog_source),
                hindrance_catalog_name = VALUES(hindrance_catalog_name),
                hindrance_catalog_summary = VALUES(hindrance_catalog_summary),
                hindrance_catalog_levels = VALUES(hindrance_catalog_levels),
                hindrance_catalog_requirements = VALUES(hindrance_catalog_requirements),
                hindrance_catalog_effects = VALUES(hindrance_catalog_effects),
                hindrance_catalog_notes = VALUES(hindrance_catalog_notes),
                hindrance_catalog_source_pages = VALUES(hindrance_catalog_source_pages),
                hindrance_catalog_updated = CURRENT_TIMESTAMP(6)",
            self::TABLE_NAME,
        );
    }
}
