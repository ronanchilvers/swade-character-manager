<?php

declare(strict_types=1);

namespace Tests\Service\Data;

use App\Service\Data\Edges;
use flight\database\SimplePdo;
use PHPUnit\Framework\TestCase;

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
}
