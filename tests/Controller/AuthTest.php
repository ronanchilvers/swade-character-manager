<?php

declare(strict_types=1);

namespace Tests\Controller;

use App\Controller\Auth;
use App\Entity;
use App\Entity\Factory\User;
use flight\Engine;
use Flight;
use PHPUnit\Framework\TestCase;

class AuthTest extends TestCase
{
    protected function setUp(): void
    {
        Flight::setEngine(new Engine());
    }

    public function testInactiveUserGetsGenericLoginFailureMessage(): void
    {
        $factory = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['byEmail', 'isActive'])
            ->getMock();
        $factory->expects(self::once())
            ->method('byEmail')
            ->with('mara@example.com')
            ->willReturn(new Entity([
                'id' => 9,
                'email' => 'mara@example.com',
                'status' => User::STATUS_INACTIVE,
            ]));
        $factory->expects(self::once())
            ->method('isActive')
            ->willReturn(false);

        $session = new AuthControllerTestSession();
        $session->oauth2state = 'expected-state';

        Flight::map('session', fn () => $session);
        Flight::map('request', fn () => new class {
            public array $query = [
                'state' => 'expected-state',
                'code' => 'google-code',
            ];
        });
        Flight::map('google', fn () => new class {
            public function getAccessToken(string $grant, array $params): object
            {
                return (object) ['token' => 'abc'];
            }

            public function getResourceOwner(object $token): object
            {
                return new class {
                    public function getEmail(): string
                    {
                        return 'mara@example.com';
                    }
                };
            }
        });
        Flight::map('redirect', function (string $url): void {
            throw new AuthControllerRedirected($url);
        });

        try {
            (new Auth($factory))->return();
            self::fail('Expected redirect');
        } catch (AuthControllerRedirected $redirect) {
            self::assertSame('/auth', $redirect->url);
        }

        self::assertSame(['Login failed'], $session->errors);
        self::assertFalse(isset($session->user));
        self::assertFalse(isset($session->oauth2state));
    }

    public function testSuccessfulLoginRedirectsToSafeStoredReturnUrlAndClearsIt(): void
    {
        $factory = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['byEmail', 'isActive'])
            ->getMock();
        $user = new Entity([
            'id' => 9,
            'email' => 'mara@example.com',
            'status' => User::STATUS_ACTIVE,
        ]);
        $factory->expects(self::once())
            ->method('byEmail')
            ->with('mara@example.com')
            ->willReturn($user);
        $factory->expects(self::once())
            ->method('isActive')
            ->with($user)
            ->willReturn(true);

        $session = new AuthControllerTestSession();
        $session->oauth2state = 'expected-state';
        $session->auth_return_url = '/campaigns/join/aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';

        $this->mapSuccessfulGoogleLogin($session);
        Flight::map('redirect', function (string $url): void {
            throw new AuthControllerRedirected($url);
        });

        try {
            (new Auth($factory))->return();
            self::fail('Expected redirect');
        } catch (AuthControllerRedirected $redirect) {
            self::assertSame('/campaigns/join/aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', $redirect->url);
        }

        self::assertSame($user, $session->user);
        self::assertFalse(isset($session->auth_return_url));
    }

    public function testSuccessfulLoginIgnoresUnsafeStoredReturnUrl(): void
    {
        $factory = $this->createStub(User::class);
        $user = new Entity([
            'id' => 9,
            'email' => 'mara@example.com',
            'status' => User::STATUS_ACTIVE,
        ]);
        $factory->method('byEmail')
            ->willReturn($user);
        $factory->method('isActive')
            ->willReturn(true);

        $session = new AuthControllerTestSession();
        $session->oauth2state = 'expected-state';
        $session->auth_return_url = 'https://evil.example/path';

        $this->mapSuccessfulGoogleLogin($session);
        Flight::map('redirect', function (string $url): void {
            throw new AuthControllerRedirected($url);
        });

        try {
            (new Auth($factory))->return();
            self::fail('Expected redirect');
        } catch (AuthControllerRedirected $redirect) {
            self::assertSame('/', $redirect->url);
        }

        self::assertFalse(isset($session->auth_return_url));
    }

    private function mapSuccessfulGoogleLogin(AuthControllerTestSession $session): void
    {
        Flight::map('session', fn () => $session);
        Flight::map('request', fn () => new class {
            public array $query = [
                'state' => 'expected-state',
                'code' => 'google-code',
            ];
        });
        Flight::map('google', fn () => new class {
            public function getAccessToken(string $grant, array $params): object
            {
                return (object) ['token' => 'abc'];
            }

            public function getResourceOwner(object $token): object
            {
                return new class {
                    public function getEmail(): string
                    {
                        return 'mara@example.com';
                    }
                };
            }
        });
    }
}

class AuthControllerTestSession
{
    public array $errors = [];
    public ?string $oauth2state = null;
    public ?string $auth_return_url = null;
    public mixed $user = null;

    public function delete(string $key): void
    {
        unset($this->{$key});
    }

    public function error(string $message): void
    {
        $this->errors[] = $message;
    }
}

class AuthControllerRedirected extends \RuntimeException
{
    public function __construct(public string $url)
    {
        parent::__construct($url);
    }
}
