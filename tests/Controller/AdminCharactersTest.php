<?php

declare(strict_types=1);

namespace Tests\Controller;

use App\Controller\Admin\Characters;
use App\Entity;
use App\Entity\Factory\Character as CharacterFactory;
use App\Entity\Factory\User;
use flight\Engine;
use Flight;
use PHPUnit\Framework\TestCase;

class AdminCharactersTest extends TestCase
{
    protected function setUp(): void
    {
        Flight::setEngine(new Engine());
    }

    public function testIndexShowsCharactersForRequestedUser(): void
    {
        $user = new Entity([
            'id' => 4,
            'firstname' => 'Alicia',
            'lastname' => 'Keys',
            'email' => 'alicia@example.com',
            'status' => 'active',
        ]);

        $userFactory = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['byId'])
            ->getMock();
        $userFactory->expects(self::once())
            ->method('byId')
            ->with(4)
            ->willReturn($user);

        $characterFactory = $this->getMockBuilder(CharacterFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['forUser'])
            ->getMock();
        $characterFactory->expects(self::once())
            ->method('forUser')
            ->with(4)
            ->willReturn([new Entity(['name' => 'Mara', 'created' => '2026-05-01 10:00:00'])]);

        Flight::map('render', function (string $template, array $data): void {
            throw new AdminCharactersRendered($template, $data);
        });

        try {
            (new Characters($userFactory, $characterFactory))->index('4');
            self::fail('Expected render');
        } catch (AdminCharactersRendered $rendered) {
            self::assertSame('admin/characters/index.twig', $rendered->template);
            self::assertSame('User Characters', $rendered->data['page_title']);
            self::assertSame('Alicia', $rendered->data['user']->firstname);
            self::assertCount(1, $rendered->data['characters']);
            self::assertSame('Mara', $rendered->data['characters'][0]->name);
            self::assertSame('Pace', $rendered->data['primary_stats']['pace']);
            self::assertSame('Toughness', $rendered->data['primary_stats']['toughness']);
        }
    }

    public function testIndexRedirectsWhenUserIdIsMissing(): void
    {
        $userFactory = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['byId'])
            ->getMock();
        $userFactory->expects(self::never())->method('byId');

        $characterFactory = $this->getMockBuilder(CharacterFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['forUser'])
            ->getMock();

        $session = new AdminCharactersSession();
        Flight::map('session', fn () => $session);
        Flight::map('redirect', function (string $url): void {
            throw new AdminCharactersRedirected($url);
        });

        try {
            (new Characters($userFactory, $characterFactory))->index('0');
            self::fail('Expected redirect');
        } catch (AdminCharactersRedirected $redirected) {
            self::assertSame('/admin/users', $redirected->url);
        }

        self::assertSame(['Unable to find user'], $session->errors);
    }

    public function testIndexRedirectsWhenUserDoesNotExist(): void
    {
        $userFactory = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['byId'])
            ->getMock();
        $userFactory->expects(self::once())
            ->method('byId')
            ->with(9)
            ->willReturn(null);

        $characterFactory = $this->getMockBuilder(CharacterFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['forUser'])
            ->getMock();

        $session = new AdminCharactersSession();
        Flight::map('session', fn () => $session);
        Flight::map('redirect', function (string $url): void {
            throw new AdminCharactersRedirected($url);
        });

        try {
            (new Characters($userFactory, $characterFactory))->index('9');
            self::fail('Expected redirect');
        } catch (AdminCharactersRedirected $redirected) {
            self::assertSame('/admin/users', $redirected->url);
        }

        self::assertSame(['Unable to find user'], $session->errors);
    }
}

class AdminCharactersRendered extends \RuntimeException
{
    public function __construct(
        public string $template,
        public array $data,
    ) {
        parent::__construct($template);
    }
}

class AdminCharactersSession
{
    public array $errors = [];

    public function error(string $message): void
    {
        $this->errors[] = $message;
    }
}

class AdminCharactersRedirected extends \RuntimeException
{
    public function __construct(public string $url)
    {
        parent::__construct($url);
    }
}
