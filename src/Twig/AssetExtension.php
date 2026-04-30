<?php

declare(strict_types=1);

namespace App\Twig;

class AssetExtension extends \Twig\Extension\AbstractExtension
{
    private array $scripts = [];
    private array $stylesheets = [];

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
        return $this->processPathArray(array_values($this->scripts));
    }

    public function addStylesheet(string $path): void
    {
        $this->stylesheets[$path] = $path;
    }

    public function getStylesheets(): array
    {
        return $this->processPathArray(array_values($this->stylesheets));
    }

    private function processPathArray($paths): array
    {
        $deployInfo = realpath(__DIR__ . '/../../.deploy_info');
        if ($deployInfo && file_exists($deployInfo)) {
            $deployData = json_decode(file_get_contents($deployInfo), true);
            if ($deployData && isset($deployData['sha'])) {
                $sha = $deployData['sha'];
                $paths = array_map(function ($path) use ($sha) {
                    return $path . '?v=' . $sha;
                }, $paths);
            }
        }

        return $paths;
    }
}
