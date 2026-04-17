<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Dice;
use PHPUnit\Framework\TestCase;

class DiceTest extends TestCase
{
    public function testValidSizesReturnsExpectedList(): void
    {
        self::assertSame([4, 6, 8, 10, 12], Dice::validSizes());
    }

    public function testIsValidRecognizesAllowedAndDisallowedSizes(): void
    {
        self::assertTrue(Dice::isValid(4));
        self::assertTrue(Dice::isValid(12));
        self::assertFalse(Dice::isValid(5));
        self::assertFalse(Dice::isValid('4'));
    }
}
