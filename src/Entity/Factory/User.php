<?php

declare(strict_types=1);

namespace App\Entity\Factory;

use App\Entity;
use App\Entity\Factory;
use League\OAuth2\Client\Provider\GoogleUser;
use Respect\Validation\ValidatorBuilder as v;

class User extends Factory
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    public static function createFromGoogleUser(GoogleUser $googleUser): Entity
    {
        $entity            = new Entity();
        $entity->firstname = $googleUser->getFirstName();
        $entity->lastname  = $googleUser->getLastName();
        $entity->email     = $googleUser->getEmail();
        $entity->superuser = 0;
        $entity->status    = static::STATUS_ACTIVE;

        return $entity;
    }

    public static function statuses(): array
    {
        return [
            static::STATUS_ACTIVE,
            static::STATUS_INACTIVE,
        ];
    }

    public function byEmail(string $email): ?Entity
    {
        return $this->one(
            $this->prefix('email') . ' = ?',
            [$email],
        );
    }

    public function byId(int $id): ?Entity
    {
        return $this->one(
            $this->prefix('id') . ' = ?',
            [$id],
        );
    }

    public function ordered(): array
    {
        return $this->find(
            order: implode(', ', [
                $this->prefix('lastname') . ' ASC',
                $this->prefix('firstname') . ' ASC',
                $this->prefix('email') . ' ASC',
            ]),
        );
    }

    public function isActive(?Entity $entity): bool
    {
        return $entity instanceof Entity
            && static::STATUS_ACTIVE === (string) $entity->status;
    }

    public function getValidationRules(): array
    {
        return [
            'firstname' => v::not(v::blank()),
            'lastname' => v::not(v::blank()),
            'email' => v::not(v::blank())->email(),
            'superuser' => v::intVal()->in([0, 1]),
            'status' => v::in(static::statuses()),
        ];
    }
}
