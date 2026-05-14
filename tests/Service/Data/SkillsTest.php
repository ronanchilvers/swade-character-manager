<?php

declare(strict_types=1);

namespace Tests\Service\Data;

use App\Service\Data\Skills;
use flight\database\SimplePdo;
use PHPUnit\Framework\TestCase;
use RuntimeException;

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

        self::assertCount(32, (new Skills(__DIR__ . '/../../../data'))->all());
        self::assertCount(32, (new Skills(__DIR__ . '/../../../data', $throwingPdo))->all());
        self::assertCount(32, (new Skills(__DIR__ . '/../../../data', $emptyPdo))->all());
    }

    public function testDatabaseRowsDecodeInvalidJsonColumnsAsEmptyArrays(): void
    {
        $pdo = $this->createMock(SimplePdo::class);
        $pdo->expects(self::once())
            ->method('fetchAll')
            ->willReturn([
                [
                    'skill_catalog_key' => 'spellcasting',
                    'skill_catalog_source' => 'custom',
                    'skill_catalog_name' => 'Spellcasting',
                    'skill_catalog_linked_attribute' => 'smarts',
                    'skill_catalog_core_skill' => '0',
                    'skill_catalog_arcane_background' => 'Magic',
                    'skill_catalog_summary' => '',
                    'skill_catalog_requirements' => '',
                    'skill_catalog_effects' => '{bad json',
                    'skill_catalog_notes' => null,
                    'skill_catalog_source_pages' => '[60]',
                ],
            ]);

        $entry = (new Skills(__DIR__ . '/../../../data', $pdo))->forId('spellcasting');

        self::assertSame('custom', $entry['source']);
        self::assertSame('Magic', $entry['arcane_background']);
        self::assertSame([], $entry['requirements']);
        self::assertSame([], $entry['effects']);
        self::assertSame([], $entry['notes']);
        self::assertSame([60], $entry['source_pages']);
    }

    public function testCoreAndNonCoreCollectionsAreBuiltFromDatabaseRows(): void
    {
        $pdo = $this->createMock(SimplePdo::class);
        $pdo->expects(self::once())
            ->method('fetchAll')
            ->willReturn([
                [
                    'skill_catalog_key' => 'custom_core',
                    'skill_catalog_source' => 'custom',
                    'skill_catalog_name' => 'Custom Core',
                    'skill_catalog_linked_attribute' => 'vigor',
                    'skill_catalog_core_skill' => '1',
                    'skill_catalog_arcane_background' => null,
                    'skill_catalog_summary' => '',
                    'skill_catalog_requirements' => '[]',
                    'skill_catalog_effects' => '[]',
                    'skill_catalog_notes' => '[]',
                    'skill_catalog_source_pages' => '[]',
                ],
                [
                    'skill_catalog_key' => 'custom_arcane',
                    'skill_catalog_source' => 'custom',
                    'skill_catalog_name' => 'Custom Arcane',
                    'skill_catalog_linked_attribute' => 'spirit',
                    'skill_catalog_core_skill' => '0',
                    'skill_catalog_arcane_background' => 'Miracles',
                    'skill_catalog_summary' => '',
                    'skill_catalog_requirements' => '[]',
                    'skill_catalog_effects' => '[]',
                    'skill_catalog_notes' => '[]',
                    'skill_catalog_source_pages' => '[]',
                ],
            ]);

        $service = new Skills(__DIR__ . '/../../../data', $pdo);

        self::assertArrayHasKey('custom_core', $service->core());
        self::assertArrayNotHasKey('athletics', $service->core());
        self::assertArrayHasKey('custom_arcane', $service->nonCore());
        self::assertSame('spirit', $service->attributeForSkill('custom_arcane'));
    }
}
