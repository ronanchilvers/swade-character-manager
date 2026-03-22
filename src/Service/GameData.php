<?php

declare(strict_types=1);

namespace App\Service;

class GameData
{
    private const ENTRY_KEY = 'entries';
    private const DETAIL_SEPARATOR = ' ';

    private array $hindrances;
    private array $skills;
    private array $edges;

    public function __construct(string $dataDir)
    {
        $this->hindrances = $this->loadHindrances($dataDir . '/hindrances.json');
        $this->skills     = $this->load($dataDir . '/skills.json');
        $this->edges      = $this->load($dataDir . '/edges.json');
    }

    private function loadHindrances(string $path): array
    {
        $hindrances = $this->load($path);

        foreach ($hindrances as $id => $hindrance) {
            $hindrances[$id] = $this->mergeEffectsByLevel($hindrance);
        }

        return $hindrances;
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

    private function mergeEffectsByLevel(array $hindrance): array
    {
        $groupedDetails = [];

        foreach ($hindrance['effects'] ?? [] as $effect) {
            $level = trim((string) ($effect['level'] ?? ''));
            $details = trim((string) ($effect['details'] ?? ''));

            if ($level === '' || $details === '') {
                continue;
            }

            if (!isset($groupedDetails[$level])) {
                $groupedDetails[$level] = [];
            }

            $groupedDetails[$level][] = $details;
        }

        $orderedLevels = [];
        foreach ($hindrance['levels'] ?? [] as $level) {
            $level = trim((string) $level);
            if ($level === '' || in_array($level, $orderedLevels, true)) {
                continue;
            }

            $orderedLevels[] = $level;
        }

        foreach (array_keys($groupedDetails) as $level) {
            if (in_array($level, $orderedLevels, true)) {
                continue;
            }

            $orderedLevels[] = $level;
        }

        $mergedEffects = [];
        foreach ($orderedLevels as $level) {
            if (!isset($groupedDetails[$level])) {
                continue;
            }

            $mergedEffects[] = [
                'level' => $level,
                'details' => implode(self::DETAIL_SEPARATOR, $groupedDetails[$level]),
            ];
        }

        $hindrance['effects'] = $mergedEffects;

        return $hindrance;
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
