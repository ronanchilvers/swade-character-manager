#!/usr/bin/env php
<?php

declare(strict_types=1);

use App\Service\Data\EdgeCatalogSeeder;
use App\Service\Data\HindranceCatalogSeeder;
use App\Service\Data\SeedCommand;
use App\Service\Data\SkillCatalogSeeder;
use flight\database\SimplePdo;

require __DIR__ . '/../vendor/autoload.php';

try {
    $command = (new SeedCommand())->resolve($argv[1] ?? null, $argv[2] ?? null, dirname(__DIR__));
} catch (RuntimeException $ex) {
    fwrite(STDERR, $ex->getMessage() . "\n");
    exit(1);
}

$type = $command['type'];
$source = $command['source'];
$filename = $command['filename'];

$settings = require __DIR__ . '/../config/settings.php';
$database = $settings['database'];
$pdo = new SimplePdo(
    sprintf(
        "%s:host=%s;dbname=%s;charset=utf8mb4",
        $database["adapter"],
        $database["host"],
        $database["name"],
    ),
    $database["username"],
    $database["password"],
);

$seeder = match ($type) {
    'edges' => new EdgeCatalogSeeder($pdo),
    'hindrances' => new HindranceCatalogSeeder($pdo),
    'skills' => new SkillCatalogSeeder($pdo),
};

$count = $seeder->seedFile($filename, $source);

printf(
    "Seeded %d %s from %s as source %s.\n",
    $count,
    $type,
    $filename,
    $source,
);
