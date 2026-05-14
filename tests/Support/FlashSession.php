<?php

declare(strict_types=1);

namespace Tests\Support;

class FlashSession
{
    public array $errors = [];
    public array $successes = [];
    public ?string $auth_return_url = null;
    public ?string $oauth2state = null;
    public mixed $user = null;

    public function delete(string $key): void
    {
        unset($this->{$key});
    }

    public function error(string $message): void
    {
        $this->errors[] = $message;
    }

    public function success(string $message): void
    {
        $this->successes[] = $message;
    }
}
