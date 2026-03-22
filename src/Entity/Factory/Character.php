<?php

declare(strict_types=1);

namespace App\Entity\Factory;

use App\Entity;
use App\Entity\Factory;
use Flight;
use Respect\Validation\ValidatorBuilder as v;
use Ronanchilvers\Utility\Str;

class Character extends Factory
{
    public function forUser(int $id)
    {
        return $this->find(
            $this->prefix('user') . ' = ?',
            [$id],
        );
    }

    public function forHash(string $hash)
    {
        return $this->one(
            $this->prefix('hash') . ' = ?',
            [$hash],
        );
    }

    public function getValidationRules(): array
    {
        return [
            'hash' => v::not(v::blank()),
            'user' => v::intVal()->greaterThan(0),
            'name' => v::not(v::blank()),
            'agility' => v::intVal()->in([4, 6, 8, 10, 12]),
            'smarts' => v::intVal()->in([4, 6, 8, 10, 12]),
            'spirit' => v::intVal()->in([4, 6, 8, 10, 12]),
            'strength' => v::intVal()->in([4, 6, 8, 10, 12]),
            'vigor' => v::intVal()->in([4, 6, 8, 10, 12]),
        ];
    }

    protected function beforeInsert(Entity $entity): void
    {
        $entity->user = Flight::session()->user->id;
        $entity->hash = Str::token(32);
    }
}
