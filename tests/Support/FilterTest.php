<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Filter;
use PHPUnit\Framework\TestCase;

class FilterTest extends TestCase
{
    public function testAlphaAndAlnumStripInvalidCharacters(): void
    {
        self::assertSame('Abcz', Filter::alpha('A-b_c!z123'));
        self::assertSame('Abcz123', Filter::alnum('A-b_c!z123'));
    }

    public function testAlphaArrayFiltersAllValues(): void
    {
        $input = ['first' => 'One!', 'second' => 'Two 2'];

        self::assertSame(['first' => 'One', 'second' => 'Two'], Filter::alphaArray($input));
    }

    public function testNoTagsAndNumberFiltering(): void
    {
        self::assertSame('safe text', Filter::noTags('<b>safe</b> text'));
        self::assertSame(123, Filter::number('abc123def'));
    }

    public function testNumberArraySupportsOptionalWhitelist(): void
    {
        $input = ['a' => '1a', 'b' => '2b', 'c' => '3c'];

        self::assertSame(['a' => 1, 'b' => 2, 'c' => 3], Filter::numberArray($input));
        self::assertSame(['a' => 1, 'c' => 3], Filter::numberArray($input, [1, 3]));
    }
}
