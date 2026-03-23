<?php

declare(strict_types=1);

namespace App\Entity\Factory;

class Result
{
    public function __construct(
        public array $errors = [],
    ) {
    }

    public function isSuccess(): bool
    {
        return 0 == count($this->errors);
    }

    public function addError(string $error): static
    {
        $this->errors[] = $error;

        return $this;
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
