<?php

declare(strict_types=1);

use App\Controller\Auth;
use App\Controller\Character;
use App\Controller\Home;
use App\Http\Response;
use App\Middleware\Auth as MiddlewareAuth;
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
Flight::route('GET /', [Home::class, 'index'])
    ->addMiddleware(MiddlewareAuth::class);

// Authentication
Flight::group('/auth', function () {
    Flight::route('GET /', [Auth::class, 'index']);
    Flight::route('GET /return', [Auth::class, 'return']);
    Flight::route('GET /logout', [Auth::class, 'logout']);
});

// Characters
Flight::group('/characters', function () {
    Flight::route('GET|POST /create', [Character::class, 'create']);
    Flight::route('GET|POST /hindrances/@hash:[a-z0-9]{32}', [Character::class, 'hindrances']);
}, [ MiddlewareAuth::class ]);

// $user = $container->get(\App\Entity\Factory\User::class)->one(
//     "user_email = ?",
//     ['ronan@thelittledot.com']
// );
// var_dump(__METHOD__, $user);
// exit;

// Start the framework
Flight::start();
