<?php

declare(strict_types=1);

namespace App\Budget;

use App\Budget;
use App\Entity;
use App\Entity\Factory\Hindrance;

class HindranceBudget extends Budget
{
    protected function init(Entity $character, array $existing): void
    {
        $this->label = "Hindrances";
        $this->id = 'hindrances';
        $this->max = Hindrance::MAX_POINTS;
        $this->value = 0;
        foreach ($existing as $hindrance) {
            $this->value += "major" == $hindrance->level ? 2 : 1;
        }
    }
}
