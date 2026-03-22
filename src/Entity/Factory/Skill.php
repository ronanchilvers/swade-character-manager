<?php

declare(strict_types=1);

namespace App\Entity\Factory;

use App\Entity\Factory;
use Respect\Validation\ValidatorBuilder as v;

class Skill extends Factory
{
    public function forCharacter(int $characterId): array
    {
        return $this->find(
            $this->prefix('character_id') . ' = ?',
            [$characterId],
        );
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
