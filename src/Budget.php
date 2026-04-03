<?php

declare(strict_types=1);

namespace App;

class Budget
{
    protected $data = [];

    public function add(
        string $id,
        string $label,
        int $value,
        ?int $max = null
    ): static {
        $this->data[$id] = [
            'label' => $label,
            'value' => $value,
            'max'   => $max,
        ];

        return $this;
    }

    public function maxFor(string $id): ?int
    {
        if (!isset($this->data[$id])) {
            return null;
        }

        return $this->data[$id]['max'];
    }

    public function all(): array
    {
        return $this->data;
    }
}
