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

    public function syncForCharacter(Entity $character, array $selected): Result
    {
        $result = new Result();
        try {
            $this->pdo->transaction(function (SimplePdo $pdo) use ($character, $selected) {
                $pdo->runQuery(
                    'DELETE FROM hindrances WHERE hindrance_character_id = ?',
                    [$character->id]
                );

                $rows = [];
                foreach ($selected as $key => $level) {
                    $entity = new Entity([
                        'character_id' => $character->id,
                        'key' => $key,
                        'level' => $level,
                    ]);
                    if (!empty($this->validate($entity))) {
                        throw new \RuntimeException('Invalid hindrance row');
                    }
                    if (!$this->insert($entity)) {
                        throw new \RuntimeException('Unable to insert hindrance row');
                    }
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
