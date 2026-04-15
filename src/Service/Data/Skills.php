<?php

declare(strict_types=1);

namespace App\Service\Data;

use App\Service\Data;
use ArrayIterator;

class Skills extends Data
{
    protected ?array $core = null;
    protected ?array $nonCore = null;

    public function core()
    {
        if (!is_array($this->core)) {
            $this->processSkills();
        }

        return $this->core;
    }

    public function nonCore()
    {
        if (!is_array($this->nonCore)) {
            $this->processSkills();
        }

        return $this->nonCore;
    }

    public function attributeForSkill(string $skill): ?string
    {
        $iterator = new ArrayIterator($this->all());
        foreach ($iterator as $entry) {
            if ($entry['id'] == $skill) {
                return $entry['linked_attribute'];
            }
        }

        return null;
    }

    protected function processSkills(): void
    {
        $skills = $this->all();
        $this->core = $this->nonCore = [];
        foreach ($skills as $skill) {
            if ($skill['core_skill']) {
                $this->core[$skill['id']] = $skill;
                continue;
            }
            $this->nonCore[$skill['id']] = $skill;
        }
    }
}
