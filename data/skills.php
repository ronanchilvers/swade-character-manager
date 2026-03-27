<?php

declare(strict_types=1);

return array (
  'schema_version' => '1.0',
  'source_pdf' => 'Savage Worlds Adventure Edition v5.pdf',
  'entry_type' => 'skill',
  'schema' => 
  array (
    'requirements' => 
    array (
      'type' => 'rank | attribute | trait | edge | wild_card | arcane_background | hindrance_any_of | special',
      'target' => 'thing being required',
      'value' => 'required value',
    ),
    'effects' => 
    array (
      'level' => 'minor | major | base | null',
      'type' => 'modifier | resource | reroll | restriction | grant | damage | action | status | special',
      'polarity' => 'benefit | penalty | mixed | neutral',
      'target' => 'normalized rules target',
      'operator' => 'add | subtract | set | ignore | grant | reroll | draw | recover | replace | double | halve | fail | break',
      'value' => 'number | string | boolean | null',
      'conditions' => 
      array (
        0 => 'optional condition tags',
      ),
      'details' => 'human-readable clarification',
    ),
  ),
  'entries' => 
  array (
    0 => 
    array (
      'id' => 'academics',
      'name' => 'Academics',
      'linked_attribute' => 'Smarts',
      'core_skill' => false,
      'arcane_background' => NULL,
      'summary' => 'Knowledge of liberal arts, social sciences, literature, history, and similar subjects.',
      'requirements' => 
      array (
      ),
      'effects' => 
      array (
      ),
      'notes' => 
      array (
      ),
      'source_pages' => 
      array (
        0 => 60,
      ),
    ),
    1 => 
    array (
      'id' => 'athletics',
      'name' => 'Athletics',
      'linked_attribute' => 'Agility',
      'core_skill' => true,
      'arcane_background' => NULL,
      'summary' => 'General athletic coordination and ability.',
      'requirements' => 
      array (
      ),
      'effects' => 
      array (
      ),
      'notes' => 
      array (
        0 => 'Covers climbing, jumping, balancing, wrestling, skiing, swimming, throwing, and catching.',
      ),
      'source_pages' => 
      array (
        0 => 60,
      ),
    ),
    2 => 
    array (
      'id' => 'battle',
      'name' => 'Battle',
      'linked_attribute' => 'Smarts',
      'core_skill' => false,
      'arcane_background' => NULL,
      'summary' => 'Knowledge of strategy, tactics, and military operations.',
      'requirements' => 
      array (
      ),
      'effects' => 
      array (
      ),
      'notes' => 
      array (
        0 => 'A key skill in Mass Battles.',
      ),
      'source_pages' => 
      array (
        0 => 60,
      ),
    ),
    3 => 
    array (
      'id' => 'boating',
      'name' => 'Boating',
      'linked_attribute' => 'Agility',
      'core_skill' => false,
      'arcane_background' => NULL,
      'summary' => 'Ability to sail or pilot boats, ships, and other watercraft.',
      'requirements' => 
      array (
      ),
      'effects' => 
      array (
      ),
      'notes' => 
      array (
      ),
      'source_pages' => 
      array (
        0 => 60,
      ),
    ),
    4 => 
    array (
      'id' => 'common_knowledge',
      'name' => 'Common Knowledge',
      'linked_attribute' => 'Smarts',
      'core_skill' => true,
      'arcane_background' => NULL,
      'summary' => 'General knowledge of the character’s world and culture.',
      'requirements' => 
      array (
      ),
      'effects' => 
      array (
      ),
      'notes' => 
      array (
      ),
      'source_pages' => 
      array (
        0 => 60,
      ),
    ),
    5 => 
    array (
      'id' => 'driving',
      'name' => 'Driving',
      'linked_attribute' => 'Agility',
      'core_skill' => false,
      'arcane_background' => NULL,
      'summary' => 'The ability to control, steer, and operate ground vehicles.',
      'requirements' => 
      array (
      ),
      'effects' => 
      array (
      ),
      'notes' => 
      array (
      ),
      'source_pages' => 
      array (
        0 => 60,
      ),
    ),
    6 => 
    array (
      'id' => 'electronics',
      'name' => 'Electronics',
      'linked_attribute' => 'Smarts',
      'core_skill' => false,
      'arcane_background' => NULL,
      'summary' => 'Use of electronic devices and systems.',
      'requirements' => 
      array (
      ),
      'effects' => 
      array (
      ),
      'notes' => 
      array (
      ),
      'source_pages' => 
      array (
        0 => 60,
      ),
    ),
    7 => 
    array (
      'id' => 'faith',
      'name' => 'Faith',
      'linked_attribute' => 'Spirit',
      'core_skill' => false,
      'arcane_background' => 'Miracles',
      'summary' => 'Arcane skill used by Arcane Background (Miracles).',
      'requirements' => 
      array (
      ),
      'effects' => 
      array (
      ),
      'notes' => 
      array (
      ),
      'source_pages' => 
      array (
        0 => 60,
      ),
    ),
    8 => 
    array (
      'id' => 'fighting',
      'name' => 'Fighting',
      'linked_attribute' => 'Agility',
      'core_skill' => false,
      'arcane_background' => NULL,
      'summary' => 'Skill in armed and unarmed combat.',
      'requirements' => 
      array (
      ),
      'effects' => 
      array (
      ),
      'notes' => 
      array (
      ),
      'source_pages' => 
      array (
        0 => 60,
      ),
    ),
    9 => 
    array (
      'id' => 'focus',
      'name' => 'Focus',
      'linked_attribute' => 'Spirit',
      'core_skill' => false,
      'arcane_background' => 'Gifted',
      'summary' => 'Arcane skill used by Arcane Background (Gifted).',
      'requirements' => 
      array (
      ),
      'effects' => 
      array (
      ),
      'notes' => 
      array (
      ),
      'source_pages' => 
      array (
        0 => 60,
      ),
    ),
    10 => 
    array (
      'id' => 'gambling',
      'name' => 'Gambling',
      'linked_attribute' => 'Smarts',
      'core_skill' => false,
      'arcane_background' => NULL,
      'summary' => 'Skill and familiarity with games of chance.',
      'requirements' => 
      array (
      ),
      'effects' => 
      array (
      ),
      'notes' => 
      array (
      ),
      'source_pages' => 
      array (
        0 => 60,
      ),
    ),
    11 => 
    array (
      'id' => 'hacking',
      'name' => 'Hacking',
      'linked_attribute' => 'Smarts',
      'core_skill' => false,
      'arcane_background' => NULL,
      'summary' => 'Coding, programming, and breaking into computer systems.',
      'requirements' => 
      array (
      ),
      'effects' => 
      array (
      ),
      'notes' => 
      array (
      ),
      'source_pages' => 
      array (
        0 => 60,
      ),
    ),
    12 => 
    array (
      'id' => 'healing',
      'name' => 'Healing',
      'linked_attribute' => 'Smarts',
      'core_skill' => false,
      'arcane_background' => NULL,
      'summary' => 'Treating and healing Wounds and disease, and reading forensic evidence.',
      'requirements' => 
      array (
      ),
      'effects' => 
      array (
      ),
      'notes' => 
      array (
      ),
      'source_pages' => 
      array (
        0 => 60,
      ),
    ),
    13 => 
    array (
      'id' => 'intimidation',
      'name' => 'Intimidation',
      'linked_attribute' => 'Spirit',
      'core_skill' => false,
      'arcane_background' => NULL,
      'summary' => 'Threatening others into doing what the character wants.',
      'requirements' => 
      array (
      ),
      'effects' => 
      array (
      ),
      'notes' => 
      array (
      ),
      'source_pages' => 
      array (
        0 => 60,
      ),
    ),
    14 => 
    array (
      'id' => 'language',
      'name' => 'Language',
      'linked_attribute' => 'Smarts',
      'core_skill' => false,
      'arcane_background' => NULL,
      'summary' => 'Knowledge and fluency in a specific language.',
      'requirements' => 
      array (
      ),
      'effects' => 
      array (
      ),
      'notes' => 
      array (
      ),
      'source_pages' => 
      array (
        0 => 60,
      ),
    ),
    15 => 
    array (
      'id' => 'notice',
      'name' => 'Notice',
      'linked_attribute' => 'Smarts',
      'core_skill' => true,
      'arcane_background' => NULL,
      'summary' => 'General awareness and perception.',
      'requirements' => 
      array (
      ),
      'effects' => 
      array (
      ),
      'notes' => 
      array (
      ),
      'source_pages' => 
      array (
        0 => 60,
      ),
    ),
    16 => 
    array (
      'id' => 'occult',
      'name' => 'Occult',
      'linked_attribute' => 'Smarts',
      'core_skill' => false,
      'arcane_background' => NULL,
      'summary' => 'Knowledge of supernatural creatures, events, history, and related lore.',
      'requirements' => 
      array (
      ),
      'effects' => 
      array (
      ),
      'notes' => 
      array (
      ),
      'source_pages' => 
      array (
        0 => 60,
      ),
    ),
    17 => 
    array (
      'id' => 'performance',
      'name' => 'Performance',
      'linked_attribute' => 'Spirit',
      'core_skill' => false,
      'arcane_background' => NULL,
      'summary' => 'Singing, dancing, acting, and other public expression.',
      'requirements' => 
      array (
      ),
      'effects' => 
      array (
      ),
      'notes' => 
      array (
      ),
      'source_pages' => 
      array (
        0 => 60,
      ),
    ),
    18 => 
    array (
      'id' => 'persuasion',
      'name' => 'Persuasion',
      'linked_attribute' => 'Spirit',
      'core_skill' => true,
      'arcane_background' => NULL,
      'summary' => 'Convincing others through reason, charm, deception, or incentives.',
      'requirements' => 
      array (
      ),
      'effects' => 
      array (
      ),
      'notes' => 
      array (
      ),
      'source_pages' => 
      array (
        0 => 60,
      ),
    ),
    19 => 
    array (
      'id' => 'piloting',
      'name' => 'Piloting',
      'linked_attribute' => 'Agility',
      'core_skill' => false,
      'arcane_background' => NULL,
      'summary' => 'Operating vehicles that move in three dimensions, such as aircraft and spacecraft.',
      'requirements' => 
      array (
      ),
      'effects' => 
      array (
      ),
      'notes' => 
      array (
      ),
      'source_pages' => 
      array (
        0 => 60,
      ),
    ),
    20 => 
    array (
      'id' => 'psionics',
      'name' => 'Psionics',
      'linked_attribute' => 'Smarts',
      'core_skill' => false,
      'arcane_background' => 'Psionics',
      'summary' => 'Arcane skill used by Arcane Background (Psionics).',
      'requirements' => 
      array (
      ),
      'effects' => 
      array (
      ),
      'notes' => 
      array (
      ),
      'source_pages' => 
      array (
        0 => 60,
      ),
    ),
    21 => 
    array (
      'id' => 'repair',
      'name' => 'Repair',
      'linked_attribute' => 'Smarts',
      'core_skill' => false,
      'arcane_background' => NULL,
      'summary' => 'Fixing mechanical and electrical gadgets, vehicles, weapons, and simple devices.',
      'requirements' => 
      array (
      ),
      'effects' => 
      array (
      ),
      'notes' => 
      array (
      ),
      'source_pages' => 
      array (
        0 => 60,
      ),
    ),
    22 => 
    array (
      'id' => 'research',
      'name' => 'Research',
      'linked_attribute' => 'Smarts',
      'core_skill' => false,
      'arcane_background' => NULL,
      'summary' => 'Finding written information from books, records, or other sources.',
      'requirements' => 
      array (
      ),
      'effects' => 
      array (
      ),
      'notes' => 
      array (
      ),
      'source_pages' => 
      array (
        0 => 60,
      ),
    ),
    23 => 
    array (
      'id' => 'riding',
      'name' => 'Riding',
      'linked_attribute' => 'Agility',
      'core_skill' => false,
      'arcane_background' => NULL,
      'summary' => 'Mounting, controlling, and riding trained beasts.',
      'requirements' => 
      array (
      ),
      'effects' => 
      array (
      ),
      'notes' => 
      array (
      ),
      'source_pages' => 
      array (
        0 => 60,
      ),
    ),
    24 => 
    array (
      'id' => 'science',
      'name' => 'Science',
      'linked_attribute' => 'Smarts',
      'core_skill' => false,
      'arcane_background' => NULL,
      'summary' => 'Knowledge of scientific disciplines such as biology, chemistry, geology, and engineering.',
      'requirements' => 
      array (
      ),
      'effects' => 
      array (
      ),
      'notes' => 
      array (
      ),
      'source_pages' => 
      array (
        0 => 60,
      ),
    ),
    25 => 
    array (
      'id' => 'shooting',
      'name' => 'Shooting',
      'linked_attribute' => 'Agility',
      'core_skill' => false,
      'arcane_background' => NULL,
      'summary' => 'Precision with ranged weapons.',
      'requirements' => 
      array (
      ),
      'effects' => 
      array (
      ),
      'notes' => 
      array (
      ),
      'source_pages' => 
      array (
        0 => 60,
      ),
    ),
    26 => 
    array (
      'id' => 'spellcasting',
      'name' => 'Spellcasting',
      'linked_attribute' => 'Smarts',
      'core_skill' => false,
      'arcane_background' => 'Magic',
      'summary' => 'Arcane skill used by Arcane Background (Magic).',
      'requirements' => 
      array (
      ),
      'effects' => 
      array (
      ),
      'notes' => 
      array (
      ),
      'source_pages' => 
      array (
        0 => 60,
      ),
    ),
    27 => 
    array (
      'id' => 'stealth',
      'name' => 'Stealth',
      'linked_attribute' => 'Agility',
      'core_skill' => true,
      'arcane_background' => NULL,
      'summary' => 'Sneaking, hiding, and avoiding detection.',
      'requirements' => 
      array (
      ),
      'effects' => 
      array (
      ),
      'notes' => 
      array (
      ),
      'source_pages' => 
      array (
        0 => 60,
      ),
    ),
    28 => 
    array (
      'id' => 'survival',
      'name' => 'Survival',
      'linked_attribute' => 'Smarts',
      'core_skill' => false,
      'arcane_background' => NULL,
      'summary' => 'Finding food, water, and shelter, and tracking in the wild.',
      'requirements' => 
      array (
      ),
      'effects' => 
      array (
      ),
      'notes' => 
      array (
      ),
      'source_pages' => 
      array (
        0 => 60,
      ),
    ),
    29 => 
    array (
      'id' => 'taunt',
      'name' => 'Taunt',
      'linked_attribute' => 'Smarts',
      'core_skill' => false,
      'arcane_background' => NULL,
      'summary' => 'Insulting or belittling others, usually as a Test.',
      'requirements' => 
      array (
      ),
      'effects' => 
      array (
      ),
      'notes' => 
      array (
      ),
      'source_pages' => 
      array (
        0 => 60,
      ),
    ),
    30 => 
    array (
      'id' => 'thievery',
      'name' => 'Thievery',
      'linked_attribute' => 'Agility',
      'core_skill' => false,
      'arcane_background' => NULL,
      'summary' => 'Sleight of hand, pickpocketing, lockpicking, and related shady skills.',
      'requirements' => 
      array (
      ),
      'effects' => 
      array (
      ),
      'notes' => 
      array (
      ),
      'source_pages' => 
      array (
        0 => 60,
      ),
    ),
    31 => 
    array (
      'id' => 'weird_science',
      'name' => 'Weird Science',
      'linked_attribute' => 'Smarts',
      'core_skill' => false,
      'arcane_background' => 'Weird Science',
      'summary' => 'Arcane skill used by Arcane Background (Weird Science).',
      'requirements' => 
      array (
      ),
      'effects' => 
      array (
      ),
      'notes' => 
      array (
      ),
      'source_pages' => 
      array (
        0 => 60,
      ),
    ),
  ),
);
