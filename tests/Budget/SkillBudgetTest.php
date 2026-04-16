<?php

declare(strict_types=1);

namespace Tests\Budget;

use App\Budget\SkillBudget;
use App\Entity;
use PHPUnit\Framework\TestCase;

class SkillBudgetTest extends TestCase
{
    public function testCoreSkillAtD4CostsZeroPoints(): void
    {
        $budget = new SkillBudget(
            new Entity([
                'agility' => 6,
            ]),
            [
                new Entity([
                    'attribute' => 'agility',
                    'core' => 'yes',
                    'die' => 4,
                ]),
            ]
        );

        self::assertSame('skills', $budget->id);
        self::assertSame('Skills', $budget->label);
        self::assertSame(12, $budget->max);
        self::assertSame(0, $budget->value);
    }

    public function testSkillBudgetChargesTwoPointsForStepsAboveLinkedAttribute(): void
    {
        $budget = new SkillBudget(
            new Entity([
                'agility' => 6,
                'smarts' => 4,
            ]),
            [
                new Entity([
                    'attribute' => 'agility',
                    'core' => 'no',
                    'die' => 8,
                ]),
                new Entity([
                    'attribute' => 'smarts',
                    'core' => 'yes',
                    'die' => 6,
                ]),
            ]
        );

        self::assertSame(6, $budget->value);
    }
}
