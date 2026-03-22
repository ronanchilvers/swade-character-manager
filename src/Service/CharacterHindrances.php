<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity;
use App\Entity\Factory\Hindrance;
use flight\database\SimplePdo;

class CharacterHindrances
{
    private const MAX_HINDRANCE_POINTS = 4;

    public function __construct(
        private SimplePdo $pdo,
        private GameData $gameData,
        private Hindrance $hindrances,
    ) {
    }

    public function processSubmission(int $characterId, array $submitted): array
    {
        $selected = $this->normalizeSelectionMap($submitted);
        $errors = $this->validateSelectionMap($selected);

        if (!empty($errors)) {
            return [
                'errors' => $errors,
                'selected' => $selected,
            ];
        }

        try {
            $this->replaceForCharacter($characterId, $selected);
        } catch (\Throwable $exception) {
            return [
                'errors' => ['Unable to save hindrances right now.'],
                'selected' => $selected,
            ];
        }

        return [
            'errors' => [],
            'selected' => $selected,
        ];
    }

    public function selectedForCharacter(int $characterId): array
    {
        $selected = [];

        foreach ($this->hindrances->forCharacter($characterId) as $row) {
            $selected[$row->key] = $row->level;
        }

        return $selected;
    }

    public function maxPoints(): int
    {
        return self::MAX_HINDRANCE_POINTS;
    }

    public function remainingPoints(array $selected): int
    {
        return max(0, self::MAX_HINDRANCE_POINTS - $this->pointsUsed($selected));
    }

    public function selectedPointsForCharacter(int $characterId): int
    {
        return $this->pointsUsed($this->selectedForCharacter($characterId));
    }

    private function normalizeSelectionMap(array $submitted): array
    {
        $selected = [];

        foreach ($submitted as $key => $level) {
            $key = trim((string) $key);
            $level = trim((string) $level);

            if ($key === '' || $level === '') {
                continue;
            }

            $selected[$key] = $level;
        }

        return $selected;
    }

    private function validateSelectionMap(array $selected): array
    {
        $errors = [];
        $points = 0;

        foreach ($selected as $key => $level) {
            $hindrance = $this->gameData->hindrance($key);
            if ($hindrance === null) {
                $errors[] = sprintf('Unknown hindrance selected: %s.', $key);
                continue;
            }

            if (!$this->gameData->hindranceSupportsLevel($key, $level)) {
                $errors[] = sprintf('Invalid level for %s.', $hindrance['name']);
                continue;
            }

            $points += $this->pointsForLevel($level);
        }

        if ($points > self::MAX_HINDRANCE_POINTS) {
            $errors[] = sprintf(
                'You may select up to %d hindrance points.',
                self::MAX_HINDRANCE_POINTS
            );
        }

        return $errors;
    }

    private function replaceForCharacter(int $characterId, array $selected): void
    {
        $this->pdo->transaction(function (SimplePdo $pdo) use ($characterId, $selected): void {
            $pdo->runQuery(
                'DELETE FROM hindrances WHERE hindrance_character_id = ?',
                [$characterId]
            );

            foreach ($selected as $key => $level) {
                $entity = new Entity([
                    'character_id' => $characterId,
                    'key' => $key,
                    'level' => $level,
                ]);

                if (!empty($this->hindrances->validate($entity))) {
                    throw new \RuntimeException('Invalid hindrance row');
                }

                if (!$this->hindrances->insert($entity)) {
                    throw new \RuntimeException('Unable to insert hindrance row');
                }
            }
        });
    }

    private function pointsForLevel(string $level): int
    {
        return match ($level) {
            'major' => 2,
            'minor' => 1,
            default => 0,
        };
    }

    private function pointsUsed(array $selected): int
    {
        $points = 0;

        foreach ($selected as $level) {
            $points += $this->pointsForLevel((string) $level);
        }

        return $points;
    }
}
