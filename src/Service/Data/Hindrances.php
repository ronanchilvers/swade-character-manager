<?php

declare(strict_types=1);

namespace App\Service\Data;

use App\Service\Data;

class Hindrances extends Data
{
    public function forBuilder(): array
    {
        return array_map(
            fn (array $hindrance): array => $hindrance + [
                'effects_by_level' => $this->groupEffectsByLevel(
                    $hindrance['effects'] ?? []
                ),
            ],
            $this->all()
        );
    }

    private function groupEffectsByLevel(array $effects): array
    {
        $grouped = [];

        foreach ($effects as $effect) {
            $level = $effect['level'] ?? null;
            $details = $effect['details'] ?? null;

            if (!is_string($level) || !is_string($details) || '' === $details) {
                continue;
            }

            $grouped[$level][] = $details;
        }

        return $grouped;
    }
}
