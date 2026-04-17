<?php

declare(strict_types=1);

namespace Tests\Service\Data;

use App\Service\Data\Edges;
use PHPUnit\Framework\TestCase;

class EdgesTest extends TestCase
{
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
