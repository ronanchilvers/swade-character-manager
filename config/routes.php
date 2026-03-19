<?php

declare(strict_types=1);

use App\Controller\Auth;
use App\Controller\Character;
use App\Controller\Home;
use App\Middleware\Auth as MiddlewareAuth;

// Variables available for registering services:
// - $container - A flightphp/Container instance
// - $settings - The application configuration array

/* @var $settings array */
/* @var $container \flight\Container */

Flight::route('GET /', [Home::class, 'index'])
    ->setAlias('home_page')
    ->addMiddleware(MiddlewareAuth::class);

// Authentication
Flight::group('/auth', function () {
    Flight::route('GET /', [Auth::class, 'index'])->setAlias('auth_login');
    Flight::route('GET /return', [Auth::class, 'return']);
    Flight::route('GET /logout', [Auth::class, 'logout'])->setAlias('auth_logout');
});

// Characters
Flight::group('/characters', function () {
    Flight::route('GET|POST /create', [Character::class, 'create'])
        ->setAlias('characters_create');
    Flight::route('GET|POST /hindrances/@hash:[a-z0-9]{32}', [Character::class, 'hindrances'])
        ->setAlias('characters_hindrances');
}, [ MiddlewareAuth::class ]);
