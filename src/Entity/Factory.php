<?php

declare(strict_types=1);

namespace App\Entity;

use flight\database\SimplePdo;
use Ronanchilvers\Utility\Str;

class Factory
{
    public function __construct(protected SimplePdo $pdo)
    {
    }

    protected function getTableName(): string
    {
        $class = explode('\\', get_called_class());

        return Str::snake(
            Str::plural(array_last($class))
        );
    }

    protected function getPrefix(): string
    {
        $class = explode('\\', get_called_class());

        return Str::snake(array_last($class)) . '_';
    }

    public function insert($data)
    {
        $prefix = $this->getPrefix();
        $values = [];
        foreach ($data as $key => $value) {
            $values[$prefix . $key] = $value;
        }

        return $this->pdo->insert(
            $this->getTableName(),
            $values
        );
    }
}
