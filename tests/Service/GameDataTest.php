<?php

declare(strict_types=1);

namespace Tests\Service;

use App\Service\GameData;
use PHPUnit\Framework\TestCase;

class GameDataTest extends TestCase
{
    public function testLoadsHindranceEntriesByKey(): void
    {
        $service = new GameData(__DIR__ . '/../../data');

        $hindrance = $service->hindrance('all_thumbs');

        self::assertNotNull($hindrance);
        self::assertSame('All Thumbs', $hindrance['name']);
        self::assertSame([
            [
                'level' => 'minor',
                'details' => 'Applies to Trait rolls made while using mechanical or electrical devices. A critical failure can break the device.',
            ],
        ], $hindrance['effects']);
    }

    public function testReturnsNullForUnknownHindranceKey(): void
    {
        $service = new GameData(__DIR__ . '/../../data');

        self::assertNull($service->hindrance('not_a_real_hindrance'));
    }

    public function testValidatesSupportedLevels(): void
    {
        $service = new GameData(__DIR__ . '/../../data');

        self::assertTrue($service->hindranceSupportsLevel('bad_eyes', 'minor'));
        self::assertTrue($service->hindranceSupportsLevel('bad_eyes', 'major'));
        self::assertFalse($service->hindranceSupportsLevel('all_thumbs', 'major'));
    }

    public function testMergesInterleavedEffectsByLevelOrder(): void
    {
        $service = new GameData(__DIR__ . '/../../data');

        self::assertSame([
            [
                'level' => 'minor',
                'details' => 'Subtract 1 from any Trait roll dependent on vision. If glasses are lost or broken in a setting where they exist, the character is Distracted.',
            ],
            [
                'level' => 'major',
                'details' => 'Subtract 2 from any Trait roll dependent on vision. If glasses are lost or broken in a setting where they exist, the character is also Vulnerable.',
            ],
        ], $service->hindrance('bad_eyes')['effects']);
    }

    public function testKeepsEmptyEffectsForRoleplayingHindrances(): void
    {
        $service = new GameData(__DIR__ . '/../../data');

        self::assertSame([], $service->hindrance('arrogant')['effects']);
    }
}
