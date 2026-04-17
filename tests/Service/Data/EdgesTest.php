<?php

declare(strict_types=1);

namespace Tests\Service\Data;

use App\Service\Data\Edges;
use PHPUnit\Framework\TestCase;

class EdgesTest extends TestCase
{
    public function testLiveCatalogLoadsKnownEdgesAndNotes(): void
    {
        $service = new Edges(__DIR__ . '/../../../data');

        $alertness = $service->forId('alertness');
        $newPowers = $service->forId('new_powers');

        self::assertSame('background', $alertness['category']);
        self::assertSame('The hero is exceptionally observant.', $alertness['summary']);
        self::assertContains('This Edge may be taken multiple times.', $newPowers['notes']);
    }
}
