<?php

declare(strict_types=1);

namespace Tests\Support;

class RequestStub
{
    public string $scheme = 'https';
    public string $host = 'example.test';

    public function __construct(
        public string $method = 'GET',
        public array $query = [],
        private string $body = '',
        public string $url = '/',
    ) {
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getBody(): string
    {
        return $this->body;
    }
}
