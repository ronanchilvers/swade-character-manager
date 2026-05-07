#!/usr/bin/env php
<?php

declare(strict_types=1);

use App\Service\Data\HindranceCatalogSeeder;
use flight\database\SimplePdo;

require __DIR__ . '/../vendor/autoload.php';

$type = $argv[1] ?? null;
$source = $argv[2] ?? null;
if (!is_string($type) || '' === trim($type) || !is_string($source) || '' === trim($source)) {
    fwrite(STDERR, "Usage: php scripts/seed.php <type> <source>\n");
    exit(1);
}

$type = trim($type);
$source = trim($source);
foreach (['Type' => $type, 'Source' => $source] as $label => $value) {
    if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $value)) {
        fwrite(STDERR, "{$label} must use lowercase letters, numbers, and hyphens only.\n");
        exit(1);
    }
}

$filename = match ($type) {
    'hindrances' => sprintf('%s/../data/%s/hindrances.php', __DIR__, $source),
    default => unsupportedType($type),
};

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
    'hindrances' => new HindranceCatalogSeeder($pdo),
    default => unsupportedType($type),
};

$count = $seeder->seedFile($filename, $source);

printf(
    "Seeded %d %s from %s as source %s.\n",
    $count,
    $type,
    $filename,
    $source,
);

function unsupportedType(string $type): never
{
    fwrite(STDERR, sprintf("Unsupported seed type: %s\n", $type));
    exit(1);
}
