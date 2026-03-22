<?php

declare(strict_types=1);

namespace App\Service;

class GameData
{
    private const ENTRY_KEY = 'entries';

    private array $hindrances;
    private array $skills;
    private array $edges;

    public function __construct(string $dataDir)
    {
        $this->hindrances = $this->load($dataDir . '/hindrances.json');
        $this->skills     = $this->load($dataDir . '/skills.json');
        $this->edges      = $this->load($dataDir . '/edges.json');
    }

    private function load(string $path): array
    {
        $decoded = json_decode(
            (string) file_get_contents($path),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
        $indexed = [];
        foreach ($decoded[self::ENTRY_KEY] as $item) {
            $indexed[$item['id']] = $item;
        }
        return $indexed;
    }

    public function hindrance(string $id): ?array
    {
        return $this->hindrances[$id] ?? null;
    }

    public function hindranceSupportsLevel(string $id, string $level): bool
    {
        $hindrance = $this->hindrance($id);
        if ($hindrance === null) {
            return false;
        }

        return in_array($level, $hindrance['levels'] ?? [], true);
    }

    public function skill(string $id): ?array
    {
        return $this->skills[$id] ?? null;
    }

    public function edge(string $id): ?array
    {
        return $this->edges[$id] ?? null;
    }

    /** All hindrances as a flat array, optionally filtered by level availability. */
    public function allHindrances(?string $level = null): array
    {
        if ($level === null) {
            return array_values($this->hindrances);
        }
        return array_values(array_filter(
            $this->hindrances,
            fn($h) => in_array($level, $h['levels'], true)
        ));
    }

    public function allSkills(): array
    {
        return array_values($this->skills);
    }

    /** All edges, optionally filtered by category. */
    public function allEdges(?string $category = null): array
    {
        if ($category === null) {
            return array_values($this->edges);
        }
        return array_values(array_filter(
            $this->edges,
            fn($e) => $e['category'] === $category
        ));
    }
}
