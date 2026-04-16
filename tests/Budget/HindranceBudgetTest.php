<?php

declare(strict_types=1);

namespace Tests\Budget;

use App\Budget\HindranceBudget;
use App\Entity;
use PHPUnit\Framework\TestCase;

class HindranceBudgetTest extends TestCase
{
    public function testHindranceBudgetTotalsMinorAndMajorSelections(): void
    {
        $budget = new HindranceBudget(
            new Entity(),
            [
                new Entity(['level' => 'minor']),
                new Entity(['level' => 'major']),
                new Entity(['level' => 'minor']),
            ]
        );

        self::assertSame('hindrances', $budget->id);
        self::assertSame('Hindrances', $budget->label);
        self::assertSame(4, $budget->max);
        self::assertSame(4, $budget->value);
    }
}
