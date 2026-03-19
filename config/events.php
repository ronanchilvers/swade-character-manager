<?php

declare(strict_types=1);

use App\Http\Session;
use flight\net\Request;

// Variables available for registering services:
// - $container - A flightphp/Container instance
// - $settings - The application configuration array

/* @var $settings array */
/* @var $container \flight\Container */

Flight::onEvent(
    'flight.request.received',
    function (Request $request) use ($container) {
        // echo 'flight.request.received<br>';
        $container
            ->get(Session::class)
            ->initialise($request);
    }
);
Flight::onEvent(
    'flight.response.headers.before',
    function () use ($container) {
        // echo 'flight.response.headers.before<br>';
        $container
            ->get(Session::class)
            ->shutdown();
    }
);
