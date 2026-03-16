<?php

declare(strict_types=1);

namespace App\Entity;

use flight\database\SimplePdo;
use Ronanchilvers\Utility\Str;

abstract class Factory
{
    private $tableName = null;
    private $prefix = null;

    public function __construct(
        protected SimplePdo $pdo,
        protected Validator $validator
    ) {
    }

    abstract protected function getValidationRules(): array;

    protected function getTableName(): string
    {
        if (is_null($this->tableName)) {
            $class = explode('\\', get_called_class());
            $this->tableName = Str::snake(
                Str::plural(array_last($class))
            );
        }

        return $this->tableName;
    }

    protected function getPrefix(): string
    {
        if (is_null($this->prefix)) {
            $class = explode('\\', get_called_class());
            $this->prefix = Str::snake(array_last($class)) . '_';
        }

        return $this->prefix;
    }

    protected function prefix(string $string): string
    {
        $prefix = $this->getPrefix();
        if (str_starts_with($string, $prefix)) {
            return $string;
        }

        return sprintf(
            "%s%s",
            $prefix,
            $string
        );
    }

    protected function unprefix(string $string): string
    {
        $prefix = $this->getPrefix();

        return str_replace($prefix, '', $string);
    }

    public function validate(array $data): array
    {
        return $this->validator->validate(
            $data,
            $this->getValidationRules(),
        );
    }

    public function all(): array
    {
        $data = $this->pdo->fetchAll(sprintf(
            "select * from %s",
            $this->getTableName(),
        ));
        if (empty($data)) {
            return [];
        }
        $result = [];
        foreach ($data as $collection) {
            $row = [];
            foreach ($collection as $key => $value) {
                $row[$this->unprefix($key)] = $value;
            }
            $result[] = $row;
        }

        return $result;
    }

    public function insert($data): ?string
    {
        $values = [];
        foreach ($data as $key => $value) {
            $values[$this->prefix($key)] = $value;
        }
        $hash = Str::token(32);
        $values[$this->prefix('hash')] = $hash;

        try {
            $this->pdo->insert(
                $this->getTableName(),
                $values
            );

            return (string) $hash;
        } catch (\Exception $e) {
            return null;
        }
    }
}
