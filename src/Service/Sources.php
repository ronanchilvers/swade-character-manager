<?php

declare(strict_types=1);

namespace App\Service;

class Sources
{
    static private array $sources = [
        'core' => 'Core Rules (Always Enabled)',
        'fantasy' => 'Savage Worlds Fantasy Companion',
        'lankhmar' => 'Lankhmar: City of Thieves',
        // 'after_the_end' => 'After The End',
        // 'library_between_worlds' => 'The Library Between Worlds',
    ];

    public static function all(): array
    {
        return static::$sources;
    }

    public static function filter(array $input): array
    {
        $filtered = [];
        foreach ($input as $key) {
            if ('core' == $key) {
                continue;
            }
            if (isset(static::$sources[$key])) {
                $filtered[] = $key;
            }
        }
        array_unshift($filtered, 'core');

        return $filtered;
    }
}
