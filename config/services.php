<?php

declare(strict_types=1);

// Variables available for registering services:
// - $container - A flightphp/Container instance
// - $settings - The application configuration array

use App\Entity\Factory\Character;
use flight\database\SimplePdo;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/* @param $settings array */
/* @param $container \flight\Container */

// The SimplePdo database connection
$container->set(
    SimplePdo::class,
    function () use ($settings) {
        return new SimplePdo(
            sprintf(
                "%s:host=%s;dbname=%s;charset=utf8mb4",
                $settings["database"]["adapter"],
                $settings["database"]["host"],
                $settings["database"]["name"]
            ),
            $settings["database"]["username"],
            $settings["database"]["password"]
        );
    }
);

// Twig
$container->set(
    Environment::class,
    function () use ($settings) {
        $loader = new FilesystemLoader(__DIR__ . "/../views/");

        return new Environment(
            $loader,
            $settings['twig']
        );
    }
);

// Database factories
$container->set(
    Character::class,
    Character::class
);
