<?php

declare(strict_types=1);

namespace Tests\Service\Data;

use App\Service\Data\Edges;
use flight\database\SimplePdo;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class EdgesTest extends TestCase
{
    public function testCatalogCanUseDatabaseBackedRows(): void
    {
        $pdo = $this->createMock(SimplePdo::class);
        $pdo->expects(self::once())
            ->method('fetchAll')
            ->willReturn([
                [
                    'edge_catalog_key' => 'alertness',
                    'edge_catalog_source' => 'core',
                    'edge_catalog_name' => 'Alertness',
                    'edge_catalog_category' => 'background',
                    'edge_catalog_repeatable' => '0',
                    'edge_catalog_summary' => 'The hero is exceptionally observant.',
                    'edge_catalog_requirements' => '[{"type":"rank","target":"rank","value":"Novice"}]',
                    'edge_catalog_effects' => '[{"level":"base","details":"+2 to Notice rolls."}]',
                    'edge_catalog_notes' => '[]',
                    'edge_catalog_source_pages' => '[61]',
                ],
                [
                    'edge_catalog_key' => 'new_powers',
                    'edge_catalog_source' => 'core',
                    'edge_catalog_name' => 'New Powers',
                    'edge_catalog_category' => 'power',
                    'edge_catalog_repeatable' => '1',
                    'edge_catalog_summary' => 'The hero learns new powers.',
                    'edge_catalog_requirements' => '[]',
                    'edge_catalog_effects' => '[]',
                    'edge_catalog_notes' => '["This Edge may be taken multiple times."]',
                    'edge_catalog_source_pages' => '[70]',
                ],
            ]);

        $service = new Edges(__DIR__ . '/../../../data', $pdo);

        $all = $service->all();
        $alertness = $service->forId('alertness');
        $newPowers = $service->forId('new_powers');

        self::assertSame('alertness', $all[0]['id']);
        self::assertSame('core', $all[0]['source']);
        self::assertSame('background', $all[0]['category']);
        self::assertFalse($all[0]['repeatable']);
        self::assertSame(
            [['type' => 'rank', 'target' => 'rank', 'value' => 'Novice']],
            $all[0]['requirements'],
        );
        self::assertSame([61], $all[0]['source_pages']);

        self::assertSame('Alertness', $alertness['name']);
        self::assertTrue($newPowers['repeatable']);
        self::assertSame(['This Edge may be taken multiple times.'], $newPowers['notes']);
    }

    public function testLiveCatalogLoadsKnownEdgesAndRepeatableFlags(): void
    {
        $service = new Edges(__DIR__ . '/../../../data');

        $alertness = $service->forId('alertness');
        $newPowers = $service->forId('new_powers');
        $scholar = $service->forId('scholar');

        self::assertSame('background', $alertness['category']);
        self::assertSame('The hero is exceptionally observant.', $alertness['summary']);
        self::assertFalse($alertness['repeatable']);
        self::assertContains('This Edge may be taken multiple times.', $newPowers['notes']);
        self::assertTrue($newPowers['repeatable']);
        self::assertTrue($scholar['repeatable']);
    }

    public function testFileCatalogIsUsedWhenDatabaseRowsAreUnavailable(): void
    {
        $throwingPdo = $this->createMock(SimplePdo::class);
        $throwingPdo->expects(self::once())
            ->method('fetchAll')
            ->willThrowException(new RuntimeException('catalog table missing'));

        $emptyPdo = $this->createMock(SimplePdo::class);
        $emptyPdo->expects(self::once())
            ->method('fetchAll')
            ->willReturn([]);

        self::assertCount(134, (new Edges(__DIR__ . '/../../../data'))->all());
        self::assertCount(134, (new Edges(__DIR__ . '/../../../data', $throwingPdo))->all());
        self::assertCount(134, (new Edges(__DIR__ . '/../../../data', $emptyPdo))->all());
    }

    public function testDatabaseRowsDecodeInvalidJsonColumnsAsEmptyArrays(): void
    {
        $pdo = $this->createMock(SimplePdo::class);
        $pdo->expects(self::once())
            ->method('fetchAll')
            ->willReturn([
                [
                    'edge_catalog_key' => 'custom_edge',
                    'edge_catalog_source' => 'custom',
                    'edge_catalog_name' => 'Custom Edge',
                    'edge_catalog_category' => 'background',
                    'edge_catalog_repeatable' => '0',
                    'edge_catalog_summary' => '',
                    'edge_catalog_requirements' => '',
                    'edge_catalog_effects' => '{bad json',
                    'edge_catalog_notes' => null,
                    'edge_catalog_source_pages' => '[61]',
                ],
            ]);

        $entry = (new Edges(__DIR__ . '/../../../data', $pdo))->forId('custom_edge');

        self::assertSame('custom', $entry['source']);
        self::assertSame('background', $entry['category']);
        self::assertFalse($entry['repeatable']);
        self::assertSame([], $entry['requirements']);
        self::assertSame([], $entry['effects']);
        self::assertSame([], $entry['notes']);
        self::assertSame([61], $entry['source_pages']);
    }

    public function testDatabaseRowsMapTruthyAndFalsyRepeatableValues(): void
    {
        $pdo = $this->createMock(SimplePdo::class);
        $pdo->expects(self::once())
            ->method('fetchAll')
            ->willReturn([
                $this->row('one', '1'),
                $this->row('zero', '0'),
            ]);

        $service = new Edges(__DIR__ . '/../../../data', $pdo);

        self::assertTrue($service->forId('one')['repeatable']);
        self::assertFalse($service->forId('zero')['repeatable']);
    }

    private function row(string $key, string $repeatable): array
    {
        return [
            'edge_catalog_key' => $key,
            'edge_catalog_source' => 'custom',
            'edge_catalog_name' => ucfirst($key),
            'edge_catalog_category' => 'background',
            'edge_catalog_repeatable' => $repeatable,
            'edge_catalog_summary' => '',
            'edge_catalog_requirements' => '[]',
            'edge_catalog_effects' => '[]',
            'edge_catalog_notes' => '[]',
            'edge_catalog_source_pages' => '[]',
        ];
    }
}
