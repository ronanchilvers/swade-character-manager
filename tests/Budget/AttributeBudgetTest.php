<?php

declare(strict_types=1);

namespace Tests\Budget;

use App\Budget\AttributeBudget;
use App\Entity;
use PHPUnit\Framework\TestCase;

class AttributeBudgetTest extends TestCase
{
    public function testAttributeBudgetUsesHindrancePointsAsCurrentImplementationDoes(): void
    {
        $budget = new AttributeBudget(
            new Entity(),
            [
                new Entity(['level' => 'minor']),
                new Entity(['level' => 'major']),
            ]
        );

        self::assertSame('attributes', $budget->id);
        self::assertSame('Attributes', $budget->label);
        self::assertSame(5, $budget->max);
        self::assertSame(3, $budget->value);
    }
}
