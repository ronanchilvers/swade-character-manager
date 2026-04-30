<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity;
use App\Entity\Factory\Result;
use flight\database\SimplePdo;
use Ronanchilvers\Utility\Str;
use Throwable;

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

    public function validate(Entity $entity): array
    {
        $data = $entity->toArray();

        return $this->validator->validate(
            $data,
            $this->getValidationRules(),
        );
    }

    public function find(?string $where = null, array $params = [], ?string $order = null): array
    {
        $sql = [];
        $sql[] = sprintf(
            "SELECT * FROM %s",
            $this->getTableName(),
        );
        if (!is_null($where)) {
            $sql[] = "WHERE {$where}";
        }
        if (!is_null($order)) {
            $sql[] = "ORDER BY {$order}";
        }
        $sql = implode(' ', $sql);
        $data = $this->pdo->fetchAll(
            $sql,
            $params
        );
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

    public function insert(Entity $entity): Result
    {
        $this->beforeInsert($entity);
        $data = $entity->toArray();
        $values = [];
        foreach ($data as $key => $value) {
            $values[$this->prefix($key)] = $value;
        }

        try {
            $this->pdo->transaction(function (SimplePdo $pdo) use ($entity, $values): void {
                $id = $pdo->insert(
                    $this->getTableName(),
                    $values
                );
                $entity->id = $id;
                $this->afterInsert($entity);
            });

            return new Result();
        } catch (Throwable $ex) {
            return new Result()->addError($ex->getMessage());
        }
    }

    public function update(Entity $entity): Result
    {
        $this->beforeUpdate($entity);
        $id = $entity->id;
        $data = $entity->toArray();
        $values = [];
        foreach ($data as $key => $value) {
            if ('id' == $key) {
                continue;
            }
            $values[$this->prefix($key)] = $value;
        }

        try {
            $id = $this->pdo->update(
                $this->getTableName(),
                $values,
                $this->prefix('id') . ' = ?',
                [$id]
            );

            return new Result();
        } catch (\Exception $ex) {
            return new Result()->addError($ex->getMessage());
        }
    }

    public function upsert(Entity $entity): Result
    {
        if (isset($entity->id)) {
            return $this->update($entity);
        }

        return $this->insert($entity);
    }

    protected function beforeInsert(Entity $entity): void
    {
    }

    protected function afterInsert(Entity $entity): void
    {
    }

    protected function beforeUpdate(Entity $entity): void
    {
    }
}
