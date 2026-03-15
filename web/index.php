<?php

declare(strict_types=1);

use Twig\Environment;
use flight\Container;
use flight\net\Request;
use App\Http\CorsPolicy;
use App\Controller\Home;

require '../vendor/autoload.php';
$settings = include '../config/settings.php';

// Configure the container
$container = new Container();
include '../config/services.php';

Flight::map('render', function ($template, array $data = []) use ($container) {
    Flight::response()
        ->write(
            $container->get(Environment::class)->render($template, $data)
        );
});
Flight::registerContainerHandler([$container, 'get']);

// Configure routing
Flight::route('GET /', [Home::class, 'index']);

Flight::start();
