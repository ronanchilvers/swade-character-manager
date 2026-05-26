<?php

declare(strict_types=1);

return [
    'schema_version' => '1.0',
    'source_pdf' => 'Lankhmar: City of Thieves',
    'entry_type' => 'hindrance',
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
            'id' => 'amorous',
            'name' => 'Amorous',
            'levels' => [
                'minor',
            ],
            'summary' => 'The hero is easily distracted by attractive people.',
            'requirements' => [],
            'effects' => [
                [
                    'level' => 'minor',
                    'type' => 'modifier',
                    'polarity' => 'penalty',
                    'target' => 'trait.resist_tricks_or_tests_of_will',
                    'operator' => 'subtract',
                    'value' => 2,
                    'conditions' => [
                        'source_has_attractive_edge',
                    ],
                    'details' => 'Subtract 2 from rolls to resist Tricks and Tests of Will made by a character with Attractive.',
                ],
                [
                    'level' => 'minor',
                    'type' => 'modifier',
                    'polarity' => 'penalty',
                    'target' => 'trait.resist_tricks_or_tests_of_will',
                    'operator' => 'subtract',
                    'value' => 4,
                    'conditions' => [
                        'source_has_very_attractive_edge',
                    ],
                    'details' => 'Subtract 4 instead if the opposing character has Very Attractive.',
                ],
            ],
            'notes' => [
                'The player chooses the gender or genders the character is attracted to.',
            ],
            'source_pages' => [
                12,
            ],
        ],
        [
            'id' => 'cocky',
            'name' => 'Cocky',
            'levels' => [
                'minor',
            ],
            'summary' => 'The hero must show off and belittle foes before fighting effectively.',
            'requirements' => [],
            'effects' => [
                [
                    'level' => 'minor',
                    'type' => 'modifier',
                    'polarity' => 'penalty',
                    'target' => 'action.all',
                    'operator' => 'subtract',
                    'value' => 2,
                    'conditions' => [
                        'in_combat',
                        'until_full_round_spent_demeaning_foe',
                    ],
                    'details' => 'Subtract 2 from all actions in combat until the character spends a full round lecturing, gloating, or otherwise demeaning an enemy.',
                ],
            ],
            'notes' => [
                'The character may make a Test of Wills while delivering the speech.',
            ],
            'source_pages' => [
                12,
            ],
        ],
        [
            'id' => 'lankhmar_obligation',
            'name' => 'Obligation',
            'levels' => [
                'minor',
                'major',
            ],
            'summary' => 'The hero has responsibilities that cannot be ignored.',
            'requirements' => [],
            'effects' => [],
            'notes' => [
                'Minor Obligations usually constrain the character time or choices.',
                'Major Obligations are crucial, hazardous, and carry consequences when neglected.',
            ],
            'source_pages' => [
                12,
            ],
        ],
        [
            'id' => 'lankhmar_impulsive',
            'name' => 'Impulsive',
            'levels' => [
                'minor',
            ],
            'summary' => 'The hero jumps into situations without hesitation or foreknowledge.',
            'requirements' => [],
            'effects' => [],
            'notes' => [
                'This is the Lankhmar Minor version of Impulsive.',
            ],
            'source_pages' => [
                13,
            ],
        ],
        [
            'id' => 'jingoistic',
            'name' => 'Jingoistic',
            'levels' => [
                'minor',
                'major',
            ],
            'summary' => 'The hero openly favors their own culture and demeans outsiders.',
            'requirements' => [],
            'effects' => [
                [
                    'level' => 'minor',
                    'type' => 'modifier',
                    'polarity' => 'penalty',
                    'target' => 'charisma.other_cultures',
                    'operator' => 'subtract',
                    'value' => 2,
                    'conditions' => [],
                    'details' => 'Minor Jingoistic gives -2 Charisma among other cultures.',
                ],
                [
                    'level' => 'major',
                    'type' => 'modifier',
                    'polarity' => 'penalty',
                    'target' => 'charisma.other_cultures',
                    'operator' => 'subtract',
                    'value' => 4,
                    'conditions' => [],
                    'details' => 'Major Jingoistic gives -4 Charisma among other cultures.',
                ],
                [
                    'level' => 'minor',
                    'type' => 'restriction',
                    'polarity' => 'penalty',
                    'target' => 'edge.command.foreigners',
                    'operator' => 'fail',
                    'value' => true,
                    'conditions' => [
                        'until_worked_together_about_one_week',
                    ],
                    'details' => 'Command Edges cannot be used with foreigners until the character has worked with them for about a week.',
                ],
            ],
            'notes' => [],
            'source_pages' => [
                13,
            ],
        ],
        [
            'id' => 'one_hand',
            'name' => 'One Hand',
            'levels' => [
                'minor',
            ],
            'summary' => 'The hero is missing one hand.',
            'requirements' => [],
            'effects' => [
                [
                    'level' => 'minor',
                    'type' => 'modifier',
                    'polarity' => 'penalty',
                    'target' => 'action.requires_missing_hand',
                    'operator' => 'subtract',
                    'value' => 4,
                    'conditions' => [],
                    'details' => 'Subtract 4 from actions requiring only the missing hand.',
                ],
                [
                    'level' => 'minor',
                    'type' => 'modifier',
                    'polarity' => 'penalty',
                    'target' => 'action.requires_both_hands',
                    'operator' => 'subtract',
                    'value' => 2,
                    'conditions' => [],
                    'details' => 'Subtract 2 from actions requiring both hands.',
                ],
                [
                    'level' => 'minor',
                    'type' => 'modifier',
                    'polarity' => 'benefit',
                    'target' => 'one_hand.socket_penalties',
                    'operator' => 'replace',
                    'value' => '-2 fine actions / -1 both hands',
                    'conditions' => [
                        'using_socket_tool',
                    ],
                    'details' => 'A 5 rilk socket tool reduces the fine-action penalty to -2 and both-hands penalty to -1.',
                ],
            ],
            'notes' => [
                'A socket can hold a small tool or weapon such as a dagger or hook.',
                'Actions that do not require fine manipulation, such as Fighting with an attached weapon, suffer no penalty.',
            ],
            'source_pages' => [
                13,
            ],
        ],
    ],
];
