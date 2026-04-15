<?php

declare(strict_types=1);

namespace App;

abstract class Budget
{
    public protected(set) string $id;
    public protected(set) int $value;
    public protected(set) int $max;
    public protected(set) string $label;

    public function __construct(Entity $character, array $existing)
    {
        $this->init($character, $existing);
    }

    abstract protected function init(Entity $character, array $existing): void;
}
