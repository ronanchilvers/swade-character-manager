<?php

declare(strict_types=1);

namespace App\Twig;

class AssetExtension extends \Twig\Extension\AbstractExtension
{
    private array $scripts = [];
    private array $stylesheets = [
        "/css/reset.css",
        "/css/app.css",
        "/css/form.css",
        "/css/buttons.css",
    ];

    public function getFunctions()
    {
        return [
            // Field helpers
            new \Twig\TwigFunction('add_script', [$this, 'addScript']),
            new \Twig\TwigFunction('get_scripts', [$this, 'getScripts']),

            // Route helpers
            new \Twig\TwigFunction('add_stylesheet', [$this, 'addStylesheet']),
            new \Twig\TwigFunction('get_stylesheets', [$this, 'getStylesheets']),
        ];
    }

    public function addScript(string $path): void
    {
        $this->scripts[$path] = $path;
    }

    public function getScripts(): array
    {
        return array_values($this->scripts);
    }

    public function addStylesheet(string $path): void
    {
        $this->stylesheets[$path] = $path;
    }

    public function getStylesheets(): array
    {
        return array_values($this->stylesheets);
    }
}
