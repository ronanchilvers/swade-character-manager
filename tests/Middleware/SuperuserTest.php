<?php

declare(strict_types=1);

namespace Tests\Middleware;

use App\Middleware\Superuser;
use flight\Engine;
use Flight;
use PHPUnit\Framework\TestCase;

class SuperuserTest extends TestCase
{
    protected function setUp(): void
    {
        Flight::setEngine(new Engine());
    }

    public function testMissingSessionUserRedirectsToLogin(): void
    {
        Flight::map('session', fn () => new SuperuserMiddlewareTestSession());
        Flight::map('redirect', function (string $url): void {
            throw new SuperuserMiddlewareRedirected($url);
        });

        try {
            (new Superuser())->before([]);
            self::fail('Expected redirect');
        } catch (SuperuserMiddlewareRedirected $redirect) {
            self::assertSame('/auth', $redirect->url);
        }
    }

    public function testNonSuperuserIsRedirectedHome(): void
    {
        $session = new SuperuserMiddlewareTestSession();
        $session->user = (object) ['id' => 3, 'superuser' => 0];

        Flight::map('session', fn () => $session);
        Flight::map('redirect', function (string $url): void {
            throw new SuperuserMiddlewareRedirected($url);
        });

        try {
            (new Superuser())->before([]);
            self::fail('Expected redirect');
        } catch (SuperuserMiddlewareRedirected $redirect) {
            self::assertSame('/', $redirect->url);
        }

        self::assertSame(['You do not have access to that page'], $session->errors);
    }

    public function testSuperuserMayProceed(): void
    {
        $session = new SuperuserMiddlewareTestSession();
        $session->user = (object) ['id' => 3, 'superuser' => 1];

        Flight::map('session', fn () => $session);
        Flight::map('redirect', function (string $url): void {
            throw new SuperuserMiddlewareRedirected($url);
        });

        (new Superuser())->before([]);

        self::assertSame([], $session->errors);
    }
}

class SuperuserMiddlewareTestSession
{
    public array $errors = [];

    public function error(string $message): void
    {
        $this->errors[] = $message;
    }
}

class SuperuserMiddlewareRedirected extends \RuntimeException
{
    public function __construct(public string $url)
    {
        parent::__construct($url);
    }
}
