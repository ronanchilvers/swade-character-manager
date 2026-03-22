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
}
