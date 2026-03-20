<?php

declare(strict_types=1);

namespace App\Twig;

use Flight;

class FlightExtension extends \Twig\Extension\AbstractExtension
{
    public function getFunctions()
    {
        return [
            // Field helpers
            new \Twig\TwigFunction('field_has_error', [$this, 'fieldHasError']),

            // Route helpers
            new \Twig\TwigFunction('get_url', [$this, 'getUrl']),
        ];
    }

    public function fieldHasError(string $field, ?array $errors): bool
    {
        return isset($errors[$field]);
    }

    public function getUrl(string $routeName, array $params = [])
    {
        return Flight::getUrl(
            $routeName,
            $params
        );
    }
}
