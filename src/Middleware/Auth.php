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
}
