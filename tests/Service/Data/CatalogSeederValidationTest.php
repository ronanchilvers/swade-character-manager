<?php

declare(strict_types=1);

namespace Tests\Service\Data;

use App\Service\Data\EdgeCatalogSeeder;
use App\Service\Data\HindranceCatalogSeeder;
use App\Service\Data\SkillCatalogSeeder;
use flight\database\SimplePdo;
use PDOStatement;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class CatalogSeederValidationTest extends TestCase
{
    #[DataProvider('seeders')]
    public function testSeedFileRejectsMissingFiles(string $class, string $type): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Unable to find {$type} source file:");

        $this->seeder($class, $this->createStub(SimplePdo::class))->seedFile('/not/a/file.php');
    }

    #[DataProvider('seeders')]
    public function testSeedFileRejectsFilesWithoutEntriesArray(string $class, string $type): void
    {
        $filename = $this->temporaryPhpFile("<?php\nreturn ['not_entries' => []];\n");

        try {
            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage(ucfirst($type) . ' source file must return an array with an entries array');

            $this->seeder($class, $this->createStub(SimplePdo::class))->seedFile($filename);
        } finally {
            @unlink($filename);
        }
    }

    #[DataProvider('seeders')]
    public function testSeedEntriesRejectsBlankSourceBeforeTransaction(string $class, string $type): void
    {
        $pdo = $this->createMock(SimplePdo::class);
        $pdo->expects(self::never())->method('transaction');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('source cannot be blank');

        $this->seeder($class, $pdo)->seedEntries([$this->validEntry($class)], '   ');
    }

    #[DataProvider('seeders')]
    public function testSeedEntriesRejectsNonArrayEntries(string $class, string $type): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Each {$type} entry must be an array");

        $this->seeder($class, $this->createStub(SimplePdo::class))->seedEntries(['bad']);
    }

    #[DataProvider('seeders')]
    public function testSeedEntriesWritesAllRowsInOneTransaction(string $class, string $type): void
    {
        $pdo = $this->createMock(SimplePdo::class);
        $statement = $this->createStub(PDOStatement::class);
        $runCount = 0;

        $pdo->expects(self::once())
            ->method('transaction')
            ->willReturnCallback(function (callable $callback) use ($pdo): void {
                $callback($pdo);
            });
        $pdo->expects(self::exactly(2))
            ->method('runQuery')
            ->willReturnCallback(function () use (&$runCount, $statement): PDOStatement {
                ++$runCount;

                return $statement;
            });

        $count = $this->seeder($class, $pdo)->seedEntries([
            $this->validEntry($class, ['id' => 'first', 'name' => 'First']),
            $this->validEntry($class, ['id' => 'second', 'name' => 'Second']),
        ]);

        self::assertSame(2, $count);
        self::assertSame(2, $runCount);
    }

    #[DataProvider('seeders')]
    public function testSeedEntriesWrapsJsonEncodingFailures(string $class, string $type): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to encode');

        $this->seeder($class, $this->createStub(SimplePdo::class))->seedEntries([
            $this->validEntry($class, ['notes' => [NAN]]),
        ]);
    }

    public function testSkillSeederRejectsMissingRequiredFieldsAndInvalidArcaneBackground(): void
    {
        $seeder = new SkillCatalogSeeder($this->createStub(SimplePdo::class));

        foreach (['id', 'name', 'linked_attribute'] as $field) {
            try {
                $seeder->seedEntries([$this->validEntry(SkillCatalogSeeder::class, [$field => ''])]);
                self::fail("Expected {$field} validation failure");
            } catch (RuntimeException $ex) {
                self::assertStringContainsString("non-blank {$field}", $ex->getMessage());
            }
        }

        try {
            $seeder->seedEntries([$this->validEntry(SkillCatalogSeeder::class, ['core_skill' => 1])]);
            self::fail('Expected core_skill validation failure');
        } catch (RuntimeException $ex) {
            self::assertSame('Skill entry must include a boolean core_skill', $ex->getMessage());
        }

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Skill arcane_background must be a string or null');

        $seeder->seedEntries([$this->validEntry(SkillCatalogSeeder::class, ['arcane_background' => 7])]);
    }

    public function testEdgeSeederRejectsMissingRequiredFields(): void
    {
        $seeder = new EdgeCatalogSeeder($this->createStub(SimplePdo::class));

        foreach (['id', 'name', 'category'] as $field) {
            try {
                $seeder->seedEntries([$this->validEntry(EdgeCatalogSeeder::class, [$field => ''])]);
                self::fail("Expected {$field} validation failure");
            } catch (RuntimeException $ex) {
                self::assertStringContainsString("non-blank {$field}", $ex->getMessage());
            }
        }

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Edge entry must include a boolean repeatable');

        $seeder->seedEntries([$this->validEntry(EdgeCatalogSeeder::class, ['repeatable' => 1])]);
    }

    public function testHindranceSeederRejectsMissingRequiredFieldsAndDefaultsOptionalArrays(): void
    {
        $seeder = new HindranceCatalogSeeder($this->createStub(SimplePdo::class));

        foreach (['id', 'name'] as $field) {
            try {
                $seeder->seedEntries([$this->validEntry(HindranceCatalogSeeder::class, [$field => ''])]);
                self::fail("Expected {$field} validation failure");
            } catch (RuntimeException $ex) {
                self::assertStringContainsString("non-blank {$field}", $ex->getMessage());
            }
        }

        $pdo = $this->createMock(SimplePdo::class);
        $capturedParams = [];
        $pdo->expects(self::once())
            ->method('transaction')
            ->willReturnCallback(function (callable $callback) use ($pdo): void {
                $callback($pdo);
            });
        $pdo->expects(self::once())
            ->method('runQuery')
            ->willReturnCallback(function (string $sql, array $params) use (&$capturedParams): PDOStatement {
                $capturedParams = $params;

                return $this->createStub(PDOStatement::class);
            });

        (new HindranceCatalogSeeder($pdo))->seedEntries([
            ['id' => 'quirk', 'name' => 'Quirk'],
        ]);

        self::assertSame('[]', $capturedParams[4]);
        self::assertSame('[]', $capturedParams[5]);
        self::assertSame('[]', $capturedParams[6]);
        self::assertSame('[]', $capturedParams[7]);
        self::assertSame('[]', $capturedParams[8]);
    }

    public static function seeders(): array
    {
        return [
            'hindrance' => [HindranceCatalogSeeder::class, 'hindrance'],
            'skill' => [SkillCatalogSeeder::class, 'skill'],
            'edge' => [EdgeCatalogSeeder::class, 'edge'],
        ];
    }

    private function seeder(string $class, SimplePdo $pdo): object
    {
        return new $class($pdo);
    }

    private function validEntry(string $class, array $overrides = []): array
    {
        $entry = [
            'id' => 'catalog_key',
            'name' => 'Catalog Name',
            'summary' => '',
            'requirements' => [],
            'effects' => [],
            'notes' => [],
            'source_pages' => [],
        ];

        if (SkillCatalogSeeder::class === $class) {
            $entry += [
                'linked_attribute' => 'smarts',
                'core_skill' => false,
                'arcane_background' => null,
            ];
        }

        if (EdgeCatalogSeeder::class === $class) {
            $entry += [
                'category' => 'background',
                'repeatable' => false,
            ];
        }

        if (HindranceCatalogSeeder::class === $class) {
            $entry += ['levels' => []];
        }

        return array_replace($entry, $overrides);
    }

    private function temporaryPhpFile(string $contents): string
    {
        $filename = tempnam(sys_get_temp_dir(), 'swade-seed-file-');
        file_put_contents($filename, $contents);

        return $filename;
    }
}
