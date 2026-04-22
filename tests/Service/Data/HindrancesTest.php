<?php

declare(strict_types=1);

namespace Tests\Service\Data;

use App\Service\Data\Hindrances;
use PHPUnit\Framework\TestCase;

class HindrancesTest extends TestCase
{
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
