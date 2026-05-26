<?php

declare(strict_types=1);

return [
    'schema_version' => '1.0',
    'source_pdf' => 'SWADE Fantasy Companion',
    'entry_type' => 'skill',
    'schema' => [
        'requirements' => [
            'type' => 'rank | attribute | trait | edge | wild_card | arcane_background | hindrance_any_of | special',
            'target' => 'thing being required',
            'value' => 'required value',
        ],
        'effects' => [
            'level' => 'minor | major | base | null',
            'type' => 'modifier | resource | reroll | restriction | grant | damage | action | status | special',
            'polarity' => 'benefit | penalty | mixed | neutral',
            'target' => 'normalized rules target',
            'operator' => 'add | subtract | set | ignore | grant | reroll | draw | recover | replace | double | halve | fail | break',
            'value' => 'number | string | boolean | null',
            'conditions' => [
                'optional condition tags',
            ],
            'details' => 'human-readable clarification',
        ],
    ],
    'entries' => [
        [
            'id' => 'fantasy_alchemy',
            'name' => 'Alchemy',
            'linked_attribute' => 'smarts',
            'core_skill' => false,
            'arcane_background' => 'Alchemist',
            'summary' => 'Arcane skill for alchemists and a practical skill for crafting or examining alchemical substances.',
            'requirements' => [],
            'effects' => [],
            'notes' => [
                'Used by Arcane Background (Alchemist).',
                'Can be used to craft alchemical items.',
                'Can substitute for Science when examining chemical reactions, reagents, and related topics.',
                'Animal Handling is not a separate Fantasy Companion skill; the text uses Intimidation or Persuasion for animal handling attempts.',
            ],
            'source_pages' => [
                31,
                90,
            ],
        ],
    ],
];
