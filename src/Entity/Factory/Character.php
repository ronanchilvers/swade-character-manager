<?php

declare(strict_types=1);

namespace App\Entity\Factory;

use App\Entity\Factory;
use Respect\Validation\ValidatorBuilder as v;

class Character extends Factory
{
    public function getValidationRules(): array
    {
        return [
            'concept' => v::not(v::blank()),
        ];
    }
}
