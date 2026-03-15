<?php

declare(strict_types=1);

namespace App\Controller\Home;

class Index
{
    public function __invoke(): mixed
    {
        echo "hallo";
        exit;
    }
}
