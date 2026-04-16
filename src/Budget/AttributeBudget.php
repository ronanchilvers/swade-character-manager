<?php

declare(strict_types=1);

namespace App\Budget;

use App\Budget;
use App\Entity;
use App\Entity\Factory\Hindrance;

class AttributeBudget extends Budget
{
    protected function init(Entity $character, array $existing): void
    {
        $this->label = "Attributes";
        $this->id = 'attributes';
        $this->max = 5;
        $this->value = 0;
        foreach ($existing as $hindrance) {
            $this->value += "major" == $hindrance->level ? 2 : 1;
        }
    }
}
