<?php

declare(strict_types=1);

namespace Tests\Support;

class JsonResponse
{
    public int $statusCode = 200;
    public array $headers = [];
    public string $body = '';
    public bool $sent = false;

    public function status(int $code): static
    {
        $this->statusCode = $code;

        return $this;
    }

    public function header(string $name, string $value): static
    {
        $this->headers[$name] = $value;

        return $this;
    }

    public function write(string $body): static
    {
        $this->body .= $body;

        return $this;
    }

    public function send(): static
    {
        $this->sent = true;

        return $this;
    }
}
