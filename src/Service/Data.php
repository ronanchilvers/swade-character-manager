<?php

declare(strict_types=1);

namespace App\Service;

abstract class Data
{
    protected string $filename;
    private array $data;

    public function __construct(string $dataDir)
    {
        $filename = rtrim($dataDir, '/') .
            '/' .
            strtolower(str_replace(__NAMESPACE__ . '\\Data\\', '', static::class)) .
            '.php';
        $this->data = require $filename;
    }

    public function all(): array
    {
        return $this->data['entries'];
    }
}
