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
        $_POST = [];
    }

    public function testIndexRendersOrderedUsers(): void
    {
        $users = [
            new Entity(['email' => 'admin@example.com']),
            new Entity(['email' => 'player@example.com']),
        ];
        $factory = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['ordered'])
            ->getMock();
        $factory->expects(self::once())
            ->method('ordered')
            ->willReturn($users);

        Flight::map('render', function (string $template, array $data): void {
            throw new AdminUsersRendered($template, $data);
        });

        try {
            (new Users($factory))->index();
            self::fail('Expected render');
        } catch (AdminUsersRendered $rendered) {
            self::assertSame('admin/users/index.twig', $rendered->template);
            self::assertSame('Manage Users', $rendered->data['page_title']);
            self::assertSame($users, $rendered->data['users']);
        }
    }

    public function testEditGetRendersUserForm(): void
    {
        $user = new Entity([
            'id' => 8,
            'email' => 'player@example.com',
            'status' => User::STATUS_ACTIVE,
            'superuser' => 0,
        ]);
        $factory = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['byId'])
            ->getMock();
        $factory->expects(self::once())
            ->method('byId')
            ->with(8)
            ->willReturn($user);

        $session = new AdminUsersControllerTestSession();
        $session->user = (object) ['id' => 7];

        Flight::map('session', fn () => $session);
        Flight::map('request', fn () => new class {
            public function getMethod(): string
            {
                return 'GET';
            }
        });
        Flight::map('render', function (string $template, array $data): void {
            throw new AdminUsersRendered($template, $data);
        });

        try {
            (new Users($factory))->edit('8');
            self::fail('Expected render');
        } catch (AdminUsersRendered $rendered) {
            self::assertSame('admin/users/edit.twig', $rendered->template);
            self::assertSame('Edit User', $rendered->data['page_title']);
            self::assertSame($user, $rendered->data['entity']);
            self::assertSame(User::statuses(), $rendered->data['statuses']);
            self::assertFalse($rendered->data['is_self']);
            self::assertSame([], $rendered->data['errors']);
        }
    }

    public function testEditRedirectsWhenUserIsMissing(): void
    {
        $factory = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['byId'])
            ->getMock();
        $factory->expects(self::never())->method('byId');

        $session = new AdminUsersControllerTestSession();

        Flight::map('session', fn () => $session);
        Flight::map('redirect', function (string $url): void {
            throw new AdminUsersControllerRedirected($url);
        });

        try {
            (new Users($factory))->edit('0');
            self::fail('Expected redirect');
        } catch (AdminUsersControllerRedirected $redirect) {
            self::assertSame('/admin/users', $redirect->url);
        }

        self::assertSame(['Unable to find user'], $session->errors);
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

    public function testEditBlocksSelfDemotion(): void
    {
        $_POST = [
            'firstname' => 'Admin',
            'lastname' => 'User',
            'status' => User::STATUS_ACTIVE,
        ];

        $factory = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['byId', 'validate', 'update'])
            ->getMock();
        $factory->method('byId')
            ->willReturn(new Entity([
                'id' => 7,
                'email' => 'admin@example.com',
                'superuser' => 1,
                'status' => User::STATUS_ACTIVE,
            ]));
        $factory->expects(self::once())
            ->method('validate')
            ->willReturn([]);
        $factory->expects(self::never())
            ->method('update');

        $session = new AdminUsersControllerTestSession();
        $session->user = (object) ['id' => 7];

        Flight::map('session', fn () => $session);
        Flight::map('request', fn () => new class {
            public function getMethod(): string
            {
                return 'POST';
            }
        });
        Flight::map('render', function (string $template, array $data): void {
            throw new AdminUsersRendered($template, $data);
        });

        try {
            (new Users($factory))->edit('7');
            self::fail('Expected render');
        } catch (AdminUsersRendered $rendered) {
            self::assertSame(['superuser'], $rendered->data['errors']);
            self::assertTrue($rendered->data['is_self']);
        }

        self::assertSame(['You cannot remove your own superuser access'], $session->errors);
    }

    public function testEditBlocksSelfDisable(): void
    {
        $_POST = [
            'firstname' => 'Admin',
            'lastname' => 'User',
            'status' => User::STATUS_INACTIVE,
            'superuser' => '1',
        ];

        $factory = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['byId', 'validate', 'update'])
            ->getMock();
        $factory->method('byId')
            ->willReturn(new Entity([
                'id' => 7,
                'email' => 'admin@example.com',
                'superuser' => 1,
                'status' => User::STATUS_ACTIVE,
            ]));
        $factory->method('validate')
            ->willReturn([]);
        $factory->expects(self::never())
            ->method('update');

        $session = new AdminUsersControllerTestSession();
        $session->user = (object) ['id' => 7];

        Flight::map('session', fn () => $session);
        Flight::map('request', fn () => new class {
            public function getMethod(): string
            {
                return 'POST';
            }
        });
        Flight::map('render', function (string $template, array $data): void {
            throw new AdminUsersRendered($template, $data);
        });

        try {
            (new Users($factory))->edit('7');
            self::fail('Expected render');
        } catch (AdminUsersRendered $rendered) {
            self::assertSame(['status'], $rendered->data['errors']);
        }

        self::assertSame(['You cannot disable your own account'], $session->errors);
    }

    public function testEditRendersValidationErrors(): void
    {
        $_POST = [
            'firstname' => '',
            'lastname' => 'User',
            'status' => User::STATUS_ACTIVE,
        ];

        $factory = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['byId', 'validate', 'update'])
            ->getMock();
        $factory->method('byId')
            ->willReturn(new Entity([
                'id' => 8,
                'email' => 'player@example.com',
                'superuser' => 0,
                'status' => User::STATUS_ACTIVE,
            ]));
        $factory->expects(self::once())
            ->method('validate')
            ->willReturn(['firstname']);
        $factory->expects(self::never())
            ->method('update');

        $session = new AdminUsersControllerTestSession();
        $session->user = (object) ['id' => 7];

        Flight::map('session', fn () => $session);
        Flight::map('request', fn () => new class {
            public function getMethod(): string
            {
                return 'POST';
            }
        });
        Flight::map('render', function (string $template, array $data): void {
            throw new AdminUsersRendered($template, $data);
        });

        try {
            (new Users($factory))->edit('8');
            self::fail('Expected render');
        } catch (AdminUsersRendered $rendered) {
            self::assertSame(['firstname'], $rendered->data['errors']);
        }

        self::assertSame(['Sorry! There was a problem!'], $session->errors);
    }

    public function testEditRendersUpdateErrors(): void
    {
        $_POST = [
            'firstname' => 'Player',
            'lastname' => 'User',
            'status' => User::STATUS_ACTIVE,
        ];

        $factory = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['byId', 'validate', 'update'])
            ->getMock();
        $factory->method('byId')
            ->willReturn(new Entity([
                'id' => 8,
                'email' => 'player@example.com',
                'superuser' => 0,
                'status' => User::STATUS_ACTIVE,
            ]));
        $factory->method('validate')
            ->willReturn([]);
        $factory->expects(self::once())
            ->method('update')
            ->willReturn(new Result(['database failed']));

        $session = new AdminUsersControllerTestSession();
        $session->user = (object) ['id' => 7];

        Flight::map('session', fn () => $session);
        Flight::map('request', fn () => new class {
            public function getMethod(): string
            {
                return 'POST';
            }
        });
        Flight::map('render', function (string $template, array $data): void {
            throw new AdminUsersRendered($template, $data);
        });

        try {
            (new Users($factory))->edit('8');
            self::fail('Expected render');
        } catch (AdminUsersRendered $rendered) {
            self::assertSame(['database failed'], $rendered->data['errors']);
        }

        self::assertSame(['Sorry! There was a problem!'], $session->errors);
    }

    public function testEnableUpdatesTargetStatus(): void
    {
        $factory = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['byId', 'update'])
            ->getMock();
        $user = new Entity([
            'id' => 8,
            'email' => 'player@example.com',
            'status' => User::STATUS_INACTIVE,
        ]);
        $factory->expects(self::once())
            ->method('byId')
            ->with(8)
            ->willReturn($user);
        $factory->expects(self::once())
            ->method('update')
            ->with(self::callback(fn (Entity $updated): bool => User::STATUS_ACTIVE === $updated->status))
            ->willReturn(new Result());

        $session = new AdminUsersControllerTestSession();
        $session->user = (object) ['id' => 7];

        Flight::map('session', fn () => $session);
        Flight::map('redirect', function (string $url): void {
            throw new AdminUsersControllerRedirected($url);
        });

        try {
            (new Users($factory))->enable('8');
            self::fail('Expected redirect');
        } catch (AdminUsersControllerRedirected $redirect) {
            self::assertSame('/admin/users', $redirect->url);
        }

        self::assertSame(['Re-enabled player@example.com'], $session->successes);
    }

    public function testDisableRedirectsWhenTargetIsMissing(): void
    {
        $factory = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['byId', 'update'])
            ->getMock();
        $factory->expects(self::once())
            ->method('byId')
            ->with(9)
            ->willReturn(null);
        $factory->expects(self::never())
            ->method('update');

        $session = new AdminUsersControllerTestSession();
        Flight::map('session', fn () => $session);
        Flight::map('redirect', function (string $url): void {
            throw new AdminUsersControllerRedirected($url);
        });

        try {
            (new Users($factory))->disable('9');
            self::fail('Expected redirect');
        } catch (AdminUsersControllerRedirected $redirect) {
            self::assertSame('/admin/users', $redirect->url);
        }

        self::assertSame(['Unable to find user'], $session->errors);
    }

    public function testDisableFlashesUpdateFailure(): void
    {
        $factory = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['byId', 'update'])
            ->getMock();
        $factory->expects(self::once())
            ->method('byId')
            ->willReturn(new Entity([
                'id' => 8,
                'email' => 'player@example.com',
                'status' => User::STATUS_ACTIVE,
            ]));
        $factory->expects(self::once())
            ->method('update')
            ->willReturn(new Result(['database failed']));

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

        self::assertSame(['Sorry! There was a problem!'], $session->errors);
    }
}

class AdminUsersRendered extends \RuntimeException
{
    public function __construct(
        public string $template,
        public array $data,
    ) {
        parent::__construct($template);
    }
}

class AdminUsersControllerTestSession
{
    public array $errors = [];
    public array $successes = [];
    public ?object $user = null;

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
