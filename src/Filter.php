<?php

declare(strict_types=1);

namespace App;

class Filter
{
    public static function alpha(mixed $input): string
    {
        return (string) preg_replace("/[^A-z]/", "", $input);
    }

    public static function alphaArray(array $input): array
    {
        $output = [];
        foreach ($input as $key => $value) {
            $output[$key] = static::alpha($value);
        }

        return $output;
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
        return (int) preg_replace('/[^0-9]/', "", (string) $input);
    }

    public static function numberArray(array $input, array $valid = []): array
    {
        $output = [];
        foreach ($input as $key => $value) {
            $value = static::number($value);
            if (!empty($valid) && !in_array($value, $valid)) {
                continue;
            }
            $output[$key] = $value;
        }

        return $output;
    }
}
