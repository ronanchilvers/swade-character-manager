<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity;
use App\Entity\Factory\Skill;
use App\Filter;
use flight\database\SimplePdo;

class CharacterSkills
{
    private const CORE_SKILLS = [
        'athletics',
        'common_knowledge',
        'notice',
        'persuasion',
        'stealth',
    ];

    private const ATTRIBUTE_ORDER = [
        'Agility',
        'Smarts',
        'Spirit',
        'Strength',
        'Vigor',
    ];

    private const DIE_STEPS = [4, 6, 8, 10, 12];
    private const SKILL_OPTIONS = [0, 4, 6, 8, 10, 12];
    private const CORE_SKILL_OPTIONS = [4, 6, 8, 10, 12];
    private const DEFAULT_ATTRIBUTE_DIE = 4;
    private const TOTAL_SKILL_POINTS = 12;

    public function __construct(
        private SimplePdo $pdo,
        private GameData $gameData,
        private Skill $skills,
    ) {
    }

    public function viewData(Entity $character): array
    {
        $selected = $this->selectionForView($this->selectedForCharacter((int) $character->id));
        $validation = $this->validateSelectionMap($selected);
        $allocation = $this->allocationFor($character, $selected);
        $formErrors = $validation['form_errors'];

        if ($allocation['skill_points_spent'] > $allocation['skill_points_total']) {
            $formErrors[] = $this->overspendMessage(
                $allocation['skill_points_spent'],
                $allocation['skill_points_total']
            );
        }

        return $this->result($character, $selected, $validation['errors'], $formErrors, $allocation);
    }

    public function processSubmission(Entity $character, array $submitted): array
    {
        $selected = $this->selectionForView($this->normalizeSelectionMap($submitted));
        $validation = $this->validateSelectionMap($selected);
        $allocation = $this->allocationFor($character, $selected);
        $formErrors = $validation['form_errors'];

        if (
            empty($validation['errors']) &&
            empty($formErrors) &&
            $allocation['skill_points_spent'] > $allocation['skill_points_total']
        ) {
            $formErrors[] = $this->overspendMessage(
                $allocation['skill_points_spent'],
                $allocation['skill_points_total']
            );
        }

        if (empty($validation['errors']) && empty($formErrors)) {
            try {
                $this->replaceForCharacter((int) $character->id, $selected);
            } catch (\Throwable $exception) {
                $formErrors[] = 'Unable to save skills right now.';
            }
        }

        return $this->result($character, $selected, $validation['errors'], $formErrors, $allocation);
    }

    private function result(
        Entity $character,
        array $selected,
        array $errors,
        array $formErrors,
        array $allocation
    ): array {
        return [
            'entity' => $character,
            'errors' => $errors,
            'form_errors' => $formErrors,
            'skill_groups' => $this->skillGroups($character, $selected),
            'allocation' => $allocation,
        ];
    }

    private function selectedForCharacter(int $characterId): array
    {
        $selected = [];

        foreach ($this->skills->forCharacter($characterId) as $row) {
            $selected[$row->key] = (int) $row->die;
        }

        return $selected;
    }

    private function normalizeSelectionMap(array $submitted): array
    {
        $selected = [];

        foreach ($submitted as $key => $die) {
            $key = trim((string) $key);
            if ($key === '') {
                continue;
            }

            $selected[$key] = Filter::number($die);
        }

        return $selected;
    }

    private function selectionForView(array $selected): array
    {
        return array_replace($this->baselineSelection(), $selected);
    }

    private function baselineSelection(): array
    {
        return array_fill_keys(self::CORE_SKILLS, 4);
    }

    private function validateSelectionMap(array $selected): array
    {
        $errors = [];
        $formErrors = [];

        foreach ($selected as $key => $die) {
            $skill = $this->gameData->skill($key);
            if ($skill === null) {
                $formErrors[] = sprintf('Unknown skill selected: %s.', $key);
                continue;
            }

            if (!in_array((int) $die, $this->optionsForSkill($skill), true)) {
                $errors[] = $key;
            }
        }

        return [
            'errors' => array_values(array_unique($errors)),
            'form_errors' => array_values(array_unique($formErrors)),
        ];
    }

