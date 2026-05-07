<?php

declare(strict_types=1);

namespace App\Middleware;

use Flight;

class Superuser
{
    public function before($params): void
    {
        $session = Flight::session();
        if (!isset($session->user)) {
            Flight::redirect('/auth');
            exit;
        }

        if (!(bool) $session->user->superuser) {
            $session->error('You do not have access to that page');
            Flight::redirect('/');
            exit;
        }
    }
}
