<?php

declare(strict_types=1);

use App\Http\Session;
use League\OAuth2\Client\Provider\Google;
use Twig\Environment;

// Variables available for registering services:
// - $container - A flightphp/Container instance
// - $settings - The application configuration array

/* @var $settings array */
/* @var $container \flight\Container */

// Mapped methods
Flight::map('session', function () use ($container) {
    return $container->get(Session::class);
});
Flight::map('google', function () use ($container) {
    return $container->get(Google::class);
});
Flight::map('render', function ($template, array $data = []) use ($container) {
    Flight::response()
        ->write(
            $container->get(Environment::class)->render($template, $data)
        );
});
