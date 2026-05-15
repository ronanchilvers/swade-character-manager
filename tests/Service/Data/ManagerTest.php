<?php

declare(strict_types=1);

namespace App\Service\Data {
    class NonDatabaseCatalog extends \App\Service\Data
    {
        protected function entryFromRow(mixed $row): array
        {
            return [];
        }
    }
}

namespace Tests\Service\Data {
    use App\Service\Data\Edges;
    use App\Service\Data\Manager;
    use App\Service\Data\NonDatabaseCatalog;
    use flight\database\SimplePdo;
    use PHPUnit\Framework\TestCase;
    use RuntimeException;

    class ManagerTest extends TestCase
    {
        private string $dataDir;

        protected function setUp(): void
        {
            parent::setUp();

            $this->dataDir = sys_get_temp_dir() . '/swade-manager-test-' . bin2hex(random_bytes(4));
            mkdir($this->dataDir);
            mkdir($this->dataDir . '/core');
            file_put_contents($this->dataDir . '/nondatabasecatalog.php', <<<'PHP'
<?php

return ['entries' => [['id' => 'file_entry']]];
PHP);
            file_put_contents($this->dataDir . '/core/edges.php', <<<'PHP'
<?php

return ['entries' => [['id' => 'file_edge']]];
PHP);
        }

        protected function tearDown(): void
        {
            @unlink($this->dataDir . '/nondatabasecatalog.php');
            @unlink($this->dataDir . '/core/edges.php');
            @rmdir($this->dataDir . '/core');
            @rmdir($this->dataDir);

            parent::tearDown();
        }

        public function testAddTypeReturnsSelfAndMemoizesLoaderInstances(): void
        {
            $manager = new Manager($this->dataDir);

            self::assertSame($manager, $manager->addType(NonDatabaseCatalog::class));
            self::assertSame(
                $manager->getType(NonDatabaseCatalog::class),
                $manager->getType(NonDatabaseCatalog::class),
            );
        }

        public function testGetTypeRejectsUnregisteredClasses(): void
        {
            $manager = new Manager($this->dataDir);

            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage('Unregistered data class ' . NonDatabaseCatalog::class);

            $manager->getType(NonDatabaseCatalog::class);
        }

        public function testGetTypePassesPdoOnlyToDatabaseAwareLoaders(): void
        {
            $pdo = $this->createMock(SimplePdo::class);
            $pdo->expects(self::once())
                ->method('fetchAll')
                ->with('SELECT * FROM edge_catalog ORDER BY edge_catalog_category ASC')
                ->willReturn([
                    [
                        'edge_catalog_key' => 'db_edge',
                        'edge_catalog_source' => 'core',
                        'edge_catalog_name' => 'Database Edge',
                        'edge_catalog_category' => 'background',
                        'edge_catalog_summary' => '',
                        'edge_catalog_repeatable' => '0',
                        'edge_catalog_requirements' => '[]',
                        'edge_catalog_effects' => '[]',
                        'edge_catalog_notes' => '[]',
                        'edge_catalog_source_pages' => '[]',
                    ],
                ]);

            $manager = new Manager($this->dataDir, $pdo);
            $manager
                ->addType(Edges::class)
                ->addType(NonDatabaseCatalog::class);

            self::assertSame('db_edge', $manager->getType(Edges::class)->all()[0]['id']);
            self::assertSame('file_entry', $manager->getType(NonDatabaseCatalog::class)->all()[0]['id']);
        }
    }
}
