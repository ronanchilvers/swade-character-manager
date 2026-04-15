<?php

declare(strict_types=1);

namespace App\Entity\Factory;

use App\Entity;
use App\Entity\Factory;
use Exception;
use Respect\Validation\ValidatorBuilder as v;
use flight\database\SimplePdo;

class Hindrance extends Factory
{
    public const MAX_POINTS = 4;

    public function forCharacter(Entity $character): array
    {
        return $this->find(
            $this->prefix('character_id') . ' = ?',
            [$character->id],
        );
    }

    public function pointsForCharacter(Entity $character): int
    {
        $hindrances = $this->forCharacter($character);
        $points = 0;
        foreach ($hindrances as $hindrance) {
            $points += "major" == $hindrance->level ? 2 : 1;
        }

        return $points;
    }

    public function syncForCharacter(Entity $character, array $selected): Result
    {
        $result = new Result();
        try {
            $this->pdo->transaction(function (SimplePdo $pdo) use ($character, $selected) {
                $num = $pdo->delete(
                    $this->getTableName(),
                    'hindrance_character_id = ?',
                    [$character->id]
                );
                $rows = [];
                foreach ($selected as $key => $level) {
                    $rows[] = [
                        $this->prefix('character_id') => $character->id,
                        $this->prefix('key') => $key,
                        $this->prefix('level') => $level,
                    ];
                }
                if (empty($rows)) {
                    return;
                }
                if (!$pdo->insert($this->getTableName(), $rows)) {
                    throw new \RuntimeException('Unable to update character hindrances');
                }
            });

            return $result;
        } catch (Exception $ex) {
            return $result
                ->addError($ex->getMessage());
        }
    }

    public function getValidationRules(): array
    {
        return [
            'character_id' => v::intVal()->greaterThan(0),
            'key' => v::stringType()->notBlank(),
            'level' => v::in(['minor', 'major']),
        ];
    }
}
