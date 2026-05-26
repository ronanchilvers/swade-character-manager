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

    public function forSources(array $sources): array
    {
        $sources = array_values(
            array_unique(
                array_filter(
                    array_map('strval', $sources),
                    static fn (string $source): bool => '' !== $source,
                )
            )
        );

        if (empty($sources)) {
            return $this->all();
        }

        $allowed = array_flip($sources);

        return array_values(
            array_filter(
                $this->all(),
                static fn (array $entry): bool => isset($allowed[(string) ($entry['source'] ?? 'core')]),
            )
        );
    }

    abstract protected function entryFromRow(mixed $row): array;
}
