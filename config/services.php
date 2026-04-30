<?php

declare(strict_types=1);

// Variables available for registering services:
// - $container - A flightphp/Container instance
// - $settings - The application configuration array

use App\Character\Sheet;
use App\Entity\Factory\Character;
use App\Entity\Factory\Edge;
use App\Entity\Factory\Gear;
use App\Entity\Factory\Hindrance;
use App\Entity\Factory\Skill;
use App\Entity\Factory\User;
use App\Entity\Factory\Weapon;
use App\Http\Session;
use App\Http\Session\CookieStorage;
use App\Http\Session\StorageInterface;
use App\Service\Data\Edges;
use App\Service\Data\Hindrances;
use App\Service\Data\Skills;
use App\Service\Data\Manager;
use App\Twig\AssetExtension;
use App\Twig\FieldExtension;
use App\Twig\RoutingExtension;
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
        $twig->addExtension(new RoutingExtension());
        $twig->addExtension(new FieldExtension());
        $twig->addExtension(new AssetExtension());
        if ($settings['twig']['debug']) {
            $twig->addExtension(new \Twig\Extension\DebugExtension());
        }
        $twig->addGlobal('session', $container->get(Session::class));
        $twig->addGlobal('request', Flight::request());
        $twig->addGlobal('site', $settings['site']);

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
    Hindrance::class,
    Skill::class,
    Edge::class,
    Gear::class,
    Weapon::class,
];
foreach ($classes as $class) {
    $container->singleton($class, $class);
}

$container->singleton(
    Manager::class,
    function () {
        $manager = new Manager(__DIR__ . '/../data');
        $manager->addType(Edges::class);
        $manager->addType(Hindrances::class);
        $manager->addType(Skills::class);

        return $manager;
    }
);

$container->singleton(Sheet::class, Sheet::class);
