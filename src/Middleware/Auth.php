<?php

declare(strict_types=1);

namespace App\Middleware;

use Flight;
use flight\Engine;

class Auth
{
    public function __construct(private Engine $app)
    {
    }

    public function before($params)
    {
        if (!isset(Flight::session()->user)) {
            Flight::redirect('/auth');
            exit;
        }
    }
}
