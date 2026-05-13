<?php

declare(strict_types=1);

namespace Tests\Middleware;

use App\Entity;
use App\Entity\Factory\User;
use App\Middleware\Auth;
use flight\Engine;
use Flight;
use PHPUnit\Framework\TestCase;

class AuthTest extends TestCase
{
    protected function setUp(): void
    {
        Flight::setEngine(new Engine());
    }

    public function testMissingSessionUserRedirectsToLogin(): void
    {
        $factory = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['byId', 'isActive'])
            ->getMock();

        $session = new AuthMiddlewareTestSession();
        Flight::map('session', fn () => $session);
        Flight::map('request', fn () => new class {
            public string $method = 'GET';
            public string $url = '/campaigns/join/aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';
        });
        Flight::map('redirect', function (string $url): void {
            throw new AuthMiddlewareRedirected($url);
        });

        try {
            (new Auth($factory))->before([]);
            self::fail('Expected redirect');
        } catch (AuthMiddlewareRedirected $redirect) {
            self::assertSame('/auth', $redirect->url);
        }

        self::assertSame('/campaigns/join/aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', $session->auth_return_url);
    }

    public function testMissingSessionUserIgnoresUnsafeReturnUrl(): void
    {
        $factory = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['byId', 'isActive'])
            ->getMock();

        $session = new AuthMiddlewareTestSession();
        Flight::map('session', fn () => $session);
        Flight::map('request', fn () => new class {
            public string $method = 'GET';
            public string $url = '//evil.example/path';
        });
        Flight::map('redirect', function (string $url): void {
            throw new AuthMiddlewareRedirected($url);
        });

        try {
            (new Auth($factory))->before([]);
            self::fail('Expected redirect');
        } catch (AuthMiddlewareRedirected $redirect) {
            self::assertSame('/auth', $redirect->url);
        }

        self::assertFalse(isset($session->auth_return_url));
    }

    public function testInactiveSessionUserIsLoggedOutWithGenericMessage(): void
    {
        $factory = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['byId', 'isActive'])
            ->getMock();
        $factory->expects(self::once())
            ->method('byId')
            ->with(4)
            ->willReturn(new Entity([
                'id' => 4,
                'status' => User::STATUS_INACTIVE,
            ]));
        $factory->expects(self::once())
            ->method('isActive')
            ->willReturn(false);

        $session = new AuthMiddlewareTestSession();
        $session->user = (object) ['id' => 4];

        Flight::map('session', fn () => $session);
        Flight::map('redirect', function (string $url): void {
            throw new AuthMiddlewareRedirected($url);
        });

        try {
            (new Auth($factory))->before([]);
            self::fail('Expected redirect');
        } catch (AuthMiddlewareRedirected $redirect) {
            self::assertSame('/auth', $redirect->url);
        }

        self::assertSame(['Login failed'], $session->errors);
        self::assertFalse(isset($session->user));
    }

    public function testActiveSessionUserIsRefreshed(): void
    {
        $factory = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['byId', 'isActive'])
            ->getMock();
        $freshUser = new Entity([
            'id' => 4,
            'status' => User::STATUS_ACTIVE,
            'firstname' => 'Mara',
        ]);
        $factory->expects(self::once())
            ->method('byId')
            ->with(4)
            ->willReturn($freshUser);
        $factory->expects(self::once())
            ->method('isActive')
            ->with($freshUser)
            ->willReturn(true);

        $session = new AuthMiddlewareTestSession();
        $session->user = (object) ['id' => 4, 'firstname' => 'Old'];

        Flight::map('session', fn () => $session);
        Flight::map('redirect', function (string $url): void {
            throw new AuthMiddlewareRedirected($url);
        });

        (new Auth($factory))->before([]);

        self::assertSame($freshUser, $session->user);
        self::assertSame([], $session->errors);
    }
}

class AuthMiddlewareTestSession
{
    public array $errors = [];

    public function delete(string $key): void
    {
        unset($this->{$key});
    }

    public function error(string $message): void
    {
        $this->errors[] = $message;
    }
}

class AuthMiddlewareRedirected extends \RuntimeException
{
    public function __construct(public string $url)
    {
        parent::__construct($url);
    }
}
