<?php

declare(strict_types=1);

return [
    'schema_version' => '1.0',
    'source_pdf' => 'Lankhmar: City of Thieves',
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
            'id' => 'lankhmar_spellcasting',
            'name' => 'Spellcasting',
            'linked_attribute' => 'smarts',
            'core_skill' => false,
            'arcane_background' => 'Lankhmar Sorcery',
            'summary' => 'Arcane skill used for Black Magic, Elemental Magic, and White Magic in Lankhmar.',
            'requirements' => [],
            'effects' => [],
            'notes' => [
                'Lankhmar uses Spellcasting (Smarts) for all arcane traditions.',
                'Power Points are not used; powers apply Casting Modifiers instead.',
            ],
            'source_pages' => [
                27,
            ],
        ],
        [
            'id' => 'knowledge_arcana',
            'name' => 'Knowledge (Arcana)',
            'linked_attribute' => 'smarts',
            'core_skill' => false,
            'arcane_background' => null,
            'summary' => 'Knowledge of magical history, arcane theory, potion making, common spell effects, and rituals.',
            'requirements' => [],
            'effects' => [],
            'notes' => [
                'Used by several Lankhmar Power Edges and for deciphering unknown rituals.',
            ],
            'source_pages' => [
                27,
                32,
            ],
        ],
        [
            'id' => 'knowledge_navigation',
            'name' => 'Knowledge (Navigation)',
            'linked_attribute' => 'smarts',
            'core_skill' => false,
            'arcane_background' => null,
            'summary' => 'Knowledge of routes, direction, and navigation.',
            'requirements' => [],
            'effects' => [],
            'notes' => [
                'Ratlings gain a bonus to this skill from Direction Sense.',
            ],
            'source_pages' => [
                12,
            ],
        ],
        [
            'id' => 'knowledge_religion',
            'name' => 'Knowledge (Religion)',
            'linked_attribute' => 'smarts',
            'core_skill' => false,
            'arcane_background' => null,
            'summary' => 'Knowledge of gods, cults, rites, and religious customs.',
            'requirements' => [],
            'effects' => [],
            'notes' => [
                'Required by Priest/Priestess and Arcane Zealot.',
            ],
            'source_pages' => [
                18,
            ],
        ],
    ],
];
