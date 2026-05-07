<?php

declare(strict_types=1);

namespace Tests\Service\Data;

use App\Service\Data\Skills;
use flight\database\SimplePdo;
use PHPUnit\Framework\TestCase;

class SkillsTest extends TestCase
{
    public function testCatalogCanUseDatabaseBackedRows(): void
    {
        $pdo = $this->createMock(SimplePdo::class);
        $pdo->expects(self::once())
            ->method('fetchAll')
            ->willReturn([
                [
                    'skill_catalog_key' => 'athletics',
                    'skill_catalog_source' => 'core',
                    'skill_catalog_name' => 'Athletics',
                    'skill_catalog_linked_attribute' => 'agility',
                    'skill_catalog_core_skill' => '1',
                    'skill_catalog_arcane_background' => null,
                    'skill_catalog_summary' => 'General athletic coordination and ability.',
                    'skill_catalog_requirements' => '[]',
                    'skill_catalog_effects' => '[]',
                    'skill_catalog_notes' => '["Covers climbing, jumping, balancing, wrestling, skiing, swimming, throwing, and catching."]',
                    'skill_catalog_source_pages' => '[60]',
                ],
                [
                    'skill_catalog_key' => 'faith',
                    'skill_catalog_source' => 'core',
                    'skill_catalog_name' => 'Faith',
                    'skill_catalog_linked_attribute' => 'spirit',
                    'skill_catalog_core_skill' => '0',
                    'skill_catalog_arcane_background' => 'Miracles',
                    'skill_catalog_summary' => 'Arcane skill used by Arcane Background (Miracles).',
                    'skill_catalog_requirements' => '[]',
                    'skill_catalog_effects' => '[]',
                    'skill_catalog_notes' => '[]',
                    'skill_catalog_source_pages' => '[60]',
                ],
            ]);

        $service = new Skills(__DIR__ . '/../../../data', $pdo);

        $all = $service->all();
        $core = $service->core();
        $nonCore = $service->nonCore();

        self::assertSame('athletics', $all[0]['id']);
        self::assertSame('core', $all[0]['source']);
        self::assertTrue($all[0]['core_skill']);
        self::assertSame([60], $all[0]['source_pages']);
        self::assertSame(
            ['Covers climbing, jumping, balancing, wrestling, skiing, swimming, throwing, and catching.'],
            $all[0]['notes'],
        );
        self::assertNull($all[0]['arcane_background']);

        self::assertSame('faith', $all[1]['id']);
        self::assertFalse($all[1]['core_skill']);
        self::assertSame('Miracles', $all[1]['arcane_background']);
        self::assertArrayHasKey('athletics', $core);
        self::assertArrayHasKey('faith', $nonCore);
        self::assertSame('spirit', $service->attributeForSkill('faith'));
    }

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
