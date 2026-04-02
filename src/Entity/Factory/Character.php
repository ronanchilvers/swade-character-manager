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
    private const ATTRIBUTE_FIELDS = [
        'agility' => [
            'name' => 'Agility',
            'description' => 'A measure of a character’s nimbleness,
            dexterity, and general coordination.'
        ],
        'smarts' => [
            'name' => 'Smarts',
            'description' => 'Measures raw intelligence, mental
            acuity, and how fast a heroine thinks on her
            feet. '
        ],
        'spirit' => [
            'name' => 'Spirit',
            'description' => 'Self-confidence, backbone, and
            willpower, used to resist social attacks and fear.'
        ],
        'strength' => [
            'name' => 'Strength',
            'description' => 'Physical power and fitness. It’s
            also used as the basis of a warrior’s damage in
            hand-to-hand combat, and to determine how
            much he can wear or carry'
        ],
        'vigor' => [
            'name' => 'Vigor',
            'description' => 'An individual’s endurance,
            resistance to disease, poison, or toxins, and
            how much physical damage she can take
            before she can’t go on'
        ],
    ];
    private const DEFAULT_PACE = 6;

    public function attributeFields(): array
    {
        return static::ATTRIBUTE_FIELDS;
    }

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

    protected function beforeUpdate(Entity $entity): void
    {
        $entity->pace = static::DEFAULT_PACE;
        $entity->toughness = ceil($entity->vigor / 2);
    }
}
