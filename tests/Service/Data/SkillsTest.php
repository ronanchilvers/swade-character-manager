<?php

declare(strict_types=1);

namespace Tests\Service\Data;

use App\Service\Data\Skills;
use PHPUnit\Framework\TestCase;

class SkillsTest extends TestCase
{
    public function testCoreAndNonCoreCollectionsReflectLiveCatalog(): void
    {
        $service = new Skills(__DIR__ . '/../../../data');

        $core = $service->core();
        $nonCore = $service->nonCore();

        self::assertArrayHasKey('athletics', $core);
        self::assertTrue($core['athletics']['core_skill']);
        self::assertArrayHasKey('fighting', $nonCore);
        self::assertFalse($nonCore['fighting']['core_skill']);
        self::assertArrayNotHasKey('fighting', $core);
        self::assertArrayNotHasKey('athletics', $nonCore);
    }

    public function testAttributeForSkillReturnsLinkedAttributeFromLiveData(): void
    {
        $service = new Skills(__DIR__ . '/../../../data');

        self::assertSame('agility', $service->attributeForSkill('athletics'));
        self::assertSame('smarts', $service->attributeForSkill('notice'));
        self::assertNull($service->attributeForSkill('not_real'));
    }
}
