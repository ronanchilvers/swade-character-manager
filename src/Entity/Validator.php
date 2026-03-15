<?php

declare(strict_types=1);

namespace App\Entity;

class Validator
{
    public function validate(array $data, array $rules): array
    {
        $errors = [];
        foreach ($data as $key => $value) {
            if (!array_key_exists($key, $rules)) {
                continue;
            }
            if (!$rules[$key]->isValid($value)) {
                $errors[] = $key;
            }
        }

        return $errors;
    }
}
