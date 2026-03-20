<?php

declare(strict_types=1);

namespace App;

class Filter
{
    public static function alpha(mixed $input): string
    {
        return (string) preg_replace("/[^A-z]/", "", $input);
    }

    public static function alnum(mixed $input): string
    {
        return (string) preg_replace("/[^A-z0-9]/", "", $input);
    }

    public static function noTags(mixed $input): string
    {
        return (string) strip_tags($input);
    }

    public static function number(mixed $input): int
    {
        return (int) preg_replace('/[^0-9]', "", $input);
    }
}
