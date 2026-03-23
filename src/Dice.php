<?php

declare(strict_types=1);

namespace App;

class Dice
{
    private const VALID_SIZES = [4,6,8,10,12];

    public static function validSizes(): array
    {
        return static::VALID_SIZES;
    }

    public static function isValid($size): bool
    {
        return in_array($size, self::VALID_SIZES);
    }
}
