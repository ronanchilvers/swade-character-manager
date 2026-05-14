<?php

declare(strict_types=1);

namespace Tests\Controller;

use App\Controller\Auth;
use App\Entity;
use App\Entity\Factory\Result;
use App\Entity\Factory\User;
use flight\Engine;
use Flight;
use League\OAuth2\Client\Provider\GoogleUser;
use PHPUnit\Framework\TestCase;

class AuthTest extends TestCase
{
    protected function setUp(): void
    {
        Flight::setEngine(new Engine());
        $_POST = [];
    }

    public function testIndexRendersGoogleAuthUrlAndStoresState(): void
    {
        $factory = $this->createStub(User::class);
        $session = new AuthControllerTestSession();

        Flight::map('session', fn () => $session);
        Flight::map('google', fn () => new class {
            public function getAuthorizationUrl(): string
            {
                return 'https://accounts.example/auth';
            }

            public function getState(): string
            {
                return 'oauth-state';
            }
        });
        Flight::map('render', function (string $template, array $data): void {
            throw new AuthControllerRendered($template, $data);
        });

        try {
            (new Auth($factory))->index();
            self::fail('Expected render');
        } catch (AuthControllerRendered $rendered) {
            self::assertSame('auth/index.twig', $rendered->template);
            self::assertSame('login', $rendered->data['body_class']);
            self::assertSame('https://accounts.example/auth', $rendered->data['url']);
        }

        self::assertSame('oauth-state', $session->oauth2state);
    }

    public function testLogoutDeletesSessionUserAndRedirectsHome(): void
    {
        $factory = $this->createStub(User::class);
        $session = new AuthControllerTestSession();
        $session->user = new Entity(['id' => 9]);

        Flight::map('session', fn () => $session);
        Flight::map('redirect', function (string $url): void {
            throw new AuthControllerRedirected($url);
        });

        try {
            (new Auth($factory))->logout();
            self::fail('Expected redirect');
        } catch (AuthControllerRedirected $redirect) {
            self::assertSame('/', $redirect->url);
        }

        self::assertFalse(isset($session->user));
    }

    public function testReturnRejectsMissingState(): void
    {
        $factory = $this->createStub(User::class);
        $session = new AuthControllerTestSession();
        $session->oauth2state = 'expected-state';

        Flight::map('session', fn () => $session);
        Flight::map('request', fn () => new class {
            public array $query = ['code' => 'google-code'];
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
        self::assertFalse(isset($session->oauth2state));
    }

    public function testReturnRejectsMismatchedState(): void
    {
        $factory = $this->createStub(User::class);
        $session = new AuthControllerTestSession();
        $session->oauth2state = 'expected-state';

        Flight::map('session', fn () => $session);
        Flight::map('request', fn () => new class {
            public array $query = [
                'state' => 'wrong-state',
                'code' => 'google-code',
            ];
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
    }

    public function testReturnRejectsProviderError(): void
    {
        $factory = $this->createStub(User::class);
        $session = new AuthControllerTestSession();
        $session->oauth2state = 'expected-state';

        Flight::map('session', fn () => $session);
        Flight::map('request', fn () => new class {
            public array $query = [
                'state' => 'expected-state',
                'error' => 'access_denied',
            ];
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
    }

    public function testReturnRejectsGoogleExceptions(): void
    {
        $factory = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['byEmail'])
            ->getMock();
        $factory->expects(self::never())->method('byEmail');

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
                throw new \RuntimeException('provider unavailable');
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

    public function testReturnCreatesNewActiveUser(): void
    {
        $factory = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['byEmail', 'insert', 'isActive'])
            ->getMock();
        $factory->expects(self::once())
            ->method('byEmail')
            ->with('new@example.com')
            ->willReturn(null);
        $factory->expects(self::once())
            ->method('insert')
            ->with(self::callback(function (Entity $user): bool {
                $user->id = 15;

                return 'New' === $user->firstname
                    && 'Player' === $user->lastname
                    && 'new@example.com' === $user->email
                    && 0 === $user->superuser
                    && User::STATUS_ACTIVE === $user->status;
            }))
            ->willReturn(new Result());
        $factory->expects(self::once())
            ->method('isActive')
            ->with(self::isInstanceOf(Entity::class))
            ->willReturn(true);

        $session = new AuthControllerTestSession();
        $session->oauth2state = 'expected-state';

        $this->mapSuccessfulGoogleLogin($session, new GoogleUser([
            'sub' => 'google-id',
            'name' => 'New Player',
            'given_name' => 'New',
            'family_name' => 'Player',
            'email' => 'new@example.com',
        ]));
        Flight::map('redirect', function (string $url): void {
            throw new AuthControllerRedirected($url);
        });

        try {
            (new Auth($factory))->return();
            self::fail('Expected redirect');
        } catch (AuthControllerRedirected $redirect) {
            self::assertSame('/', $redirect->url);
        }

        self::assertInstanceOf(Entity::class, $session->user);
        self::assertSame(15, $session->user->id);
    }

    public function testReturnRejectsNewUserInsertFailure(): void
    {
        $factory = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['byEmail', 'insert', 'isActive'])
            ->getMock();
        $factory->expects(self::once())
            ->method('byEmail')
            ->willReturn(null);
        $factory->expects(self::once())
            ->method('insert')
            ->willReturn(new Result(['unable to insert']));
        $factory->expects(self::never())
            ->method('isActive');

        $session = new AuthControllerTestSession();
        $session->oauth2state = 'expected-state';

        $this->mapSuccessfulGoogleLogin($session, new GoogleUser([
            'sub' => 'google-id',
            'name' => 'New Player',
            'given_name' => 'New',
            'family_name' => 'Player',
            'email' => 'new@example.com',
        ]));
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
    }

    private function mapSuccessfulGoogleLogin(AuthControllerTestSession $session, ?object $googleUser = null): void
    {
        $googleUser ??= new class {
            public function getEmail(): string
            {
                return 'mara@example.com';
            }
        };

        Flight::map('session', fn () => $session);
        Flight::map('request', fn () => new class {
            public array $query = [
                'state' => 'expected-state',
                'code' => 'google-code',
            ];
        });
        Flight::map('google', fn () => new class ($googleUser) {
            public function __construct(private object $googleUser)
            {
            }

            public function getAccessToken(string $grant, array $params): object
            {
                return (object) ['token' => 'abc'];
            }

            public function getResourceOwner(object $token): object
            {
                return $this->googleUser;
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

class AuthControllerRendered extends \RuntimeException
{
    public function __construct(
        public string $template,
        public array $data,
    ) {
        parent::__construct($template);
    }
}
