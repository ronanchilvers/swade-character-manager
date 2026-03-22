<?php

declare(strict_types=1);

namespace App\Twig;

use Flight;

class FieldExtension extends \Twig\Extension\AbstractExtension
{
    public function getFunctions()
    {
        return [
            // Field helpers
            new \Twig\TwigFunction('field_has_error', [$this, 'fieldHasError']),
        ];
    }

    public function fieldHasError(string $field, array $errors): bool
    {
        return in_array($field, $errors);
    }
}
