<?php

declare(strict_types=1);

namespace App\Service\Data;

use App\Service\Data;
use flight\database\SimplePdo;
use RuntimeException;

class Manager
{
    private const DATABASE_AWARE_TYPES = [
        Hindrances::class,
        Skills::class,
    ];

    protected string $dataDir;
    protected array $types = [];
    protected array $data = [];

    public function __construct(
        string $dataDir,
        private ?SimplePdo $pdo = null,
    ) {
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
            $this->data[$class] = in_array($class, self::DATABASE_AWARE_TYPES, true)
                ? new $class($this->dataDir, $this->pdo)
                : new $class($this->dataDir);
        }

        return $this->data[$class];
    }
}
