<?php

declare(strict_types=1);

namespace App\Service;

abstract class Data
{
    protected string $filename;
    private array $data;

    public function __construct(string $dataDir)
    {
        $filename = rtrim($dataDir, '/') . '/' . $this->filename;
        $this->data = json_decode(
            (string) file_get_contents($filename),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
    }

    public function all(): array
    {
        return $this->data['entries'];
    }
}
