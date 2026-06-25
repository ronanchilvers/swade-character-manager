<?php

declare(strict_types=1);

namespace Tests\Service\Data;

use App\Service\Data\Archetypes;
use PHPUnit\Framework\TestCase;

class ArchetypesTest extends TestCase
{
    private string $dataDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dataDir = sys_get_temp_dir() . '/swade-archetypes-test-' . bin2hex(random_bytes(4));
        mkdir($this->dataDir);
        mkdir($this->dataDir . '/archetypes');

        file_put_contents($this->dataDir . '/archetypes/warrior.json', json_encode([
            'name'        => 'Warrior',
            'summary'     => 'A seasoned fighter.',
            'attributes'  => ['strength' => 8, 'vigor' => 8, 'agility' => 6, 'smarts' => 4, 'spirit' => 6],
            'skills'      => [['key' => 'fighting', 'die' => 8]],
            'hindrances'  => [['key' => 'loyal', 'level' => 'minor']],
            'edges'       => [['key' => 'brawny']],
            'names'       => ['Thor', 'Mira'],
        ]));

        file_put_contents($this->dataDir . '/archetypes/rogue.json', json_encode([
            'name'    => 'Rogue',
            'summary' => 'Shadows and subterfuge.',
            'names'   => ['Zara'],
        ]));

        file_put_contents($this->dataDir . '/archetypes/hidden.json', json_encode([
            'name'   => 'Hidden',
            'active' => false,
            'names'  => ['Ghost'],
        ]));
    }

    protected function tearDown(): void
    {
        @unlink($this->dataDir . '/archetypes/warrior.json');
        @unlink($this->dataDir . '/archetypes/rogue.json');
        @unlink($this->dataDir . '/archetypes/hidden.json');
        @rmdir($this->dataDir . '/archetypes');
        @rmdir($this->dataDir);

        parent::tearDown();
    }

    public function testAllReturnsSortedArchetypesWithIdFromFilename(): void
    {
        $catalog = new Archetypes($this->dataDir);

        $all = $catalog->all();

        self::assertCount(2, $all);
        // ksort means rogue (r) < warrior (w); hidden is excluded
        self::assertSame('rogue', $all[0]['id']);
        self::assertSame('warrior', $all[1]['id']);
        self::assertSame('Warrior', $all[1]['name']);
    }

    public function testAllExcludesInactiveArchetypes(): void
    {
        $catalog = new Archetypes($this->dataDir);

        $ids = array_column($catalog->all(), 'id');

        self::assertNotContains('hidden', $ids);
    }

    public function testForIdReturnsNullForInactiveArchetype(): void
    {
        $catalog = new Archetypes($this->dataDir);

        self::assertNull($catalog->forId('hidden'));
    }

    public function testForIdReturnsMatchingArchetypeOrNull(): void
    {
        $catalog = new Archetypes($this->dataDir);

        $warrior = $catalog->forId('warrior');
        self::assertIsArray($warrior);
        self::assertSame('warrior', $warrior['id']);
        self::assertSame('Warrior', $warrior['name']);
        self::assertSame('A seasoned fighter.', $warrior['summary']);

        self::assertNull($catalog->forId('missing'));
    }

    public function testForSourcesReturnsAll(): void
    {
        $catalog = new Archetypes($this->dataDir);

        self::assertSame($catalog->all(), $catalog->forSources(['core']));
        self::assertSame($catalog->all(), $catalog->forSources([]));
    }

    public function testAllReturnsEmptyArrayWhenDirectoryIsEmpty(): void
    {
        $emptyDir = sys_get_temp_dir() . '/swade-archetypes-empty-' . bin2hex(random_bytes(4));
        mkdir($emptyDir);
        mkdir($emptyDir . '/archetypes');

        $catalog = new Archetypes($emptyDir);

        self::assertSame([], $catalog->all());

        @rmdir($emptyDir . '/archetypes');
        @rmdir($emptyDir);
    }
}
