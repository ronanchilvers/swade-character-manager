<?php

declare(strict_types=1);

namespace App\Entity\Factory;

use App\Entity;
use App\Entity\Factory;
use Respect\Validation\ValidatorBuilder as v;
use Ronanchilvers\Utility\Str;

class Character extends Factory
{
    public function getValidationRules(): array
    {
        return [
            'hash' => v::not(v::blank()),
            'user' => v::not(v::blank())->greaterThan(1),
            'name' => v::not(v::blank()),
            'concept' => v::not(v::blank()),
        ];
    }

    protected function beforeInsert(Entity $entity): void
    {
        $entity->hash = Str::token(32);
    }
}
