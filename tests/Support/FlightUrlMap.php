<?php

declare(strict_types=1);

namespace Tests\Support;

use RuntimeException;

class FlightUrlMap
{
    public function __construct(
        private array $routes = [],
    ) {
    }

    public function add(string $alias, string $template): static
    {
        $this->routes[$alias] = $template;

        return $this;
    }

    public function url(string $alias, array $params = []): string
    {
        if (!isset($this->routes[$alias])) {
            throw new RuntimeException(sprintf('No test URL mapped for route alias %s', $alias));
        }

        $url = $this->routes[$alias];
        foreach ($params as $key => $value) {
            $url = str_replace(sprintf('{%s}', $key), (string) $value, $url);
        }

        return $url;
    }
}
