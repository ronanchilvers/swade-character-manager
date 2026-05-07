<?php

declare(strict_types=1);

namespace App\Service\Data;

use flight\database\SimplePdo;
use JsonException;
use RuntimeException;

class SkillCatalogSeeder
{
    private const TABLE_NAME = 'skill_catalog';

    public function __construct(
        private SimplePdo $pdo,
    ) {
    }

    public function seedFile(string $filename, string $source = 'core'): int
    {
        if (!is_file($filename)) {
            throw new RuntimeException(sprintf('Unable to find skill source file: %s', $filename));
        }

        $data = require $filename;
        if (!is_array($data) || !isset($data['entries']) || !is_array($data['entries'])) {
            throw new RuntimeException('Skill source file must return an array with an entries array');
        }

        return $this->seedEntries($data['entries'], $source);
    }

    public function seedEntries(array $entries, string $source = 'core'): int
    {
        $source = trim($source);
        if ('' === $source) {
            throw new RuntimeException('Skill source cannot be blank');
        }

        $seen = [];
        $rows = [];
        foreach ($entries as $entry) {
            $row = $this->rowFromEntry($entry, $source);
            $key = $row['skill_catalog_key'];
            if (isset($seen[$key])) {
                throw new RuntimeException(sprintf('Duplicate skill key in source: %s', $key));
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
            throw new RuntimeException('Each skill entry must be an array');
        }

        $key = $this->requiredString($entry, 'id');
        $name = $this->requiredString($entry, 'name');
        $linkedAttribute = $this->requiredString($entry, 'linked_attribute');
        $coreSkill = $this->requiredBool($entry, 'core_skill');

        return [
            'skill_catalog_key' => $key,
            'skill_catalog_source' => $source,
            'skill_catalog_name' => $name,
            'skill_catalog_linked_attribute' => $linkedAttribute,
            'skill_catalog_core_skill' => $coreSkill ? 1 : 0,
            'skill_catalog_arcane_background' => $this->nullableString($entry['arcane_background'] ?? null),
            'skill_catalog_summary' => (string) ($entry['summary'] ?? ''),
            'skill_catalog_requirements' => $this->encodeJson($entry['requirements'] ?? []),
            'skill_catalog_effects' => $this->encodeJson($entry['effects'] ?? []),
            'skill_catalog_notes' => $this->encodeJson($entry['notes'] ?? []),
            'skill_catalog_source_pages' => $this->encodeJson($entry['source_pages'] ?? []),
        ];
    }

    private function requiredString(array $entry, string $field): string
    {
        $value = $entry[$field] ?? null;
        if (!is_string($value) || '' === trim($value)) {
            throw new RuntimeException(sprintf('Skill entry must include a non-blank %s', $field));
        }

        return $value;
    }

    private function requiredBool(array $entry, string $field): bool
    {
        $value = $entry[$field] ?? null;
        if (!is_bool($value)) {
            throw new RuntimeException(sprintf('Skill entry must include a boolean %s', $field));
        }

        return $value;
    }

    private function nullableString(mixed $value): ?string
    {
        if (is_null($value)) {
            return null;
        }
        if (!is_string($value)) {
            throw new RuntimeException('Skill arcane_background must be a string or null');
        }

        return $value;
    }

    private function encodeJson(mixed $value): string
    {
        try {
            return json_encode($value, JSON_THROW_ON_ERROR);
        } catch (JsonException $ex) {
            throw new RuntimeException('Unable to encode skill catalog data as JSON', 0, $ex);
        }
    }

    private function upsertSql(): string
    {
        return sprintf(
            "INSERT INTO %s (
                skill_catalog_key,
                skill_catalog_source,
                skill_catalog_name,
                skill_catalog_linked_attribute,
                skill_catalog_core_skill,
                skill_catalog_arcane_background,
                skill_catalog_summary,
                skill_catalog_requirements,
                skill_catalog_effects,
                skill_catalog_notes,
                skill_catalog_source_pages
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                skill_catalog_source = VALUES(skill_catalog_source),
                skill_catalog_name = VALUES(skill_catalog_name),
                skill_catalog_linked_attribute = VALUES(skill_catalog_linked_attribute),
                skill_catalog_core_skill = VALUES(skill_catalog_core_skill),
                skill_catalog_arcane_background = VALUES(skill_catalog_arcane_background),
                skill_catalog_summary = VALUES(skill_catalog_summary),
                skill_catalog_requirements = VALUES(skill_catalog_requirements),
                skill_catalog_effects = VALUES(skill_catalog_effects),
                skill_catalog_notes = VALUES(skill_catalog_notes),
                skill_catalog_source_pages = VALUES(skill_catalog_source_pages),
                skill_catalog_updated = CURRENT_TIMESTAMP(6)",
            self::TABLE_NAME,
        );
    }
}
