<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity;
use App\Entity\Factory\Character as FactoryCharacter;
use App\Filter;

class CharacterAttributes
{
    private const ATTRIBUTE_FIELDS = [
        'agility' => 'Agility',
        'smarts' => 'Smarts',
        'spirit' => 'Spirit',
        'strength' => 'Strength',
        'vigor' => 'Vigor',
    ];

    private const ATTRIBUTE_OPTIONS = [4, 6, 8, 10, 12];
    private const DEFAULT_ATTRIBUTE_DIE = 4;
    private const BASE_ATTRIBUTE_POINTS = 5;
    private const HINDRANCE_POINTS_PER_ATTRIBUTE_STEP = 2;

    public function __construct(
        private FactoryCharacter $characters,
        private CharacterHindrances $characterHindrances,
    ) {
    }

    public function viewData(Entity $character): array
    {
        $availablePoints = $this->characterHindrances->selectedPointsForCharacter((int) $character->id);
        $allocation = $this->allocationFor($character, $availablePoints);
        $formErrors = [];

        if ($allocation['hindrance_points_spent'] > $allocation['hindrance_points_available']) {
            $formErrors[] = $this->overspendMessage(
                $allocation['hindrance_points_spent'],
                $allocation['hindrance_points_available']
            );
        }

        return [
            'entity' => $character,
            'errors' => [],
            'form_errors' => $formErrors,
            'attribute_fields' => self::ATTRIBUTE_FIELDS,
            'attribute_options' => self::ATTRIBUTE_OPTIONS,
            'allocation' => $allocation,
        ];
    }

    public function processSubmission(Entity $character, array $submitted): array
    {
        foreach (array_keys(self::ATTRIBUTE_FIELDS) as $field) {
            $character->{$field} = Filter::number($submitted[$field] ?? '');
        }

        $errors = $this->characters->validate($character);
        $availablePoints = $this->characterHindrances->selectedPointsForCharacter((int) $character->id);
        $allocation = $this->allocationFor($character, $availablePoints);
        $formErrors = [];

        if (
            empty($errors) &&
            $allocation['hindrance_points_spent'] > $allocation['hindrance_points_available']
        ) {
            $formErrors[] = $this->overspendMessage(
                $allocation['hindrance_points_spent'],
                $allocation['hindrance_points_available']
            );
        }

        if (
            empty($errors) &&
            empty($formErrors) &&
            !$this->characters->upsert($character)
        ) {
            $formErrors[] = 'Unable to save attributes right now.';
        }

        return [
            'entity' => $character,
            'errors' => $errors,
            'form_errors' => $formErrors,
            'attribute_fields' => self::ATTRIBUTE_FIELDS,
            'attribute_options' => self::ATTRIBUTE_OPTIONS,
            'allocation' => $allocation,
        ];
    }

    private function allocationFor(Entity $character, int $availableHindrancePoints): array
    {
        $stepsAboveDefault = 0;

        foreach (array_keys(self::ATTRIBUTE_FIELDS) as $field) {
            $stepsAboveDefault += $this->stepsForDie($this->attributeValue($character, $field));
        }

        $attributePointsSpent = min($stepsAboveDefault, self::BASE_ATTRIBUTE_POINTS);
        $extraAttributeSteps = max(0, $stepsAboveDefault - self::BASE_ATTRIBUTE_POINTS);
        $hindrancePointsSpent = $extraAttributeSteps * self::HINDRANCE_POINTS_PER_ATTRIBUTE_STEP;

        return [
            'attribute_points_total' => self::BASE_ATTRIBUTE_POINTS,
            'attribute_points_spent' => $attributePointsSpent,
            'attribute_points_remaining' => max(0, self::BASE_ATTRIBUTE_POINTS - $attributePointsSpent),
            'hindrance_points_available' => $availableHindrancePoints,
            'hindrance_points_spent' => $hindrancePointsSpent,
            'hindrance_points_remaining' => max(0, $availableHindrancePoints - $hindrancePointsSpent),
        ];
    }

    private function attributeValue(Entity $character, string $field): int
    {
        $value = (int) ($character->{$field} ?? self::DEFAULT_ATTRIBUTE_DIE);
        if (!in_array($value, self::ATTRIBUTE_OPTIONS, true)) {
            return self::DEFAULT_ATTRIBUTE_DIE;
        }

        return $value;
    }

    private function stepsForDie(int $die): int
    {
        return match ($die) {
            12 => 4,
            10 => 3,
            8 => 2,
            6 => 1,
            default => 0,
        };
    }

    private function overspendMessage(int $requiredPoints, int $availablePoints): string
    {
        return sprintf(
            'These attributes require %d hindrance points, but only %d are available from selected hindrances.',
            $requiredPoints,
            $availablePoints
        );
    }
}
