<?php

declare(strict_types=1);

namespace Tests\Controller;

use App\Controller\Admin\Users;
use App\Entity;
use App\Entity\Factory\Result;
use App\Entity\Factory\User;
use flight\Engine;
use Flight;
use PHPUnit\Framework\TestCase;

class AdminUsersTest extends TestCase
{
    protected function setUp(): void
    {
        Flight::setEngine(new Engine());
    }

    public function testDisableBlocksCurrentUser(): void
    {
        $factory = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['byId', 'update'])
            ->getMock();
        $factory->expects(self::once())
            ->method('byId')
            ->with(7)
            ->willReturn(new Entity([
                'id' => 7,
                'email' => 'admin@example.com',
                'status' => User::STATUS_ACTIVE,
            ]));
        $factory->expects(self::never())
            ->method('update');

        $session = new AdminUsersControllerTestSession();
        $session->user = (object) ['id' => 7];

        Flight::map('session', fn () => $session);
        Flight::map('redirect', function (string $url): void {
            throw new AdminUsersControllerRedirected($url);
        });

        try {
            (new Users($factory))->disable('7');
            self::fail('Expected redirect');
        } catch (AdminUsersControllerRedirected $redirect) {
            self::assertSame('/admin/users', $redirect->url);
        }

        self::assertSame(['You cannot disable your own account'], $session->errors);
    }

    public function testDisableUpdatesTargetStatus(): void
    {
        $factory = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['byId', 'update'])
            ->getMock();
        $user = new Entity([
            'id' => 8,
            'email' => 'player@example.com',
            'status' => User::STATUS_ACTIVE,
        ]);
        $factory->expects(self::once())
            ->method('byId')
            ->with(8)
            ->willReturn($user);
        $factory->expects(self::once())
            ->method('update')
            ->with(self::callback(function (Entity $updated): bool {
                return User::STATUS_INACTIVE === $updated->status;
            }))
            ->willReturn(new Result());

        $session = new AdminUsersControllerTestSession();
        $session->user = (object) ['id' => 7];

        Flight::map('session', fn () => $session);
        Flight::map('redirect', function (string $url): void {
            throw new AdminUsersControllerRedirected($url);
        });

        try {
            (new Users($factory))->disable('8');
            self::fail('Expected redirect');
        } catch (AdminUsersControllerRedirected $redirect) {
            self::assertSame('/admin/users', $redirect->url);
        }

        self::assertSame(['Disabled player@example.com'], $session->successes);
    }

    public function testEditIgnoresPostedEmailChanges(): void
    {
        $_POST = [
            'firstname' => 'Updated',
            'lastname' => 'Name',
            'email' => 'changed@example.com',
            'status' => User::STATUS_ACTIVE,
            'superuser' => '1',
        ];

        $factory = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['byId', 'validate', 'update'])
            ->getMock();
        $user = new Entity([
            'id' => 8,
            'firstname' => 'Original',
            'lastname' => 'User',
            'email' => 'player@example.com',
            'status' => User::STATUS_ACTIVE,
            'superuser' => 0,
        ]);
        $factory->expects(self::once())
            ->method('byId')
            ->with(8)
            ->willReturn($user);
        $factory->expects(self::once())
            ->method('validate')
            ->with(self::callback(function (Entity $updated): bool {
                return 'Updated' === $updated->firstname
                    && 'Name' === $updated->lastname
                    && 'player@example.com' === $updated->email
                    && 1 === $updated->superuser
                    && User::STATUS_ACTIVE === $updated->status;
            }))
            ->willReturn([]);
        $factory->expects(self::once())
            ->method('update')
            ->with(self::callback(function (Entity $updated): bool {
                return 'player@example.com' === $updated->email;
            }))
            ->willReturn(new Result());

        $session = new AdminUsersControllerTestSession();
        $session->user = (object) ['id' => 7];

        Flight::map('session', fn () => $session);
        Flight::map('request', fn () => new class {
            public function getMethod(): string
            {
                return 'POST';
            }
        });
        Flight::map('redirect', function (string $url): void {
            throw new AdminUsersControllerRedirected($url);
        });

        try {
            (new Users($factory))->edit('8');
            self::fail('Expected redirect');
        } catch (AdminUsersControllerRedirected $redirect) {
            self::assertSame('/admin/users/8', $redirect->url);
        }
    }
}

class AdminUsersControllerTestSession
{
    public array $errors = [];
    public array $successes = [];

    public function error(string $message): void
    {
        $this->errors[] = $message;
    }

    public function success(string $message): void
    {
        $this->successes[] = $message;
    }
}

class AdminUsersControllerRedirected extends \RuntimeException
{
    public function __construct(public string $url)
    {
        parent::__construct($url);
    }
}
