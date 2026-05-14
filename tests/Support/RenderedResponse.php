<?php

declare(strict_types=1);

namespace Tests\Support;

use RuntimeException;

class RenderedResponse extends RuntimeException
{
    public function __construct(
        public string $template,
        public array $data,
    ) {
        parent::__construct($template);
    }
}
