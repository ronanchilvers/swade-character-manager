<?php

declare(strict_types=1);

namespace Tests\Support;

use RuntimeException;

class RedirectedResponse extends RuntimeException
{
    public function __construct(public string $url)
    {
        parent::__construct($url);
    }
}
