<?php

declare(strict_types=1);

use App\Controller\Character;
use App\Controller\Home;
use App\Http\Response;
use App\Http\Session;
use flight\Container;
use Twig\Environment;

if (PHP_SAPI == 'cli-server') {
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}

require '../vendor/autoload.php';
$settings = include '../config/settings.php';

// Configure the container
$container = new Container();
include '../config/events.php';
include '../config/services.php';

// Overrides
Flight::register('response', Response::class);

// Mapped methods
Flight::map('session', function () use ($container) {
    return $container->get(Session::class);
});
Flight::map('render', function ($template, array $data = []) use ($container) {
    Flight::response()
        ->write(
            $container->get(Environment::class)->render($template, $data)
        );
});
Flight::registerContainerHandler([$container, 'get']);

// Routing
Flight::route('GET /', [Home::class, 'index']);

// Auth
// Flight::route('GET /', [Auth::class, 'index']);

// Characters
Flight::route('GET|POST /create', [Character::class, 'create']);
Flight::route('GET|POST /hindrances/@hash:[a-z0-9]{32}', [Character::class, 'hindrances']);

// Start the framework
Flight::start();
