<?php

declare(strict_types=1);

namespace Tests\Character;

use App\Character\Sheet;
use App\Entity;
use App\Entity\Factory\Character as CharacterFactory;
use App\Entity\Factory\Skill as SkillFactory;
use App\Entity\Validator;
use App\Service\Data\Edges;
use App\Service\Data\Hindrances;
use App\Service\Data\Manager;
use App\Service\Data\Skills;
use flight\database\SimplePdo;
use PHPUnit\Framework\TestCase;

class SheetTest extends TestCase
{
    private const DATA_DIR = __DIR__ . '/../../data';

    public function testIdentityExposesNameRankAndDerivedTraits(): void
    {
        $result = $this->build(
            character: new Entity([
                'name' => 'Mara',
                'rank' => 'Novice',
                'pace' => 6,
                'parry' => 5,
                'toughness' => 7,
            ]),
        );

        self::assertSame('Mara', $result['identity']['name']);
        self::assertSame('Novice', $result['identity']['rank']);
        self::assertSame(6, $result['identity']['pace']);
        self::assertSame(5, $result['identity']['parry']);
        self::assertSame(7, $result['identity']['toughness']);
    }

    public function testMissingIdentityTraitsReturnAsNull(): void
    {
        $result = $this->build(character: new Entity(['name' => 'Nameless']));

        self::assertNull($result['identity']['pace']);
        self::assertNull($result['identity']['parry']);
        self::assertNull($result['identity']['toughness']);
    }

    public function testStateExposesShakenAsBoolean(): void
    {
        $set = $this->build(character: new Entity(['name' => 'Mara', 'shaken' => 1]));
        self::assertTrue($set['state']['shaken']);

        $unset = $this->build(character: new Entity(['name' => 'Mara']));
        self::assertFalse($unset['state']['shaken']);

        $zero = $this->build(character: new Entity(['name' => 'Mara', 'shaken' => 0]));
        self::assertFalse($zero['state']['shaken']);
    }

    public function testAttributesAreReturnedInCanonicalOrderWithLabels(): void
    {
        $result = $this->build(
            character: new Entity([
                'agility' => 6,
                'smarts' => 4,
                'spirit' => 8,
                'strength' => 10,
                'vigor' => 12,
            ]),
        );

        $keys = array_column($result['attributes'], 'key');
        self::assertSame(
            ['agility', 'smarts', 'spirit', 'strength', 'vigor'],
            $keys,
        );

        $labels = array_column($result['attributes'], 'label');
        self::assertSame(
            ['Agility', 'Smarts', 'Spirit', 'Strength', 'Vigor'],
            $labels,
        );

        $dice = array_column($result['attributes'], 'die');
        self::assertSame([6, 4, 8, 10, 12], $dice);

        foreach ($result['attributes'] as $row) {
            self::assertSame($row['die'], $row['max']);
        }
    }

    public function testHindrancesAreEnrichedFromCatalogAndLevelPreserved(): void
    {
        $result = $this->build(
            character: new Entity(),
            hindrances: [
                new Entity(['key' => 'all_thumbs', 'level' => 'minor']),
            ],
        );

        self::assertCount(1, $result['hindrances']);
        $row = $result['hindrances'][0];
        self::assertSame('all_thumbs', $row['key']);
        self::assertSame('All Thumbs', $row['name']);
        self::assertSame('minor', $row['level']);
        self::assertNotSame('', $row['summary']);
    }

    public function testMissingHindranceCatalogEntryFallsBackToStoredKey(): void
    {
        $result = $this->build(
            character: new Entity(),
            hindrances: [
                new Entity(['key' => 'not_a_real_hindrance', 'level' => 'major']),
            ],
        );

        $row = $result['hindrances'][0];
        self::assertSame('Not A Real Hindrance', $row['name']);
        self::assertSame('major', $row['level']);
        self::assertSame('', $row['summary']);
    }

    public function testSkillsAreEnrichedAndSortedCoreFirstThenAlpha(): void
    {
        $result = $this->build(
            character: new Entity(),
            skills: [
                new Entity(['key' => 'fighting', 'die' => 8, 'attribute' => 'agility', 'core' => 'no']),
                new Entity(['key' => 'notice', 'die' => 6, 'attribute' => 'smarts', 'core' => 'yes']),
                new Entity(['key' => 'athletics', 'die' => 4, 'attribute' => 'agility', 'core' => 'yes']),
            ],
        );

        $names = array_column($result['skills'], 'name');
        self::assertSame(['Athletics', 'Notice', 'Fighting'], $names);

        $core = array_column($result['skills'], 'is_core');
        self::assertSame([true, true, false], $core);

        $firstSkill = $result['skills'][0];
        self::assertSame('agility', $firstSkill['linked_attribute']);
        self::assertSame(4, $firstSkill['die']);
        self::assertSame([4, 6, 8, 10, 12], $firstSkill['die_faces']);
    }

    public function testMissingSkillCatalogEntryFallsBackToStoredKey(): void
    {
        $result = $this->build(
            character: new Entity(),
            skills: [
                new Entity(['key' => 'not_a_real_skill', 'die' => 4, 'attribute' => 'spirit', 'core' => 'no']),
            ],
        );

        $row = $result['skills'][0];
        self::assertSame('Not A Real Skill', $row['name']);
        self::assertSame('spirit', $row['linked_attribute']);
        self::assertFalse($row['is_core']);
    }

    public function testEdgesAreEnrichedFromCatalogInCharacterOrder(): void
    {
        $result = $this->build(
            character: new Entity(),
            edges: [
                new Entity(['key' => 'brawny', 'count' => 1]),
                new Entity(['key' => 'alertness', 'count' => 1]),
            ],
        );

        self::assertCount(2, $result['edges']);
        $names = array_column($result['edges'], 'name');
        self::assertSame(['Brawny', 'Alertness'], $names);
        self::assertSame('background', $result['edges'][0]['category']);
        self::assertNotSame('', $result['edges'][0]['summary']);
    }

    public function testRepeatableEdgeCountIsPreserved(): void
    {
        $result = $this->build(
            character: new Entity(),
            edges: [new Entity(['key' => 'new_powers', 'count' => 3])],
        );

        self::assertSame(3, $result['edges'][0]['count']);
    }

    public function testEdgesDefaultCountToOneWhenMissing(): void
    {
        $result = $this->build(
            character: new Entity(),
            edges: [new Entity(['key' => 'alertness'])],
        );

        self::assertSame(1, $result['edges'][0]['count']);
    }

    public function testMissingEdgeCatalogEntryFallsBackToStoredKey(): void
    {
        $result = $this->build(
            character: new Entity(),
            edges: [new Entity(['key' => 'not_a_real_edge', 'count' => 1])],
        );

        $row = $result['edges'][0];
        self::assertSame('Not A Real Edge', $row['name']);
        self::assertSame('', $row['category']);
        self::assertSame('', $row['summary']);
    }

    private function build(
        Entity $character,
        array $hindrances = [],
        array $skills = [],
        array $edges = [],
    ): array {
        $manager = new Manager(self::DATA_DIR);
        $manager->addType(Edges::class);
        $manager->addType(Hindrances::class);
        $manager->addType(Skills::class);

        $factory = new CharacterFactory(
            $this->createStub(SimplePdo::class),
            new Validator(),
            $this->createStub(SkillFactory::class),
            $this->createStub(Manager::class),
        );

        return new Sheet()->build(
            $character,
            $hindrances,
            $skills,
            $edges,
            $manager,
            $factory,
        );
    }
}
