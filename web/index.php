<?php

declare(strict_types=1);

use App\Http\Response;
use flight\Container;

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
