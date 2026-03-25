<?php

declare(strict_types=1);

namespace App\Entity\Factory;

use App\Entity;
use App\Entity\Factory;
use Exception;
use Respect\Validation\ValidatorBuilder as v;
use flight\database\SimplePdo;

class Skill extends Factory
{
    public function forCharacter(Entity $character): array
    {
        return $this->find(
            $this->prefix('character_id') . ' = ?',
            [$character->id],
        );
    }

    public function syncForCharacter(Entity $character, array $selected): Result
    {
        $result = new Result();
        try {
            $this->pdo->transaction(function (SimplePdo $pdo) use ($character, $selected) {
                $pdo->delete(
                    $this->getTableName(),
                    'skill_character_id = ?',
                    [$character->id]
                );

                $rows = [];
                foreach ($selected as $key => $die) {
                    $rows[] = [
                        $this->prefix('character_id')  => $character->id,
                        $this->prefix('key')  => $key,
                        $this->prefix('die')  => $die,
                    ];
                }
                if (!$pdo->insert($this->getTableName(), $rows)) {
                    throw new \RuntimeException('Unable to save selected skills');
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
            'die' => v::in([4, 6, 8, 10, 12]),
        ];
    }
}
