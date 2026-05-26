<?php

declare(strict_types=1);

namespace Tests\Service\Data;

use App\Service\Data\Hindrances;
use flight\database\SimplePdo;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class HindrancesTest extends TestCase
{
    public function testBuilderDataCanUseDatabaseBackedCatalogRows(): void
    {
        $pdo = $this->createMock(SimplePdo::class);
        $pdo->expects(self::once())
            ->method('fetchAll')
            ->willReturn([
                [
                    'hindrance_catalog_key' => 'all_thumbs',
                    'hindrance_catalog_source' => 'core',
                    'hindrance_catalog_name' => 'All Thumbs',
                    'hindrance_catalog_summary' => 'The hero is bad with mechanical or electrical devices.',
                    'hindrance_catalog_levels' => '["minor"]',
                    'hindrance_catalog_requirements' => '[]',
                    'hindrance_catalog_effects' => '[{"level":"minor","details":"Applies to Trait rolls made while using mechanical or electrical devices."}]',
                    'hindrance_catalog_notes' => '[]',
                    'hindrance_catalog_source_pages' => '[24]',
                ],
            ]);

        $service = new Hindrances(__DIR__ . '/../../../data', $pdo);
        $hindrances = $service->forBuilder();

        self::assertSame('all_thumbs', $hindrances[0]['id']);
        self::assertSame('core', $hindrances[0]['source']);
        self::assertSame(['minor'], $hindrances[0]['levels']);
        self::assertSame([24], $hindrances[0]['source_pages']);
        self::assertSame(
            ['Applies to Trait rolls made while using mechanical or electrical devices.'],
            $hindrances[0]['effects_by_level']['minor'],
        );
    }

    public function testBuilderDataGroupsMinorAndMajorEffectsWithoutReplacingRawEffects(): void
    {
        $service = new Hindrances(__DIR__ . '/../../../data');

        $hindrances = [];
        foreach ($service->forBuilder() as $hindrance) {
            $hindrances[$hindrance['id']] = $hindrance;
        }

        self::assertSame(
            [
                'Applies to Trait rolls made while using mechanical or electrical devices.',
                'A critical failure can break the device.',
            ],
            $hindrances['all_thumbs']['effects_by_level']['minor']
        );
        self::assertArrayNotHasKey(
            'major',
            $hindrances['all_thumbs']['effects_by_level']
        );

        self::assertSame(
            [
                'Subtract 1 from any Trait roll dependent on vision.',
                'If glasses are lost or broken in a setting where they exist, the character is Distracted.',
            ],
            $hindrances['bad_eyes']['effects_by_level']['minor']
        );
        self::assertSame(
            [
                'Subtract 2 from any Trait roll dependent on vision.',
                'If glasses are lost or broken in a setting where they exist, the character is also Vulnerable.',
            ],
            $hindrances['bad_eyes']['effects_by_level']['major']
        );
        self::assertCount(4, $hindrances['bad_eyes']['effects']);

        self::assertSame([], $hindrances['arrogant']['effects_by_level']);
        self::assertSame([], $hindrances['arrogant']['effects']);
    }

    public function testFileCatalogIsUsedWhenNoPdoIsProvided(): void
    {
        $service = new Hindrances(__DIR__ . '/../../../data');

        self::assertCount(57, $service->all());
        self::assertSame('All Thumbs', $service->forId('all_thumbs')['name']);
        self::assertNull($service->forId('missing'));
    }

    public function testCatalogCanBeFilteredBySource(): void
    {
        $pdo = $this->createMock(SimplePdo::class);
        $pdo->expects(self::once())
            ->method('fetchAll')
            ->willReturn([
                $this->row('core_hindrance', 'core'),
                $this->row('fantasy_hindrance', 'fantasy'),
            ]);

        $entries = (new Hindrances(__DIR__ . '/../../../data', $pdo))->forBuilder(['core']);

        self::assertSame(['core_hindrance'], array_column($entries, 'id'));
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

        self::assertCount(57, (new Hindrances(__DIR__ . '/../../../data', $throwingPdo))->all());
        self::assertCount(57, (new Hindrances(__DIR__ . '/../../../data', $emptyPdo))->all());
    }

    public function testDatabaseRowsDecodeInvalidJsonColumnsAsEmptyArrays(): void
    {
        $pdo = $this->createMock(SimplePdo::class);
        $pdo->expects(self::once())
            ->method('fetchAll')
            ->willReturn([
                [
                    'hindrance_catalog_key' => 'quirk',
                    'hindrance_catalog_source' => 'custom',
                    'hindrance_catalog_name' => 'Quirk',
                    'hindrance_catalog_summary' => '',
                    'hindrance_catalog_levels' => '',
                    'hindrance_catalog_requirements' => '{bad json',
                    'hindrance_catalog_effects' => null,
                    'hindrance_catalog_notes' => '"not an array"',
                    'hindrance_catalog_source_pages' => '[22]',
                ],
            ]);

        $entry = (new Hindrances(__DIR__ . '/../../../data', $pdo))->forId('quirk');

        self::assertSame('custom', $entry['source']);
        self::assertSame([], $entry['levels']);
        self::assertSame([], $entry['requirements']);
        self::assertSame([], $entry['effects']);
        self::assertSame([], $entry['notes']);
        self::assertSame([22], $entry['source_pages']);
    }

    public function testBuilderIgnoresMalformedEffectRowsWhenGrouping(): void
    {
        $pdo = $this->createMock(SimplePdo::class);
        $pdo->expects(self::once())
            ->method('fetchAll')
            ->willReturn([
                [
                    'hindrance_catalog_key' => 'bad_eyes',
                    'hindrance_catalog_source' => 'core',
                    'hindrance_catalog_name' => 'Bad Eyes',
                    'hindrance_catalog_summary' => '',
                    'hindrance_catalog_levels' => '["minor","major"]',
                    'hindrance_catalog_requirements' => '[]',
                    'hindrance_catalog_effects' => json_encode([
                        ['level' => 'minor', 'details' => 'Minor effect'],
                        ['level' => 'major', 'details' => 'Major effect'],
                        ['level' => 'minor', 'details' => ''],
                        ['level' => 5, 'details' => 'Wrong level type'],
                        ['details' => 'Missing level'],
                    ]),
                    'hindrance_catalog_notes' => '[]',
                    'hindrance_catalog_source_pages' => '[]',
                ],
            ]);

        $entry = (new Hindrances(__DIR__ . '/../../../data', $pdo))->forBuilder()[0];

        self::assertSame(
            [
                'minor' => ['Minor effect'],
                'major' => ['Major effect'],
            ],
            $entry['effects_by_level'],
        );
        self::assertCount(5, $entry['effects']);
    }

    private function row(string $key, string $source): array
    {
        return [
            'hindrance_catalog_key' => $key,
            'hindrance_catalog_source' => $source,
            'hindrance_catalog_name' => ucfirst($key),
            'hindrance_catalog_summary' => '',
            'hindrance_catalog_levels' => '["minor"]',
            'hindrance_catalog_requirements' => '[]',
            'hindrance_catalog_effects' => '[]',
            'hindrance_catalog_notes' => '[]',
            'hindrance_catalog_source_pages' => '[]',
        ];
    }
}
