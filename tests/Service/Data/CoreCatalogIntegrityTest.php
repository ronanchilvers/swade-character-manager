<?php

declare(strict_types=1);

namespace Tests\Service\Data;

use PHPUnit\Framework\TestCase;

class CoreCatalogIntegrityTest extends TestCase
{
    private const DATA_DIR = __DIR__ . '/../../../data';
    private const CATALOGS = ['hindrances', 'skills', 'edges'];

    public function testRuntimeCatalogFilesReturnUniqueEntries(): void
    {
        foreach (self::CATALOGS as $catalog) {
            $entries = $this->runtimeEntries($catalog);

            self::assertNotSame([], $entries, "{$catalog} should contain entries");
            $ids = [];
            foreach ($entries as $entry) {
                self::assertIsArray($entry, "{$catalog} entries should be arrays");
                self::assertArrayHasKey('id', $entry);
                self::assertIsString($entry['id']);
                self::assertNotSame('', trim($entry['id']));
                self::assertArrayNotHasKey($entry['id'], $ids, "Duplicate {$catalog} id {$entry['id']}");
                $ids[$entry['id']] = true;
            }
        }
    }

    public function testHindranceCatalogShape(): void
    {
        $names = [];
        foreach ($this->runtimeEntries('hindrances') as $entry) {
            $names[] = $entry['name'];
            self::assertArrayValueTypes($entry['levels'] ?? [], ['minor', 'major'], $entry['id'] . ' levels');
            $this->assertArrayFields($entry, ['requirements', 'effects', 'notes', 'source_pages']);
            $this->assertNumericSourcePages($entry);
        }

        self::assertSame($this->sorted($names), $names, 'Hindrances should stay sorted by display name');
    }

    public function testSkillCatalogShape(): void
    {
        $names = [];
        $validAttributes = ['agility', 'smarts', 'spirit', 'strength', 'vigor'];
        foreach ($this->runtimeEntries('skills') as $entry) {
            $names[] = $entry['name'];
            self::assertContains($entry['linked_attribute'], $validAttributes, $entry['id'] . ' linked_attribute');
            self::assertIsBool($entry['core_skill'], $entry['id'] . ' core_skill');
            $this->assertArrayFields($entry, ['requirements', 'effects', 'notes', 'source_pages']);
            $this->assertNumericSourcePages($entry);
        }

        self::assertSame($this->sorted($names), $names, 'Skills should stay sorted by display name');
    }

    public function testEdgeCatalogShape(): void
    {
        foreach ($this->runtimeEntries('edges') as $entry) {
            self::assertIsString($entry['category'], $entry['id'] . ' category');
            self::assertNotSame('', trim($entry['category']), $entry['id'] . ' category');
            self::assertIsBool($entry['repeatable'], $entry['id'] . ' repeatable');
            $this->assertArrayFields($entry, ['requirements', 'effects', 'notes', 'source_pages']);
            $this->assertNumericSourcePages($entry);
        }
    }

    public function testReferenceJsonFilesParseAndMatchRuntimeIds(): void
    {
        foreach (self::CATALOGS as $catalog) {
            $json = json_decode(
                file_get_contents(self::DATA_DIR . "/{$catalog}.json"),
                true,
                flags: JSON_THROW_ON_ERROR,
            );
            $jsonEntries = $json['entries'] ?? $json;

            self::assertIsArray($jsonEntries, "{$catalog}.json entries");
            self::assertSame(
                array_keys($this->idsByEntry($this->runtimeEntries($catalog))),
                array_keys($this->idsByEntry($jsonEntries)),
                "{$catalog}.json ids should match runtime PHP ids",
            );
        }
    }

    private function runtimeEntries(string $catalog): array
    {
        $data = require self::DATA_DIR . "/core/{$catalog}.php";

        self::assertIsArray($data, $catalog);
        self::assertArrayHasKey('entries', $data, $catalog);
        self::assertIsArray($data['entries'], $catalog);

        return $data['entries'];
    }

    private function idsByEntry(array $entries): array
    {
        $ids = [];
        foreach ($entries as $entry) {
            self::assertIsArray($entry);
            self::assertArrayHasKey('id', $entry);
            self::assertArrayNotHasKey($entry['id'], $ids, 'Duplicate JSON id ' . $entry['id']);
            $ids[$entry['id']] = true;
        }

        return $ids;
    }

    private function assertArrayFields(array $entry, array $fields): void
    {
        foreach ($fields as $field) {
            if (array_key_exists($field, $entry)) {
                self::assertIsArray($entry[$field], "{$entry['id']} {$field}");
            }
        }
    }

    private function assertNumericSourcePages(array $entry): void
    {
        foreach ($entry['source_pages'] ?? [] as $page) {
            self::assertIsNumeric($page, "{$entry['id']} source_pages");
        }
    }

    private function sorted(array $values): array
    {
        $sorted = $values;
        sort($sorted, SORT_NATURAL | SORT_FLAG_CASE);

        return $sorted;
    }

    private static function assertArrayValueTypes(array $values, array $allowed, string $label): void
    {
        foreach ($values as $value) {
            self::assertContains($value, $allowed, $label);
        }
    }
}
