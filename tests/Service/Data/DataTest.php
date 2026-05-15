<?php

declare(strict_types=1);

namespace App\Service\Data {
    class TestCatalog extends \App\Service\Data
    {
        protected function entryFromRow(mixed $row): array
        {
            return [];
        }
    }
}

namespace Tests\Service\Data {
    use App\Service\Data\TestCatalog;
    use PHPUnit\Framework\TestCase;

    class DataTest extends TestCase
    {
        private string $dataDir;

        protected function setUp(): void
        {
            parent::setUp();

            $this->dataDir = sys_get_temp_dir() . '/swade-data-test-' . bin2hex(random_bytes(4));
            mkdir($this->dataDir);
            file_put_contents($this->dataDir . '/testcatalog.php', <<<'PHP'
<?php

return [
    'entries' => [
        ['id' => 'alpha', 'name' => 'Alpha'],
        ['id' => 'beta', 'name' => 'Beta'],
    ],
];
PHP);
        }

        protected function tearDown(): void
        {
            @unlink($this->dataDir . '/testcatalog.php');
            @rmdir($this->dataDir);

            parent::tearDown();
        }

        public function testAllReturnsEntriesFromExpectedPhpFile(): void
        {
            $catalog = new TestCatalog($this->dataDir);

            self::assertSame(
                [
                    ['id' => 'alpha', 'name' => 'Alpha'],
                    ['id' => 'beta', 'name' => 'Beta'],
                ],
                $catalog->all(),
            );
        }

        public function testForIdReturnsMatchingEntryOrNull(): void
        {
            $catalog = new TestCatalog($this->dataDir);

            self::assertSame(['id' => 'beta', 'name' => 'Beta'], $catalog->forId('beta'));
            self::assertNull($catalog->forId('missing'));
        }
    }
}
