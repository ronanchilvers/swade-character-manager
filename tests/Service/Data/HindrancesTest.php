<?php

declare(strict_types=1);

namespace Tests\Service\Data;

use App\Service\Data\Hindrances;
use flight\database\SimplePdo;
use PHPUnit\Framework\TestCase;

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
}
