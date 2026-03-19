<?php

declare(strict_types=1);

use App\Http\Response;
use flight\Container;

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
include '../config/maps.php';

// Setup
Flight::registerContainerHandler([$container, 'get']);
Flight::register('response', Response::class);

// Routing
include '../config/routes.php';

// Start the framework
Flight::start();
