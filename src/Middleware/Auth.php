<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Entity\Factory\User;
use Flight;

class Auth
{
    private const LOGIN_FAILED_MESSAGE = 'Login failed';

    public function __construct(private User $factory)
    {
    }

    public function before($params): void
    {
        $session = Flight::session();
        if (!isset($session->user) || !isset($session->user->id)) {
            $this->storeReturnUrl($session);
            Flight::redirect('/auth');
            exit;
        }

        $user = $this->factory->byId((int) $session->user->id);
        if (!$this->factory->isActive($user)) {
            $session->delete('user');
            $session->error(static::LOGIN_FAILED_MESSAGE);
            Flight::redirect('/auth');
            exit;
        }

        $session->user = $user;
    }

    private function storeReturnUrl(object $session): void
    {
        $request = Flight::request();
        if (!isset($request->method) || 'GET' !== strtoupper((string) $request->method)) {
            return;
        }
        if (!isset($request->url) || !$this->isSafeReturnUrl((string) $request->url)) {
            return;
        }

        $session->auth_return_url = (string) $request->url;
    }

    private function isSafeReturnUrl(string $url): bool
    {
        if (!str_starts_with($url, '/') || str_starts_with($url, '//')) {
            return false;
        }

        $parts = parse_url($url);

        return is_array($parts)
            && !isset($parts['scheme'])
            && !isset($parts['host']);
    }
}
