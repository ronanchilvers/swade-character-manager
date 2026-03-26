<?php

declare(strict_types=1);

use App\Http\Response;
use Tracy\Debugger;
use flight\Container;
use flight\debug\tracy\TracyExtensionLoader;

if (PHP_SAPI == 'cli-server') {
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}

require '../vendor/autoload.php';
$settings = include '../config/settings.php';

// Debugging support
Debugger::enable();
Debugger::$logDirectory = __DIR__ . "/../tmp/logs/";
Debugger::$strictMode = true;
if (Debugger::$showBar) {
    Flight::app()->set('flight.content_length', false);
    new TracyExtensionLoader(Flight::app());
}

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
