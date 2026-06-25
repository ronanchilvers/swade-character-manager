<?php

declare(strict_types=1);

namespace App\Service\Data;

use App\Service\Data;

class Archetypes extends Data
{
    private array $entries = [];

    public function __construct(string $dataDir)
    {
        $pattern = rtrim($dataDir, '/') . '/archetypes/*.json';
        foreach (glob($pattern) as $file) {
            $id = basename($file, '.json');
            $decoded = json_decode(file_get_contents($file), true);
            if (is_array($decoded)) {
                $this->entries[$id] = array_merge(['id' => $id], $decoded);
            }
        }
        ksort($this->entries);
    }

    public function all(): array
    {
        return array_values($this->entries);
    }

    public function forId(string $id): ?array
    {
        return $this->entries[$id] ?? null;
    }

    public function forSources(array $sources): array
    {
        return $this->all();
    }

    protected function entryFromRow(mixed $row): array
    {
        return [];
    }
}
