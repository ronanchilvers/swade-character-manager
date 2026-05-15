<?php

declare(strict_types=1);

namespace App\Service\Data;

use App\Service\Data;
use ArrayIterator;
use flight\database\SimplePdo;
use Throwable;

class Skills extends Data
{
    private const TABLE_NAME = 'skill_catalog';

    protected ?array $core = null;
    protected ?array $nonCore = null;
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

    public function core()
    {
        if (!is_array($this->core)) {
            $this->processSkills();
        }

        return $this->core;
    }

    public function nonCore()
    {
        if (!is_array($this->nonCore)) {
            $this->processSkills();
        }

        return $this->nonCore;
    }

    public function attributeForSkill(string $skill): ?string
    {
        $iterator = new ArrayIterator($this->all());
        foreach ($iterator as $entry) {
            if ($entry['id'] == $skill) {
                return $entry['linked_attribute'];
            }
        }

        return null;
    }

    protected function processSkills(): void
    {
        $skills = $this->all();
        $this->core = $this->nonCore = [];
        foreach ($skills as $skill) {
            if ($skill['core_skill']) {
                $this->core[$skill['id']] = $skill;
                continue;
            }
            $this->nonCore[$skill['id']] = $skill;
        }
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
                    'SELECT * FROM %s ORDER BY skill_catalog_name ASC',
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
        $this->core = null;
        $this->nonCore = null;

        return $this->databaseEntries;
    }

    protected function entryFromRow(mixed $row): array
    {
        return [
            'id' => (string) $row['skill_catalog_key'],
            'source' => (string) $row['skill_catalog_source'],
            'name' => (string) $row['skill_catalog_name'],
            'linked_attribute' => (string) $row['skill_catalog_linked_attribute'],
            'core_skill' => (bool) (int) $row['skill_catalog_core_skill'],
            'arcane_background' => is_null($row['skill_catalog_arcane_background'])
                ? null
                : (string) $row['skill_catalog_arcane_background'],
            'summary' => (string) $row['skill_catalog_summary'],
            'requirements' => $this->decodeJson($row['skill_catalog_requirements']),
            'effects' => $this->decodeJson($row['skill_catalog_effects']),
            'notes' => $this->decodeJson($row['skill_catalog_notes']),
            'source_pages' => $this->decodeJson($row['skill_catalog_source_pages']),
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
