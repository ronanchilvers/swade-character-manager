<?php

declare(strict_types=1);

namespace App\Twig;

use Flight;

class RoutingExtension extends \Twig\Extension\AbstractExtension
{
    public function getFunctions()
    {
        return [
            // Route helpers
            new \Twig\TwigFunction('get_url', [$this, 'getUrl']),
        ];
    }

    public function getUrl(string $routeName, array $params = [])
    {
        return Flight::getUrl(
            $routeName,
            $params
        );
    }
}
