<?php

declare(strict_types=1);

namespace App\Entity\Factory;

use App\Entity;
use App\Entity\Factory;
use Exception;
use Respect\Validation\ValidatorBuilder as v;
use flight\database\SimplePdo;

class Edge extends Factory
{
    public function forCharacter(Entity $character): array
    {
        return $this->find(
            $this->prefix('character_id') . ' = ?',
            [$character->id],
            "edge_created ASC"
        );
    }

    public function syncForCharacter(Entity $character, array $selected): Result
    {
        $result = new Result();
        try {
            $this->pdo->transaction(function (SimplePdo $pdo) use ($character, $selected) {
                $pdo->delete(
                    $this->getTableName(),
                    'edge_character_id = ?',
                    [$character->id]
                );

                $rows = [];
                foreach ($selected as $key => $count) {
                    $rows[] = [
                        $this->prefix('character_id') => $character->id,
                        $this->prefix('key') => $key,
                        $this->prefix('count') => $count,
                    ];
                }

                if (empty($rows)) {
                    return;
                }

                if (!$pdo->insert($this->getTableName(), $rows)) {
                    throw new \RuntimeException('Unable to update character edges');
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
            'count' => v::intVal()->greaterThan(0),
        ];
    }
}
