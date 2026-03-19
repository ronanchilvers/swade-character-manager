<?php

declare(strict_types=1);

namespace App\Entity\Factory;

use App\Entity;
use App\Entity\Factory;
use League\OAuth2\Client\Provider\GoogleUser;
use Respect\Validation\ValidatorBuilder as v;

class User extends Factory
{
    public static function createFromGoogleUser(GoogleUser $googleUser): Entity
    {
        $entity          = new Entity();
        $entity->firstname = $googleUser->getFirstName();
        $entity->lastname  = $googleUser->getLastName();
        $entity->email     = $googleUser->getEmail();

        return $entity;
    }

    public function getValidationRules(): array
    {
        return [
            'firstname' => v::not(v::blank()),
            'lastname' => v::not(v::blank()),
            'email' => v::not(v::blank())->email(),
        ];
    }
}
