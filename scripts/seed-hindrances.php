#!/usr/bin/env php
<?php

declare(strict_types=1);

use App\Service\Data\HindranceCatalogSeeder;
use flight\database\SimplePdo;

require __DIR__ . '/../vendor/autoload.php';

$settings = require __DIR__ . '/../config/settings.php';
$database = $settings['database'];
$source = $argv[1] ?? null;
if (!is_string($source) || '' === trim($source)) {
    fwrite(STDERR, "Usage: php scripts/seed-hindrances.php <source>\n");
    exit(1);
}

$source = trim($source);
if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $source)) {
    fwrite(STDERR, "Source must use lowercase letters, numbers, and hyphens only.\n");
    exit(1);
}

$filename = sprintf(
    '%s/../data/%s/hindrances.php',
    __DIR__,
    $source,
);

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

$count = new HindranceCatalogSeeder($pdo)->seedFile($filename, $source);

printf(
    "Seeded %d hindrances from %s as source %s.\n",
    $count,
    $filename,
    $source,
);
