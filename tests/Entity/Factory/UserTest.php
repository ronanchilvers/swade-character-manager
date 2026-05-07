<?php

declare(strict_types=1);

namespace Tests\Entity\Factory;

use App\Entity;
use App\Entity\Factory\User;
use App\Entity\Validator;
use flight\database\SimplePdo;
use League\OAuth2\Client\Provider\GoogleUser;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testCreateFromGoogleUserDefaultsToActiveNonSuperuser(): void
    {
        $entity = User::createFromGoogleUser(
            new GoogleUser([
                'email' => 'mara@example.com',
                'given_name' => 'Mara',
                'family_name' => 'Stone',
            ])
        );

        self::assertSame('Mara', $entity->firstname);
        self::assertSame('Stone', $entity->lastname);
        self::assertSame('mara@example.com', $entity->email);
        self::assertSame(0, $entity->superuser);
        self::assertSame(User::STATUS_ACTIVE, $entity->status);
    }

    public function testValidationAcceptsKnownStatusAndSuperuserValues(): void
    {
        $errors = $this->factory()->validate(
            new Entity([
                'firstname' => 'Mara',
                'lastname' => 'Stone',
                'email' => 'mara@example.com',
                'superuser' => 1,
                'status' => User::STATUS_ACTIVE,
            ])
        );

        self::assertSame([], $errors);
    }

    public function testValidationRejectsUnknownStatus(): void
    {
        $errors = $this->factory()->validate(
            new Entity([
                'firstname' => 'Mara',
                'lastname' => 'Stone',
                'email' => 'mara@example.com',
                'superuser' => 0,
                'status' => 'disabled',
            ])
        );

        self::assertSame(['status'], $errors);
    }

    public function testValidationRejectsUnexpectedSuperuserValues(): void
    {
        $errors = $this->factory()->validate(
            new Entity([
                'firstname' => 'Mara',
                'lastname' => 'Stone',
                'email' => 'mara@example.com',
                'superuser' => 4,
                'status' => User::STATUS_ACTIVE,
            ])
        );

        self::assertSame(['superuser'], $errors);
    }

    public function testIsActiveOnlyReturnsTrueForActiveUsers(): void
    {
        $factory = $this->factory();

        self::assertTrue($factory->isActive(new Entity(['status' => User::STATUS_ACTIVE])));
        self::assertFalse($factory->isActive(new Entity(['status' => User::STATUS_INACTIVE])));
        self::assertFalse($factory->isActive(null));
    }

    private function factory(): User
    {
        return new User(
            $this->createStub(SimplePdo::class),
            new Validator(),
        );
    }
}
