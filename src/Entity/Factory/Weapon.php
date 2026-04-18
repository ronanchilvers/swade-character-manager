<?php

declare(strict_types=1);

namespace App\Entity\Factory;

use App\Entity;
use App\Entity\Factory;
use Exception;
use Respect\Validation\ValidatorBuilder as v;
use flight\database\SimplePdo;

class Weapon extends Factory
{
    private const STRING_FIELDS = ['name', 'range', 'damage', 'ap', 'rof', 'weight'];

    public function forCharacter(Entity $character): array
    {
        $rows = $this->find(
            $this->prefix('character_id') . ' = ?',
            [$character->id],
        );
        usort($rows, fn (Entity $a, Entity $b) => ((int) $a->position) <=> ((int) $b->position));

        return $rows;
    }

    public function syncForCharacter(Entity $character, array $rows): Result
    {
        $result = new Result();
        try {
            $this->pdo->transaction(function (SimplePdo $pdo) use ($character, $rows) {
                $pdo->delete(
                    $this->getTableName(),
                    'weapon_character_id = ?',
                    [$character->id]
                );

                $inserts = [];
                foreach (array_values($rows) as $position => $row) {
                    $values = [];
                    foreach (self::STRING_FIELDS as $field) {
                        $values[$field] = trim((string) ($row[$field] ?? ''));
                    }
                    if ('' === $values['name']) {
                        continue;
                    }
                    $notes = $row['notes'] ?? null;

                    $inserts[] = [
                        $this->prefix('character_id') => $character->id,
                        $this->prefix('position')     => $position,
                        $this->prefix('name')         => $values['name'],
                        $this->prefix('range')        => $values['range'],
                        $this->prefix('damage')       => $values['damage'],
                        $this->prefix('ap')           => $values['ap'],
                        $this->prefix('rof')          => $values['rof'],
                        $this->prefix('weight')       => $values['weight'],
                        $this->prefix('notes')        => null === $notes ? null : (string) $notes,
                    ];
                }

                if (empty($inserts)) {
                    return;
                }

                if (!$pdo->insert($this->getTableName(), $inserts)) {
                    throw new \RuntimeException('Unable to update character weapons');
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
            'name'         => v::stringType()->length(1, 128),
        ];
    }
}
