<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity;
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
            $result[] = new Entity($row);
        }

        return $result;
    }

    public function one(string $where, array $params = []): ?Entity
    {
        try {
            $sql = "SELECT * FROM {$this->getTableName()} WHERE {$where}";
            $values = $this->pdo->fetchRow(
                $sql,
                $params
            );
            if (is_null($values) || 0 == count($values)) {
                return null;
            }
            $data = [];
            foreach ($values as $key => $value) {
                $data[$this->unprefix($key)] = $value;
            }

            return new Entity($data);
        } catch (\Exception $ex) {
            return null;
        }
    }

    public function insert(Entity $entity): bool
    {
        $data = $entity->toArray();
        $values = [];
        $this->beforeInsert($entity);
        foreach ($data as $key => $value) {
            $values[$this->prefix($key)] = $value;
        }

        try {
            $id = $this->pdo->insert(
                $this->getTableName(),
                $values
            );
            $entity->id = $id;

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function beforeInsert(Entity $entity): void
    {
    }
}
