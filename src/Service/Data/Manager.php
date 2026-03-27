<?php

declare(strict_types=1);

namespace App\Service\Data;

use App\Service\Data;
use RuntimeException;

class Manager
{
    protected string $dataDir;
    protected array $types = [];
    protected array $data = [];

    public function __construct(string $dataDir)
    {
        $this->dataDir = $dataDir;
    }

    public function addType(
        string $class
    ): static {
        $this->types[$class] = $class;

        return $this;
    }

    public function getType(string $class): Data
    {
        if (!isset($this->types[$class])) {
            throw new RuntimeException('Unregistered data class ' . $class);
        }
        if (!isset($this->data[$class])) {
            $this->data[$class] = new $class($this->dataDir);
        }

        return $this->data[$class];
    }
}
