<?php

declare(strict_types=1);

// Variables available for registering services:
// - $container - A flightphp/Container instance
// - $settings - The application configuration array

use App\Entity\Factory\Character;
use App\Entity\Factory\User;
use App\Http\Session;
use App\Http\Session\CookieStorage;
use App\Http\Session\StorageInterface;
use flight\database\SimplePdo;
use League\OAuth2\Client\Provider\Google;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/* @var $settings array */
/* @var $container \flight\Container */

// The SimplePdo database connection
$container->singleton(
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

// Session
$container->singleton(
    StorageInterface::class,
    function () use ($settings) {
        return new CookieStorage($settings["session"]);
    }
);
$container->singleton(
    Session::class,
    Session::class
);

// Twig
$container->singleton(
    Environment::class,
    function () use ($container, $settings) {
        $loader = new FilesystemLoader(__DIR__ . "/../views/");

        $twig = new Environment(
            $loader,
            $settings['twig']
        );
        $twig->addGlobal('session', $container->get(Session::class));

        return $twig;
    }
);

// Authentication
$container->singleton(
    Google::class,
    function () use ($settings) {
        return new Google($settings['auth']['google']);
    }
);

// Database factories
$classes = [
    Character::class,
    User::class,
];
foreach ($classes as $class) {
    $container->singleton($class, $class);
}
