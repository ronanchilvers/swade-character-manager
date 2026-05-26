<?php

declare(strict_types=1);

namespace Tests\Schema;

use PHPUnit\Framework\TestCase;

class SchemaConsistencyTest extends TestCase
{
    private const SCHEMA_DIR = __DIR__ . '/../../schema';

    public function testBaseSchemaFilesExistInExpectedOrder(): void
    {
        self::assertSame(
            [
                '010_users.sql',
                '020_characters.sql',
                '030_hindrances.sql',
                '040_skills.sql',
                '050_edges.sql',
                '060_gear.sql',
                '070_weapons.sql',
            ],
            array_map('basename', glob(self::SCHEMA_DIR . '/*.sql')),
        );
    }

    public function testMigrationFilesAreTimestampPrefixed(): void
    {
        foreach (glob(self::SCHEMA_DIR . '/migrations/*.sql') as $file) {
            self::assertMatchesRegularExpression('/^\d{14}_[a-z0-9_]+\.sql$/', basename($file));
        }
    }

    public function testCatalogMigrationsDefineExpectedTables(): void
    {
        self::assertMatchesRegularExpression(
            '/CREATE TABLE IF NOT EXISTS `?hindrance_catalog`?/',
            $this->readMigration('create_hindrance_catalog'),
        );
        self::assertMatchesRegularExpression(
            '/CREATE TABLE IF NOT EXISTS `?skill_catalog`?/',
            $this->readMigration('create_skill_catalog'),
        );
        self::assertMatchesRegularExpression(
            '/CREATE TABLE IF NOT EXISTS `?edge_catalog`?/',
            $this->readMigration('create_edge_catalog'),
        );
        self::assertMatchesRegularExpression(
            '/CREATE TABLE IF NOT EXISTS `?catalog_sources`?/',
            $this->readMigration('create_catalog_sources'),
        );
        self::assertStringContainsString(
            "'core', 'Core Rules (Always Enabled)'",
            $this->readMigration('create_catalog_sources'),
        );
    }

    public function testSelectionTablesUseStableCatalogKeyColumns(): void
    {
        self::assertStringContainsString('hindrance_key', file_get_contents(self::SCHEMA_DIR . '/030_hindrances.sql'));
        self::assertStringContainsString('skill_key', file_get_contents(self::SCHEMA_DIR . '/040_skills.sql'));
        self::assertStringContainsString('edge_key', file_get_contents(self::SCHEMA_DIR . '/050_edges.sql'));
    }

    public function testSharingMigrationAddsPublicShareToken(): void
    {
        $migration = $this->readMigration('add_character_share_token');

        self::assertStringContainsString('character_share_token', $migration);
        self::assertStringContainsString('uq_character_share_token', $migration);
    }

    public function testGearAndWeaponTablesReferenceCharacters(): void
    {
        self::assertStringContainsString('gear_character_id', file_get_contents(self::SCHEMA_DIR . '/060_gear.sql'));
        self::assertStringContainsString('weapon_character_id', file_get_contents(self::SCHEMA_DIR . '/070_weapons.sql'));
    }

    public function testCampaignMigrationsDefineCampaignTablesAndCharacterAssignment(): void
    {
        self::assertMatchesRegularExpression('/CREATE TABLE IF NOT EXISTS `?campaigns`?/', $this->readMigration('create_campaigns'));
        self::assertMatchesRegularExpression('/CREATE TABLE IF NOT EXISTS `?campaign_members`?/', $this->readMigration('create_campaign_members'));
        self::assertStringContainsString('character_campaign', $this->readMigration('add_campaign_to_characters'));
    }

    private function readMigration(string $name): string
    {
        $matches = glob(self::SCHEMA_DIR . "/migrations/*_{$name}.sql");
        self::assertCount(1, $matches, $name);

        return file_get_contents($matches[0]);
    }
}