    private function skillGroups(Entity $character, array $selected): array
    {
        $grouped = [];
        foreach (self::ATTRIBUTE_ORDER as $attribute) {
            $grouped[$attribute] = [];
        }

        foreach ($this->gameData->allSkills() as $skill) {
            $attribute = (string) ($skill['linked_attribute'] ?? '');
            if (!array_key_exists($attribute, $grouped)) {
                $grouped[$attribute] = [];
            }

            $grouped[$attribute][] = [
                'id' => $skill['id'],
                'name' => $skill['name'],
                'summary' => $skill['summary'] ?? null,
                'core_skill' => (bool) ($skill['core_skill'] ?? false),
                'selected_die' => (int) ($selected[$skill['id']] ?? 0),
                'options' => $this->optionsForSkill($skill),
            ];
        }

        $groups = [];
        foreach ($grouped as $attribute => $skills) {
            if (empty($skills)) {
                continue;
            }

            $groups[] = [
                'attribute' => $attribute,
                'attribute_die' => $this->attributeDie($character, $attribute),
                'skills' => $skills,
            ];
        }

        return $groups;
    }

    private function optionsForSkill(array $skill): array
    {
        if (!empty($skill['core_skill'])) {
            return self::CORE_SKILL_OPTIONS;
        }

        return self::SKILL_OPTIONS;
    }

    private function allocationFor(Entity $character, array $selected): array
    {
        $pointsSpent = 0;

        foreach ($selected as $key => $die) {
            $skill = $this->gameData->skill($key);
            if ($skill === null) {
                continue;
            }

            $pointsSpent += $this->pointsForSkill(
                (int) $die,
                $this->attributeDie($character, (string) ($skill['linked_attribute'] ?? '')),
                !empty($skill['core_skill']) ? 4 : 0
            );
        }

        return [
            'skill_points_total' => self::TOTAL_SKILL_POINTS,
            'skill_points_spent' => $pointsSpent,
            'skill_points_remaining' => max(0, self::TOTAL_SKILL_POINTS - $pointsSpent),
        ];
    }

    private function pointsForSkill(int $die, int $attributeDie, int $baselineDie): int
    {
        if (!in_array($die, self::DIE_STEPS, true)) {
            return 0;
        }

        $points = 0;

        foreach (self::DIE_STEPS as $stepDie) {
            if ($stepDie <= $baselineDie || $stepDie > $die) {
                continue;
            }

            $points += $stepDie <= $attributeDie ? 1 : 2;
        }

        return $points;
    }

    private function attributeDie(Entity $character, string $attribute): int
    {
        $field = strtolower($attribute);
        $value = (int) ($character->{$field} ?? self::DEFAULT_ATTRIBUTE_DIE);

        if (!in_array($value, self::DIE_STEPS, true)) {
            return self::DEFAULT_ATTRIBUTE_DIE;
        }

        return $value;
    }

    private function replaceForCharacter(int $characterId, array $selected): void
    {
        $this->pdo->transaction(function (SimplePdo $pdo) use ($characterId, $selected): void {
            $pdo->runQuery(
                'DELETE FROM skills WHERE skill_character_id = ?',
                [$characterId]
            );

            foreach ($selected as $key => $die) {
                if ((int) $die <= 0) {
                    continue;
                }

                $entity = new Entity([
                    'character_id' => $characterId,
                    'key' => $key,
                    'die' => (int) $die,
                ]);

                if (!empty($this->skills->validate($entity))) {
                    throw new \RuntimeException('Invalid skill row');
                }

                if (!$this->skills->insert($entity)) {
                    throw new \RuntimeException('Unable to insert skill row');
                }
            }
        });
    }

    private function overspendMessage(int $requiredPoints, int $availablePoints): string
    {
        return sprintf(
            'These skills require %d skill points, but only %d are available.',
            $requiredPoints,
            $availablePoints
        );
    }
}
