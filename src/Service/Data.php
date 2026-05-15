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

    public function forId(string $id): ?array
    {
        foreach ($this->all() as $datum) {
            if ($datum['id'] == $id) {
                return $datum;
            }
        }

        return null;
    }

    public function all(): array
    {
        return $this->data['entries'];
    }

    abstract protected function entryFromRow(mixed $row): array;
}
