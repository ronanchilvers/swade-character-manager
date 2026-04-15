<?php

declare(strict_types=1);

namespace App\Budget;

use App\Budget;
use App\Dice;
use App\Entity;

class SkillBudget extends Budget
{
    protected function init(Entity $character, array $existing): void
    {
        $this->label = "Skills";
        $this->id = 'skills';
        $this->max = 12;
        $this->value = 0;
        $diceSizes = Dice::validSizes();
        foreach ($existing as $skill) {
            $attribute = $character->get($skill->attribute);
            $skillCost = 'yes' == $skill->core ? -1 : 0;
            foreach ($diceSizes as $size) {
                if ($size > $skill->die) {
                    break;
                }
                $skillCost += ($size > $attribute) ? 2 : 1;
            }
            $this->value += $skillCost;
        }
    }
}
