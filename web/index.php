<?php

declare(strict_types=1);

use flight\Container;
use flight\net\Request;
use App\Http\CorsPolicy;
use App\Controller\Home\Index;

require '../vendor/autoload.php';
$settings = include '../config/settings.php';

// Configure the container
$container = new Container();
include '../config/services.php';
Flight::registerContainerHandler([$container, 'get']);

$corsConfig = is_array($settings['cors'] ?? null) ? $settings['cors'] : [];
if (CorsPolicy::isEnabled($corsConfig)) {
    Flight::before('start', function () use ($corsConfig): void {
        $origin = Request::getHeader('Origin', '');
        $corsHeaders = CorsPolicy::resolve($corsConfig, $origin);
        foreach ($corsHeaders as $name => $value) {
            Flight::response()->header($name, $value);
        }
    });

    Flight::route('OPTIONS *', function (): void {
        Flight::response()->status(204)->send();
    });
}

// Configure routing
Flight::route('GET /', Index::class);

Flight::start();
