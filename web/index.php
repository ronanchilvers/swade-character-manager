<?php

declare(strict_types=1);

use App\Controller\Character;
use App\Controller\Home;
use flight\Container;
use Twig\Environment;

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

// Characters
Flight::route('GET /create', [Character::class, 'create']);
Flight::route('POST /create', [Character::class, 'createPost']);

Flight::start();
