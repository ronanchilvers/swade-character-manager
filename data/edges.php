<?php

declare(strict_types=1);

return array (
  'schema_version' => '1.0',
  'source_pdf' => 'Savage Worlds Adventure Edition v5.pdf',
  'entry_type' => 'edge',
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
      'operator' => 'add | subtract | set | ignore | grant | reroll | draw | recover | replace | remove | double | halve | fail | break',
      'value' => 'number | string | boolean | null',
      'conditions' =>
      array (
        0 => 'optional condition tags',
      ),
      'details' => 'human-readable clarification',
    ),
    'repeatable' => 'boolean: whether the edge may be taken more than once',
  ),
  'entries' =>
  array (
    0 =>
    array (
      'id' => 'alertness',
      'name' => 'Alertness',
      'category' => 'background',
      'summary' => 'The hero is exceptionally observant.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'skill.notice',
          'operator' => 'add',
          'value' => 2,
          'conditions' =>
          array (
          ),
          'details' => '+2 to Notice rolls.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 61,
      ),
    ),
    1 =>
    array (
      'id' => 'ambidextrous',
      'name' => 'Ambidextrous',
      'category' => 'background',
      'summary' => 'The hero uses either hand equally well.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
        1 =>
        array (
          'type' => 'attribute',
          'target' => 'Agility',
          'value' => 'd8+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'off_hand_trait_penalty',
          'operator' => 'ignore',
          'value' => 2,
          'conditions' =>
          array (
          ),
          'details' => 'Ignore the usual −2 off-hand penalty.',
        ),
      ),
      'notes' =>
      array (
        0 => 'If wielding two weapons, Parry bonuses from both can stack.',
      ),
      'source_pages' =>
      array (
        0 => 61,
      ),
    ),
    2 =>
    array (
      'id' => 'arcane_background',
      'name' => 'Arcane Background',
      'category' => 'background',
      'summary' => 'Grants access to an arcane tradition.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'grant',
          'polarity' => 'benefit',
          'target' => 'arcane_background_access',
          'operator' => 'grant',
          'value' => true,
          'conditions' =>
          array (
          ),
          'details' => 'Allows the character to take one of the Arcane Backgrounds from Chapter Five.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 61,
      ),
    ),
    3 =>
    array (
      'id' => 'arcane_resistance',
      'name' => 'Arcane Resistance',
      'category' => 'background',
      'summary' => 'The hero resists supernatural powers.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
        1 =>
        array (
          'type' => 'attribute',
          'target' => 'Spirit',
          'value' => 'd8+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'enemy.arcane_skill_targeting_self',
          'operator' => 'subtract',
          'value' => 2,
          'conditions' =>
          array (
          ),
          'details' => 'Enemy arcane skill rolls targeting the hero suffer −2.',
        ),
        1 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'incoming.magical_damage',
          'operator' => 'subtract',
          'value' => 2,
          'conditions' =>
          array (
          ),
          'details' => 'Magical damage dealt to the hero is reduced by 2.',
        ),
      ),
      'notes' =>
      array (
        0 => 'Applies against magical bonuses on weapons and similar supernatural effects.',
      ),
      'source_pages' =>
      array (
        0 => 61,
      ),
    ),
    4 =>
    array (
      'id' => 'improved_arcane_resistance',
      'name' => 'Improved Arcane Resistance',
      'category' => 'background',
      'summary' => 'A stronger version of Arcane Resistance.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
        1 =>
        array (
          'type' => 'edge',
          'target' => 'edge',
          'value' => 'Arcane Resistance',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'enemy.arcane_skill_targeting_self',
          'operator' => 'subtract',
          'value' => 4,
          'conditions' =>
          array (
          ),
          'details' => 'Enemy arcane skill rolls targeting the hero suffer −4.',
        ),
        1 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'incoming.magical_damage',
          'operator' => 'subtract',
          'value' => 4,
          'conditions' =>
          array (
          ),
          'details' => 'Magical damage dealt to the hero is reduced by 4.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 61,
      ),
    ),
    5 =>
    array (
      'id' => 'aristocrat',
      'name' => 'Aristocrat',
      'category' => 'background',
      'summary' => 'The hero moves comfortably among the elite.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'networking.upper_class_persuasion',
          'operator' => 'add',
          'value' => 2,
          'conditions' =>
          array (
          ),
          'details' => '+2 to Persuasion when Networking with elites, nobles, or similar figures.',
        ),
        1 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'skill.common_knowledge',
          'operator' => 'add',
          'value' => 2,
          'conditions' =>
          array (
            0 => 'upper_class_etiquette_heraldry_or_gossip',
          ),
          'details' => '+2 to Common Knowledge rolls about upper-class etiquette, heraldry, family lines, or gossip.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 61,
      ),
    ),
    6 =>
    array (
      'id' => 'attractive',
      'name' => 'Attractive',
      'category' => 'background',
      'summary' => 'The hero gains social leverage through appearance.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
        1 =>
        array (
          'type' => 'attribute',
          'target' => 'Vigor',
          'value' => 'd6+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'skill.performance',
          'operator' => 'add',
          'value' => 1,
          'conditions' =>
          array (
            0 => 'target_is_attracted_to_character_type',
          ),
          'details' => '+1 to Performance rolls against interested targets.',
        ),
        1 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'skill.persuasion',
          'operator' => 'add',
          'value' => 1,
          'conditions' =>
          array (
            0 => 'target_is_attracted_to_character_type',
          ),
          'details' => '+1 to Persuasion rolls against interested targets.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 61,
      ),
    ),
    7 =>
    array (
      'id' => 'very_attractive',
      'name' => 'Very Attractive',
      'category' => 'background',
      'summary' => 'A stronger version of Attractive.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
        1 =>
        array (
          'type' => 'edge',
          'target' => 'edge',
          'value' => 'Attractive',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'skill.performance',
          'operator' => 'add',
          'value' => 2,
          'conditions' =>
          array (
            0 => 'target_is_attracted_to_character_type',
          ),
          'details' => '+2 to Performance rolls against interested targets.',
        ),
        1 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'skill.persuasion',
          'operator' => 'add',
          'value' => 2,
          'conditions' =>
          array (
            0 => 'target_is_attracted_to_character_type',
          ),
          'details' => '+2 to Persuasion rolls against interested targets.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 61,
      ),
    ),
    8 =>
    array (
      'id' => 'berserk',
      'name' => 'Berserk',
      'category' => 'background',
      'summary' => 'Physical harm can send the hero into a deadly rage.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'special',
          'polarity' => 'mixed',
          'target' => 'berserk_trigger',
          'operator' => 'grant',
          'value' => true,
          'conditions' =>
          array (
            0 => 'after_wound_or_shaken_from_physical_damage',
            1 => 'on_failed_smarts_roll_or_voluntary_failure',
          ),
          'details' => 'The hero goes Berserk after suffering physical harm if the Smarts roll fails.',
        ),
        1 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'attribute.strength',
          'operator' => 'add',
          'value' => 'one_die_type',
          'conditions' =>
          array (
            0 => 'while_berserk',
          ),
          'details' => 'Strength increases one die type while Berserk.',
        ),
        2 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'derived.toughness',
          'operator' => 'add',
          'value' => 2,
          'conditions' =>
          array (
            0 => 'while_berserk',
          ),
          'details' => '+2 Toughness while Berserk.',
        ),
        3 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'wound_penalties',
          'operator' => 'ignore',
          'value' => 1,
          'conditions' =>
          array (
            0 => 'while_berserk',
          ),
          'details' => 'Ignore one level of Wound penalties while Berserk.',
        ),
        4 =>
        array (
          'level' => 'base',
          'type' => 'restriction',
          'polarity' => 'penalty',
          'target' => 'melee_attacks',
          'operator' => 'set',
          'value' => 'wild_attack_required',
          'conditions' =>
          array (
            0 => 'while_berserk',
          ),
          'details' => 'All melee attacks must be Wild Attacks while Berserk.',
        ),
      ),
      'notes' =>
      array (
        0 => 'Critical failures on Fighting hit a random target while Berserk.',
        1 => 'After five consecutive rounds the hero takes Fatigue; after ten rounds rage ends.',
        2 => 'The hero can attempt to end the rage with a Smarts roll at −2.',
      ),
      'source_pages' =>
      array (
        0 => 61,
      ),
    ),
    9 =>
    array (
      'id' => 'brave',
      'name' => 'Brave',
      'category' => 'background',
      'summary' => 'The hero handles terror better than most.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
        1 =>
        array (
          'type' => 'attribute',
          'target' => 'Spirit',
          'value' => 'd6+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'resist.fear',
          'operator' => 'add',
          'value' => 2,
          'conditions' =>
          array (
          ),
          'details' => '+2 to Fear checks.',
        ),
        1 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'fear_table_result',
          'operator' => 'subtract',
          'value' => 2,
          'conditions' =>
          array (
          ),
          'details' => 'Subtract 2 from rolls on the Fear Table.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 61,
      ),
    ),
    10 =>
    array (
      'id' => 'brawny',
      'name' => 'Brawny',
      'category' => 'background',
      'summary' => 'The hero is exceptionally large or fit.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
        1 =>
        array (
          'type' => 'attribute',
          'target' => 'Strength',
          'value' => 'd6+',
        ),
        2 =>
        array (
          'type' => 'attribute',
          'target' => 'Vigor',
          'value' => 'd6+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'derived.size',
          'operator' => 'add',
          'value' => 1,
          'conditions' =>
          array (
          ),
          'details' => 'Size increases by +1, which also increases Toughness by 1.',
        ),
        1 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'strength_for_encumbrance_and_minimum_strength',
          'operator' => 'add',
          'value' => 'one_die_type',
          'conditions' =>
          array (
          ),
          'details' => 'Treat Strength as one die type higher for Encumbrance and Minimum Strength requirements.',
        ),
      ),
      'notes' =>
      array (
        0 => 'Brawny cannot increase Size above +3.',
      ),
      'source_pages' =>
      array (
        0 => 61,
      ),
    ),
    11 =>
    array (
      'id' => 'brute',
      'name' => 'Brute',
      'category' => 'background',
      'summary' => 'The hero relies on strength instead of coordination for Athletics.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
        1 =>
        array (
          'type' => 'attribute',
          'target' => 'Strength',
          'value' => 'd6+',
        ),
        2 =>
        array (
          'type' => 'attribute',
          'target' => 'Vigor',
          'value' => 'd6+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'special',
          'polarity' => 'benefit',
          'target' => 'skill.athletics_linked_attribute',
          'operator' => 'replace',
          'value' => 'Strength',
          'conditions' =>
          array (
          ),
          'details' => 'Athletics is linked to Strength instead of Agility, including resisting Athletics if the hero chooses.',
        ),
        1 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'range.thrown_weapons_short',
          'operator' => 'add',
          'value' => 1,
          'conditions' =>
          array (
          ),
          'details' => 'Increase Short Range of thrown items by +1, with Medium and Long recalculated from the new Short Range.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 61,
      ),
    ),
    12 =>
    array (
      'id' => 'charismatic',
      'name' => 'Charismatic',
      'category' => 'background',
      'summary' => 'The hero is naturally persuasive.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
        1 =>
        array (
          'type' => 'attribute',
          'target' => 'Spirit',
          'value' => 'd8+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'reroll',
          'polarity' => 'benefit',
          'target' => 'skill.persuasion',
          'operator' => 'reroll',
          'value' => 1,
          'conditions' =>
          array (
            0 => 'free_reroll',
          ),
          'details' => 'Gain one free reroll on Persuasion rolls.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 61,
      ),
    ),
    13 =>
    array (
      'id' => 'elan',
      'name' => 'Elan',
      'category' => 'background',
      'summary' => 'The hero gets more out of Benny-powered rerolls.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
        1 =>
        array (
          'type' => 'attribute',
          'target' => 'Spirit',
          'value' => 'd8+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'trait_reroll_using_benny',
          'operator' => 'add',
          'value' => 2,
          'conditions' =>
          array (
          ),
          'details' => '+2 when spending a Benny to reroll a Trait roll.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 61,
      ),
    ),
    14 =>
    array (
      'id' => 'fame',
      'name' => 'Fame',
      'category' => 'background',
      'summary' => 'The hero is a minor celebrity.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'skill.persuasion',
          'operator' => 'add',
          'value' => 1,
          'conditions' =>
          array (
            0 => 'when_recognized',
          ),
          'details' => '+1 to Persuasion when the hero is recognized.',
        ),
        1 =>
        array (
          'level' => 'base',
          'type' => 'resource',
          'polarity' => 'benefit',
          'target' => 'performance_fee',
          'operator' => 'double',
          'value' => 2,
          'conditions' =>
          array (
          ),
          'details' => 'Earn double the usual fee for Performance.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 61,
      ),
    ),
    15 =>
    array (
      'id' => 'famous',
      'name' => 'Famous',
      'category' => 'background',
      'summary' => 'The hero is widely known and paid accordingly.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Seasoned',
        ),
        1 =>
        array (
          'type' => 'edge',
          'target' => 'edge',
          'value' => 'Fame',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'skill.persuasion',
          'operator' => 'add',
          'value' => 2,
          'conditions' =>
          array (
            0 => 'when_recognized',
          ),
          'details' => '+2 to Persuasion when the hero is recognized.',
        ),
        1 =>
        array (
          'level' => 'base',
          'type' => 'resource',
          'polarity' => 'benefit',
          'target' => 'performance_fee',
          'operator' => 'set',
          'value' => '5x_or_more',
          'conditions' =>
          array (
          ),
          'details' => 'Earn five times or more the usual fee for Performance.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 61,
      ),
    ),
    16 =>
    array (
      'id' => 'fast_healer',
      'name' => 'Fast Healer',
      'category' => 'background',
      'summary' => 'The hero recovers naturally more effectively.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
        1 =>
        array (
          'type' => 'attribute',
          'target' => 'Vigor',
          'value' => 'd8+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'vigor.natural_healing',
          'operator' => 'add',
          'value' => 2,
          'conditions' =>
          array (
          ),
          'details' => '+2 to Vigor rolls for natural healing.',
        ),
      ),
      'notes' =>
      array (
        0 => 'Natural healing checks are made every 3 days.',
      ),
      'source_pages' =>
      array (
        0 => 61,
      ),
    ),
    17 =>
    array (
      'id' => 'fleet_footed',
      'name' => 'Fleet-Footed',
      'category' => 'background',
      'summary' => 'The hero moves faster than normal.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
        1 =>
        array (
          'type' => 'attribute',
          'target' => 'Agility',
          'value' => 'd6+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'derived.pace',
          'operator' => 'add',
          'value' => 2,
          'conditions' =>
          array (
          ),
          'details' => '+2 Pace.',
        ),
        1 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'movement.running_die',
          'operator' => 'add',
          'value' => 'one_die_type',
          'conditions' =>
          array (
          ),
          'details' => 'Increase the running die one step.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 61,
      ),
    ),
    18 =>
    array (
      'id' => 'linguist',
      'name' => 'Linguist',
      'category' => 'background',
      'summary' => 'The hero begins play with several languages.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
        1 =>
        array (
          'type' => 'attribute',
          'target' => 'Smarts',
          'value' => 'd6+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'grant',
          'polarity' => 'benefit',
          'target' => 'skill.language',
          'operator' => 'grant',
          'value' => 'half_smarts_die_type_languages_at_d6',
          'conditions' =>
          array (
          ),
          'details' => 'Start with a number of Language skills at d6 equal to half the hero’s Smarts die type.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 61,
      ),
    ),
    19 =>
    array (
      'id' => 'luck',
      'name' => 'Luck',
      'category' => 'background',
      'summary' => 'The hero starts each session with more Bennies.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'resource',
          'polarity' => 'benefit',
          'target' => 'resource.bennies.start_session',
          'operator' => 'add',
          'value' => 1,
          'conditions' =>
          array (
          ),
          'details' => 'Gain one extra Benny at the start of each session.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 61,
      ),
    ),
    20 =>
    array (
      'id' => 'great_luck',
      'name' => 'Great Luck',
      'category' => 'background',
      'summary' => 'The hero starts each session with even more Bennies.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
        1 =>
        array (
          'type' => 'edge',
          'target' => 'edge',
          'value' => 'Luck',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'resource',
          'polarity' => 'benefit',
          'target' => 'resource.bennies.start_session',
          'operator' => 'add',
          'value' => 2,
          'conditions' =>
          array (
          ),
          'details' => 'Gain two extra Bennies at the start of each session.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 61,
      ),
    ),
    21 =>
    array (
      'id' => 'quick',
      'name' => 'Quick',
      'category' => 'background',
      'summary' => 'The hero reacts faster than normal in combat.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
        1 =>
        array (
          'type' => 'attribute',
          'target' => 'Agility',
          'value' => 'd8+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'action',
          'polarity' => 'benefit',
          'target' => 'combat.action_cards',
          'operator' => 'draw',
          'value' => 'redraw_5_or_lower',
          'conditions' =>
          array (
          ),
          'details' => 'The hero may discard and redraw Action Cards of 5 or lower.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 61,
      ),
    ),
    22 =>
    array (
      'id' => 'rich',
      'name' => 'Rich',
      'category' => 'background',
      'summary' => 'The hero starts wealthier than most.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'resource',
          'polarity' => 'benefit',
          'target' => 'resource.starting_funds',
          'operator' => 'set',
          'value' => '3x',
          'conditions' =>
          array (
          ),
          'details' => 'Start with three times the normal starting funds.',
        ),
        1 =>
        array (
          'level' => 'base',
          'type' => 'resource',
          'polarity' => 'benefit',
          'target' => 'income.annual_salary',
          'operator' => 'set',
          'value' => 150000,
          'conditions' =>
          array (
          ),
          'details' => 'Average annual salary is $150,000.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 61,
      ),
    ),
    23 =>
    array (
      'id' => 'filthy_rich',
      'name' => 'Filthy Rich',
      'category' => 'background',
      'summary' => 'The hero is extremely wealthy.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
        1 =>
        array (
          'type' => 'edge',
          'target' => 'edge',
          'value' => 'Rich',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'resource',
          'polarity' => 'benefit',
          'target' => 'resource.starting_funds',
          'operator' => 'set',
          'value' => '5x',
          'conditions' =>
          array (
          ),
          'details' => 'Start with five times the normal starting funds.',
        ),
        1 =>
        array (
          'level' => 'base',
          'type' => 'resource',
          'polarity' => 'benefit',
          'target' => 'income.annual_salary',
          'operator' => 'set',
          'value' => 500000,
          'conditions' =>
          array (
          ),
          'details' => 'Average annual salary is $500,000.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 61,
      ),
    ),
    24 =>
    array (
      'id' => 'block',
      'name' => 'Block',
      'category' => 'combat',
      'summary' => 'The hero is harder to hit in melee.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Seasoned',
        ),
        1 =>
        array (
          'type' => 'trait',
          'target' => 'Fighting',
          'value' => 'd8+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'derived.parry',
          'operator' => 'add',
          'value' => 1,
          'conditions' =>
          array (
          ),
          'details' => '+1 Parry.',
        ),
        1 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'incoming.gang_up_bonus',
          'operator' => 'ignore',
          'value' => 1,
          'conditions' =>
          array (
          ),
          'details' => 'Ignore 1 point of Gang Up bonus from foes.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 62,
      ),
    ),
    25 =>
    array (
      'id' => 'improved_block',
      'name' => 'Improved Block',
      'category' => 'combat',
      'summary' => 'A stronger version of Block.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Veteran',
        ),
        1 =>
        array (
          'type' => 'edge',
          'target' => 'edge',
          'value' => 'Block',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'derived.parry',
          'operator' => 'add',
          'value' => 2,
          'conditions' =>
          array (
          ),
          'details' => '+2 Parry.',
        ),
        1 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'incoming.gang_up_bonus',
          'operator' => 'ignore',
          'value' => 2,
          'conditions' =>
          array (
          ),
          'details' => 'Ignore 2 points of Gang Up bonus from foes.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 62,
      ),
    ),
    26 =>
    array (
      'id' => 'brawler',
      'name' => 'Brawler',
      'category' => 'combat',
      'summary' => 'The hero is tougher and deadlier unarmed.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
        1 =>
        array (
          'type' => 'attribute',
          'target' => 'Strength',
          'value' => 'd8+',
        ),
        2 =>
        array (
          'type' => 'attribute',
          'target' => 'Vigor',
          'value' => 'd8+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'derived.toughness',
          'operator' => 'add',
          'value' => 1,
          'conditions' =>
          array (
          ),
          'details' => '+1 Toughness.',
        ),
        1 =>
        array (
          'level' => 'base',
          'type' => 'damage',
          'polarity' => 'benefit',
          'target' => 'damage.unarmed_bonus',
          'operator' => 'add',
          'value' => 'd4_or_one_die_step',
          'conditions' =>
          array (
          ),
          'details' => 'Add a d4 to unarmed damage, or increase an existing bonus die one step if combined with Martial Artist, Claws, or similar abilities.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 62,
      ),
    ),
    27 =>
    array (
      'id' => 'bruiser',
      'name' => 'Bruiser',
      'category' => 'combat',
      'summary' => 'The hero becomes even tougher and more dangerous unarmed.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Seasoned',
        ),
        1 =>
        array (
          'type' => 'edge',
          'target' => 'edge',
          'value' => 'Brawler',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'damage',
          'polarity' => 'benefit',
          'target' => 'damage.unarmed_strength',
          'operator' => 'add',
          'value' => 'one_die_type',
          'conditions' =>
          array (
          ),
          'details' => 'Increase unarmed Strength damage one die type.',
        ),
        1 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'derived.toughness',
          'operator' => 'add',
          'value' => 1,
          'conditions' =>
          array (
          ),
          'details' => 'Gain another +1 Toughness.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 62,
      ),
    ),
    28 =>
    array (
      'id' => 'calculating',
      'name' => 'Calculating',
      'category' => 'combat',
      'summary' => 'The hero makes a low card count.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
        1 =>
        array (
          'type' => 'attribute',
          'target' => 'Smarts',
          'value' => 'd8+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'one_action_penalties',
          'operator' => 'ignore',
          'value' => 2,
          'conditions' =>
          array (
            0 => 'action_card_5_or_lower',
          ),
          'details' => 'Ignore up to 2 points of penalties on one action when holding an Action Card of 5 or lower.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 62,
      ),
    ),
    29 =>
    array (
      'id' => 'combat_reflexes',
      'name' => 'Combat Reflexes',
      'category' => 'combat',
      'summary' => 'The hero recovers from stun and shock more easily.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Seasoned',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'spirit.recover_shaken_or_stunned',
          'operator' => 'add',
          'value' => 2,
          'conditions' =>
          array (
          ),
          'details' => '+2 Spirit when recovering from being Shaken or Stunned.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 62,
      ),
    ),
    30 =>
    array (
      'id' => 'counterattack',
      'name' => 'Counterattack',
      'category' => 'combat',
      'summary' => 'The hero punishes missed melee attacks.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Seasoned',
        ),
        1 =>
        array (
          'type' => 'trait',
          'target' => 'Fighting',
          'value' => 'd8+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'action',
          'polarity' => 'benefit',
          'target' => 'free_fighting_attack',
          'operator' => 'grant',
          'value' => 1,
          'conditions' =>
          array (
            0 => 'once_per_turn',
            1 => 'when_foe_fails_fighting_roll_against_self',
          ),
          'details' => 'Gain one free attack against a foe who misses with a Fighting roll.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 62,
      ),
    ),
    31 =>
    array (
      'id' => 'improved_counterattack',
      'name' => 'Improved Counterattack',
      'category' => 'combat',
      'summary' => 'The hero can counter multiple missed melee attacks.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Veteran',
        ),
        1 =>
        array (
          'type' => 'edge',
          'target' => 'edge',
          'value' => 'Counterattack',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'action',
          'polarity' => 'benefit',
          'target' => 'free_fighting_attack',
          'operator' => 'grant',
          'value' => 3,
          'conditions' =>
          array (
            0 => 'once_per_turn_total',
            1 => 'when_foes_fail_fighting_rolls_against_self',
          ),
          'details' => 'Gain free counterattacks against up to three failed attacks per turn.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 62,
      ),
    ),
    32 =>
    array (
      'id' => 'dead_shot',
      'name' => 'Dead Shot',
      'category' => 'combat',
      'summary' => 'A Joker can turn the first ranged hit into devastating damage.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'wild_card',
          'target' => 'wild_card',
          'value' => true,
        ),
        1 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
        2 =>
        array (
          'type' => 'special',
          'target' => 'one_of',
          'value' =>
          array (
            0 => 'Athletics d8+',
            1 => 'Shooting d8+',
          ),
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'damage',
          'polarity' => 'benefit',
          'target' => 'first_successful_athletics_throwing_or_shooting_damage',
          'operator' => 'double',
          'value' => 2,
          'conditions' =>
          array (
            0 => 'when_dealt_a_joker',
          ),
          'details' => 'On a Joker, the first successful Athletics (throwing) or Shooting roll deals double damage.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 62,
      ),
    ),
    33 =>
    array (
      'id' => 'dodge',
      'name' => 'Dodge',
      'category' => 'combat',
      'summary' => 'The hero is harder to hit at range.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Seasoned',
        ),
        1 =>
        array (
          'type' => 'attribute',
          'target' => 'Agility',
          'value' => 'd8+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'incoming.ranged_attack_total',
          'operator' => 'subtract',
          'value' => 2,
          'conditions' =>
          array (
          ),
          'details' => 'Foes suffer −2 to hit the hero with ranged attacks.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 62,
      ),
    ),
    34 =>
    array (
      'id' => 'improved_dodge',
      'name' => 'Improved Dodge',
      'category' => 'combat',
      'summary' => 'The hero is also much better at Evasion.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Seasoned',
        ),
        1 =>
        array (
          'type' => 'edge',
          'target' => 'edge',
          'value' => 'Dodge',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'evasion_total',
          'operator' => 'add',
          'value' => 2,
          'conditions' =>
          array (
          ),
          'details' => '+2 to Evasion totals.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 62,
      ),
    ),
    35 =>
    array (
      'id' => 'double_tap',
      'name' => 'Double Tap',
      'category' => 'combat',
      'summary' => 'The hero puts two shots on target more effectively.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Seasoned',
        ),
        1 =>
        array (
          'type' => 'trait',
          'target' => 'Shooting',
          'value' => 'd6+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'shooting_attack_total',
          'operator' => 'add',
          'value' => 1,
          'conditions' =>
          array (
            0 => 'firing_no_more_than_rof_1',
          ),
          'details' => '+1 to hit when firing no more than RoF 1.',
        ),
        1 =>
        array (
          'level' => 'base',
          'type' => 'damage',
          'polarity' => 'benefit',
          'target' => 'shooting_damage',
          'operator' => 'add',
          'value' => 1,
          'conditions' =>
          array (
            0 => 'firing_no_more_than_rof_1',
          ),
          'details' => '+1 damage when firing no more than RoF 1.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 62,
      ),
    ),
    36 =>
    array (
      'id' => 'extraction',
      'name' => 'Extraction',
      'category' => 'combat',
      'summary' => 'The hero can slip away from one adjacent foe safely.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
        1 =>
        array (
          'type' => 'attribute',
          'target' => 'Agility',
          'value' => 'd8+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'special',
          'polarity' => 'benefit',
          'target' => 'withdraw_from_close_combat',
          'operator' => 'ignore',
          'value' => 1,
          'conditions' =>
          array (
          ),
          'details' => 'One adjacent foe does not get a free attack when the hero withdraws from close combat.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 62,
      ),
    ),
    37 =>
    array (
      'id' => 'improved_extraction',
      'name' => 'Improved Extraction',
      'category' => 'combat',
      'summary' => 'The hero can disengage from several nearby foes.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Seasoned',
        ),
        1 =>
        array (
          'type' => 'edge',
          'target' => 'edge',
          'value' => 'Extraction',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'special',
          'polarity' => 'benefit',
          'target' => 'withdraw_from_close_combat',
          'operator' => 'ignore',
          'value' => 3,
          'conditions' =>
          array (
          ),
          'details' => 'Up to three adjacent foes do not get free attacks when the hero withdraws.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 62,
      ),
    ),
    38 =>
    array (
      'id' => 'feint',
      'name' => 'Feint',
      'category' => 'combat',
      'summary' => 'The hero can force a less agile defense during a Fighting Test.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
        1 =>
        array (
          'type' => 'trait',
          'target' => 'Fighting',
          'value' => 'd8+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'special',
          'polarity' => 'benefit',
          'target' => 'fighting_test_resistance_attribute',
          'operator' => 'replace',
          'value' => 'Smarts',
          'conditions' =>
          array (
          ),
          'details' => 'The hero may choose to have a foe resist a Fighting Test with Smarts instead of Agility.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 62,
      ),
    ),
    39 =>
    array (
      'id' => 'first_strike',
      'name' => 'First Strike',
      'category' => 'combat',
      'summary' => 'The hero lashes out when enemies move into reach.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
        1 =>
        array (
          'type' => 'attribute',
          'target' => 'Agility',
          'value' => 'd8+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'action',
          'polarity' => 'benefit',
          'target' => 'free_fighting_attack',
          'operator' => 'grant',
          'value' => 1,
          'conditions' =>
          array (
            0 => 'once_per_round',
            1 => 'when_foe_moves_within_reach',
          ),
          'details' => 'Gain one free Fighting attack when a foe moves within Reach.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 62,
      ),
    ),
    40 =>
    array (
      'id' => 'improved_first_strike',
      'name' => 'Improved First Strike',
      'category' => 'combat',
      'summary' => 'The hero can exploit multiple openings when foes close in.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Heroic',
        ),
        1 =>
        array (
          'type' => 'edge',
          'target' => 'edge',
          'value' => 'First Strike',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'action',
          'polarity' => 'benefit',
          'target' => 'free_fighting_attack',
          'operator' => 'grant',
          'value' => 3,
          'conditions' =>
          array (
            0 => 'when_foes_move_within_reach',
          ),
          'details' => 'Gain free Fighting attacks against up to three foes when they move within Reach.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 62,
      ),
    ),
    41 =>
    array (
      'id' => 'free_runner',
      'name' => 'Free Runner',
      'category' => 'combat',
      'summary' => 'The hero moves through obstacles and chases efficiently.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
        1 =>
        array (
          'type' => 'attribute',
          'target' => 'Agility',
          'value' => 'd8+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'special',
          'polarity' => 'benefit',
          'target' => 'movement.difficult_ground',
          'operator' => 'ignore',
          'value' => true,
          'conditions' =>
          array (
          ),
          'details' => 'Ignore Difficult Ground.',
        ),
        1 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'skill.athletics',
          'operator' => 'add',
          'value' => 2,
          'conditions' =>
          array (
            0 => 'foot_chases_or_climbing',
          ),
          'details' => '+2 to Athletics in foot chases and when climbing.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 62,
      ),
    ),
    42 =>
    array (
      'id' => 'frenzy',
      'name' => 'Frenzy',
      'category' => 'combat',
      'summary' => 'The hero can unleash multiple melee strikes at once.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Seasoned',
        ),
        1 =>
        array (
          'type' => 'trait',
          'target' => 'Fighting',
          'value' => 'd8+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'action',
          'polarity' => 'benefit',
          'target' => 'one_melee_attack_fighting_dice',
          'operator' => 'add',
          'value' => 1,
          'conditions' =>
          array (
            0 => 'limited_action',
          ),
          'details' => 'Roll a second Fighting die for one melee attack.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 62,
      ),
    ),
    43 =>
    array (
      'id' => 'improved_frenzy',
      'name' => 'Improved Frenzy',
      'category' => 'combat',
      'summary' => 'The hero can press an even more furious melee assault.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Veteran',
        ),
        1 =>
        array (
          'type' => 'edge',
          'target' => 'edge',
          'value' => 'Frenzy',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'action',
          'polarity' => 'benefit',
          'target' => 'one_melee_attack_fighting_dice',
          'operator' => 'add',
          'value' => 2,
          'conditions' =>
          array (
            0 => 'limited_action',
          ),
          'details' => 'Roll a third Fighting die for one melee attack.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 62,
      ),
    ),
    44 =>
    array (
      'id' => 'giant_killer',
      'name' => 'Giant Killer',
      'category' => 'combat',
      'summary' => 'The hero hits far above their size class.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'damage',
          'polarity' => 'benefit',
          'target' => 'damage_vs_target_three_or_more_sizes_larger',
          'operator' => 'add',
          'value' => '1d6',
          'conditions' =>
          array (
          ),
          'details' => '+1d6 damage against creatures three Sizes larger or more.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 62,
      ),
    ),
    45 =>
    array (
      'id' => 'hard_to_kill',
      'name' => 'Hard to Kill',
      'category' => 'combat',
      'summary' => 'The hero resists dying from severe wounds.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Veteran',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'vigor.avoid_bleeding_out',
          'operator' => 'ignore',
          'value' => 'wound_penalties',
          'conditions' =>
          array (
          ),
          'details' => 'Ignore Wound penalties when making Vigor rolls to avoid Bleeding Out.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 62,
      ),
    ),
    46 =>
    array (
      'id' => 'harder_to_kill',
      'name' => 'Harder to Kill',
      'category' => 'combat',
      'summary' => 'The hero can survive apparent death.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Veteran',
        ),
        1 =>
        array (
          'type' => 'edge',
          'target' => 'edge',
          'value' => 'Hard to Kill',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'special',
          'polarity' => 'benefit',
          'target' => 'death_result',
          'operator' => 'replace',
          'value' => 'incapacitated_on_even_roll',
          'conditions' =>
          array (
          ),
          'details' => 'If the character perishes, roll a die; on an even result the hero is Incapacitated instead and survives somehow.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 62,
      ),
    ),
    47 =>
    array (
      'id' => 'improvisational_fighter',
      'name' => 'Improvisational Fighter',
      'category' => 'combat',
      'summary' => 'The hero makes the best of improvised weapons.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Seasoned',
        ),
        1 =>
        array (
          'type' => 'attribute',
          'target' => 'Smarts',
          'value' => 'd6+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'improvised_weapon_attack_penalty',
          'operator' => 'ignore',
          'value' => 2,
          'conditions' =>
          array (
          ),
          'details' => 'Ignore the usual −2 penalty when attacking with improvised weapons.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 62,
      ),
    ),
    48 =>
    array (
      'id' => 'iron_jaw',
      'name' => 'Iron Jaw',
      'category' => 'combat',
      'summary' => 'The hero is very hard to knock cold.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
        1 =>
        array (
          'type' => 'attribute',
          'target' => 'Vigor',
          'value' => 'd8+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'vigor.soak',
          'operator' => 'add',
          'value' => 2,
          'conditions' =>
          array (
          ),
          'details' => '+2 to Soak rolls.',
        ),
        1 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'vigor.avoid_knockout_blow',
          'operator' => 'add',
          'value' => 2,
          'conditions' =>
          array (
          ),
          'details' => '+2 to Vigor rolls made to avoid Knockout Blows.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 62,
      ),
    ),
    49 =>
    array (
      'id' => 'killer_instinct',
      'name' => 'Killer Instinct',
      'category' => 'combat',
      'summary' => 'The hero excels at pressing an advantage in Tests.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Seasoned',
        ),
        1 =>
        array (
          'type' => 'attribute',
          'target' => 'Smarts',
          'value' => 'd8+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'reroll',
          'polarity' => 'benefit',
          'target' => 'opposed_test_initiated_by_self',
          'operator' => 'reroll',
          'value' => 1,
          'conditions' =>
          array (
            0 => 'free_reroll',
          ),
          'details' => 'Gain a free reroll on any opposed Test the hero initiates.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 62,
      ),
    ),
    50 =>
    array (
      'id' => 'level_headed',
      'name' => 'Level Headed',
      'category' => 'combat',
      'summary' => 'The hero reads the fight quickly and acts on the better card.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Seasoned',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'action',
          'polarity' => 'benefit',
          'target' => 'combat.action_cards_drawn',
          'operator' => 'draw',
          'value' => 1,
          'conditions' =>
          array (
            0 => 'choose_best_card',
          ),
          'details' => 'Draw one additional Action Card each round and choose which one to use.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 62,
      ),
    ),
    51 =>
    array (
      'id' => 'improved_level_headed',
      'name' => 'Improved Level Headed',
      'category' => 'combat',
      'summary' => 'The hero evaluates several options at once in combat.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Seasoned',
        ),
        1 =>
        array (
          'type' => 'edge',
          'target' => 'edge',
          'value' => 'Level Headed',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'action',
          'polarity' => 'benefit',
          'target' => 'combat.action_cards_drawn',
          'operator' => 'draw',
          'value' => 2,
          'conditions' =>
          array (
            0 => 'choose_best_card',
          ),
          'details' => 'Draw two additional Action Cards each round and choose which one to use.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 62,
      ),
    ),
    52 =>
    array (
      'id' => 'marksman',
      'name' => 'Marksman',
      'category' => 'combat',
      'summary' => 'The hero makes a deliberate, high-quality ranged shot.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Seasoned',
        ),
        1 =>
        array (
          'type' => 'special',
          'target' => 'one_of',
          'value' =>
          array (
            0 => 'Athletics d8+',
            1 => 'Shooting d8+',
          ),
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'ranged_attack_penalties',
          'operator' => 'ignore',
          'value' => 2,
          'conditions' =>
          array (
            0 => 'when_not_moving',
            1 => 'rof_1_or_less',
          ),
          'details' => 'Ignore up to 2 points of penalties from Range, Cover, Called Shot, Scale, or Speed.',
        ),
        1 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'first_athletics_throwing_or_shooting_roll',
          'operator' => 'add',
          'value' => 1,
          'conditions' =>
          array (
            0 => 'when_not_moving',
            1 => 'rof_1_or_less',
          ),
          'details' => 'Alternatively, add +1 to the first Athletics (throwing) or Shooting roll.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 63,
      ),
    ),
    53 =>
    array (
      'id' => 'martial_artist',
      'name' => 'Martial Artist',
      'category' => 'combat',
      'summary' => 'The hero is trained to make unarmed combat more effective.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
        1 =>
        array (
          'type' => 'trait',
          'target' => 'Fighting',
          'value' => 'd6+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'unarmed_fighting_total',
          'operator' => 'add',
          'value' => 1,
          'conditions' =>
          array (
          ),
          'details' => '+1 to Unarmed Fighting.',
        ),
        1 =>
        array (
          'level' => 'base',
          'type' => 'grant',
          'polarity' => 'benefit',
          'target' => 'unarmed_attacks_are_natural_weapons',
          'operator' => 'grant',
          'value' => true,
          'conditions' =>
          array (
          ),
          'details' => 'Fists and feet count as Natural Weapons.',
        ),
        2 =>
        array (
          'level' => 'base',
          'type' => 'damage',
          'polarity' => 'benefit',
          'target' => 'damage.unarmed_bonus',
          'operator' => 'add',
          'value' => 'd4_or_one_die_step',
          'conditions' =>
          array (
          ),
          'details' => 'Add a d4 to unarmed damage, or increase the bonus die one step if the hero already has one.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 63,
      ),
    ),
    54 =>
    array (
      'id' => 'martial_warrior',
      'name' => 'Martial Warrior',
      'category' => 'combat',
      'summary' => 'The hero is an even more formidable unarmed combatant.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Seasoned',
        ),
        1 =>
        array (
          'type' => 'edge',
          'target' => 'edge',
          'value' => 'Martial Artist',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'unarmed_fighting_total',
          'operator' => 'add',
          'value' => 2,
          'conditions' =>
          array (
          ),
          'details' => '+2 to Unarmed Fighting.',
        ),
        1 =>
        array (
          'level' => 'base',
          'type' => 'damage',
          'polarity' => 'benefit',
          'target' => 'damage.unarmed_bonus',
          'operator' => 'add',
          'value' => 'one_die_type',
          'conditions' =>
          array (
          ),
          'details' => 'Increase the unarmed damage bonus die one step.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 63,
      ),
    ),
    55 =>
    array (
      'id' => 'mighty_blow',
      'name' => 'Mighty Blow',
      'category' => 'combat',
      'summary' => 'A Joker can turn the first melee hit into devastating damage.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'wild_card',
          'target' => 'wild_card',
          'value' => true,
        ),
        1 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
        2 =>
        array (
          'type' => 'trait',
          'target' => 'Fighting',
          'value' => 'd8+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'damage',
          'polarity' => 'benefit',
          'target' => 'first_successful_fighting_damage',
          'operator' => 'double',
          'value' => 2,
          'conditions' =>
          array (
            0 => 'when_dealt_a_joker',
          ),
          'details' => 'On a Joker, the first successful Fighting roll deals double damage.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 63,
      ),
    ),
    56 =>
    array (
      'id' => 'nerves_of_steel',
      'name' => 'Nerves of Steel',
      'category' => 'combat',
      'summary' => 'The hero shrugs off pain more effectively.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
        1 =>
        array (
          'type' => 'attribute',
          'target' => 'Vigor',
          'value' => 'd8+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'wound_penalties',
          'operator' => 'ignore',
          'value' => 1,
          'conditions' =>
          array (
          ),
          'details' => 'Ignore one level of Wound penalties.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 63,
      ),
    ),
    57 =>
    array (
      'id' => 'improved_nerves_of_steel',
      'name' => 'Improved Nerves of Steel',
      'category' => 'combat',
      'summary' => 'The hero operates well even while badly hurt.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
        1 =>
        array (
          'type' => 'edge',
          'target' => 'edge',
          'value' => 'Nerves of Steel',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'wound_penalties',
          'operator' => 'ignore',
          'value' => 2,
          'conditions' =>
          array (
          ),
          'details' => 'Ignore up to two levels of Wound penalties.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 63,
      ),
    ),
    58 =>
    array (
      'id' => 'no_mercy',
      'name' => 'No Mercy',
      'category' => 'combat',
      'summary' => 'The hero hits harder when spending a Benny on damage.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Seasoned',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'damage',
          'polarity' => 'benefit',
          'target' => 'damage_reroll_using_benny',
          'operator' => 'add',
          'value' => 2,
          'conditions' =>
          array (
          ),
          'details' => '+2 damage when spending a Benny to reroll damage.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 63,
      ),
    ),
    59 =>
    array (
      'id' => 'rapid_fire',
      'name' => 'Rapid Fire',
      'category' => 'combat',
      'summary' => 'The hero can pour out more bullets in one attack.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Seasoned',
        ),
        1 =>
        array (
          'type' => 'trait',
          'target' => 'Shooting',
          'value' => 'd6+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'action',
          'polarity' => 'benefit',
          'target' => 'shooting_attack_rate_of_fire',
          'operator' => 'add',
          'value' => 1,
          'conditions' =>
          array (
            0 => 'one_shooting_attack_per_turn',
          ),
          'details' => 'Increase RoF by 1 for one Shooting attack per turn.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 63,
      ),
    ),
    60 =>
    array (
      'id' => 'improved_rapid_fire',
      'name' => 'Improved Rapid Fire',
      'category' => 'combat',
      'summary' => 'The hero can increase rate of fire on multiple attacks.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Veteran',
        ),
        1 =>
        array (
          'type' => 'edge',
          'target' => 'edge',
          'value' => 'Rapid Fire',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'action',
          'polarity' => 'benefit',
          'target' => 'shooting_attack_rate_of_fire',
          'operator' => 'add',
          'value' => 1,
          'conditions' =>
          array (
            0 => 'up_to_two_shooting_attacks_per_turn',
          ),
          'details' => 'Increase RoF by 1 for up to two Shooting attacks per turn.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 63,
      ),
    ),
    61 =>
    array (
      'id' => 'rock_and_roll',
      'name' => 'Rock and Roll!',
      'category' => 'combat',
      'summary' => 'The hero controls automatic weapons expertly when braced.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Seasoned',
        ),
        1 =>
        array (
          'type' => 'trait',
          'target' => 'Shooting',
          'value' => 'd8+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'recoil_penalty',
          'operator' => 'ignore',
          'value' => true,
          'conditions' =>
          array (
            0 => 'rof_2_or_more',
            1 => 'character_does_not_move',
          ),
          'details' => 'Ignore Recoil when firing weapons with RoF 2 or more, as long as the character does not move.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 63,
      ),
    ),
    62 =>
    array (
      'id' => 'steady_hands',
      'name' => 'Steady Hands',
      'category' => 'combat',
      'summary' => 'The hero remains accurate on unstable footing.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
        1 =>
        array (
          'type' => 'attribute',
          'target' => 'Agility',
          'value' => 'd8+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'unstable_platform_penalty',
          'operator' => 'ignore',
          'value' => true,
          'conditions' =>
          array (
          ),
          'details' => 'Ignore the Unstable Platform penalty.',
        ),
        1 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'running_penalty',
          'operator' => 'set',
          'value' => -1,
          'conditions' =>
          array (
          ),
          'details' => 'Reduce the running penalty to −1.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 63,
      ),
    ),
    63 =>
    array (
      'id' => 'sweep',
      'name' => 'Sweep',
      'category' => 'combat',
      'summary' => 'The hero can strike every foe in reach with one attack.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
        1 =>
        array (
          'type' => 'attribute',
          'target' => 'Strength',
          'value' => 'd8+',
        ),
        2 =>
        array (
          'type' => 'trait',
          'target' => 'Fighting',
          'value' => 'd8+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'action',
          'polarity' => 'benefit',
          'target' => 'melee_attack_all_targets_in_reach',
          'operator' => 'grant',
          'value' => true,
          'conditions' =>
          array (
            0 => 'limited_action',
            1 => 'with_two_handed_weapon_or_minus_2_without',
          ),
          'details' => 'Make one Fighting roll to hit all targets in the weapon’s Reach.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 63,
      ),
    ),
    64 =>
    array (
      'id' => 'improved_sweep',
      'name' => 'Improved Sweep',
      'category' => 'combat',
      'summary' => 'The hero can Sweep without striking allies.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Veteran',
        ),
        1 =>
        array (
          'type' => 'edge',
          'target' => 'edge',
          'value' => 'Sweep',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'special',
          'polarity' => 'benefit',
          'target' => 'sweep_attack',
          'operator' => 'grant',
          'value' => 'avoid_allies',
          'conditions' =>
          array (
            0 => 'limited_action',
          ),
          'details' => 'Sweep attacks can avoid allies.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 63,
      ),
    ),
    65 =>
    array (
      'id' => 'trademark_weapon',
      'name' => 'Trademark Weapon',
      'category' => 'combat',
      'summary' => 'The hero is especially effective with one signature weapon.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
        1 =>
        array (
          'type' => 'special',
          'target' => 'related_skill',
          'value' => 'd8+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'related_attack_total_with_specific_weapon',
          'operator' => 'add',
          'value' => 1,
          'conditions' =>
          array (
          ),
          'details' => '+1 to Athletics (throwing), Fighting, or Shooting totals with one chosen weapon.',
        ),
        1 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'derived.parry',
          'operator' => 'add',
          'value' => 1,
          'conditions' =>
          array (
            0 => 'while_chosen_weapon_is_readied',
          ),
          'details' => '+1 Parry while the chosen weapon is readied.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 63,
      ),
    ),
    66 =>
    array (
      'id' => 'improved_trademark_weapon',
      'name' => 'Improved Trademark Weapon',
      'category' => 'combat',
      'summary' => 'The hero’s signature weapon bonus improves further.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Seasoned',
        ),
        1 =>
        array (
          'type' => 'edge',
          'target' => 'edge',
          'value' => 'Trademark Weapon',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'related_attack_total_with_specific_weapon',
          'operator' => 'add',
          'value' => 2,
          'conditions' =>
          array (
          ),
          'details' => '+2 to Athletics (throwing), Fighting, or Shooting totals with the chosen weapon.',
        ),
        1 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'derived.parry',
          'operator' => 'add',
          'value' => 2,
          'conditions' =>
          array (
            0 => 'while_chosen_weapon_is_readied',
          ),
          'details' => '+2 Parry while the chosen weapon is readied.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 63,
      ),
    ),
    67 =>
    array (
      'id' => 'two_fisted',
      'name' => 'Two-Fisted',
      'category' => 'combat',
      'summary' => 'The hero can attack with two melee weapons more efficiently.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
        1 =>
        array (
          'type' => 'attribute',
          'target' => 'Agility',
          'value' => 'd8+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'action',
          'polarity' => 'benefit',
          'target' => 'extra_off_hand_melee_attack',
          'operator' => 'grant',
          'value' => 1,
          'conditions' =>
          array (
            0 => 'no_multi_action_penalty',
          ),
          'details' => 'Make one extra Fighting roll with a second melee weapon in the off-hand without the usual Multi-Action penalty.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 63,
      ),
    ),
    68 =>
    array (
      'id' => 'two_gun_kid',
      'name' => 'Two-Gun Kid',
      'category' => 'combat',
      'summary' => 'The hero can fire or throw with two ranged weapons more efficiently.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
        1 =>
        array (
          'type' => 'attribute',
          'target' => 'Agility',
          'value' => 'd8+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'action',
          'polarity' => 'benefit',
          'target' => 'extra_off_hand_ranged_attack',
          'operator' => 'grant',
          'value' => 1,
          'conditions' =>
          array (
            0 => 'no_multi_action_penalty',
          ),
          'details' => 'Make one extra Shooting roll, or Athletics (throwing) roll, with a second ranged weapon in the off-hand without the usual Multi-Action penalty.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 63,
      ),
    ),
    69 =>
    array (
      'id' => 'command',
      'name' => 'Command',
      'category' => 'leadership',
      'summary' => 'The hero improves recovery for allied Extras nearby.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
        1 =>
        array (
          'type' => 'attribute',
          'target' => 'Smarts',
          'value' => 'd6+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'extras_in_command_range_recover_shaken_or_stunned',
          'operator' => 'add',
          'value' => 1,
          'conditions' =>
          array (
          ),
          'details' => 'Extras in Command Range add +1 to Spirit rolls to recover from Shaken and Vigor rolls to recover from Stunned.',
        ),
      ),
      'notes' =>
      array (
        0 => 'Command Range is 5 inches or 10 yards unless modified.',
      ),
      'source_pages' =>
      array (
        0 => 63,
      ),
    ),
    70 =>
    array (
      'id' => 'command_presence',
      'name' => 'Command Presence',
      'category' => 'leadership',
      'summary' => 'The hero can direct allies from farther away.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Seasoned',
        ),
        1 =>
        array (
          'type' => 'edge',
          'target' => 'edge',
          'value' => 'Command',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'command_range',
          'operator' => 'set',
          'value' => '10_inches_20_yards',
          'conditions' =>
          array (
          ),
          'details' => 'Increase Command Range to 10 inches or 20 yards.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 63,
      ),
    ),
    71 =>
    array (
      'id' => 'fervor',
      'name' => 'Fervor',
      'category' => 'leadership',
      'summary' => 'The hero inspires allied Extras to hit harder.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Veteran',
        ),
        1 =>
        array (
          'type' => 'attribute',
          'target' => 'Spirit',
          'value' => 'd8+',
        ),
        2 =>
        array (
          'type' => 'edge',
          'target' => 'edge',
          'value' => 'Command',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'damage',
          'polarity' => 'benefit',
          'target' => 'extras_in_command_range_fighting_damage',
          'operator' => 'add',
          'value' => 1,
          'conditions' =>
          array (
          ),
          'details' => 'Extras in Command Range add +1 to Fighting damage rolls.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 63,
      ),
    ),
    72 =>
    array (
      'id' => 'hold_the_line',
      'name' => 'Hold the Line',
      'category' => 'leadership',
      'summary' => 'The hero stiffens allied defenses.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Seasoned',
        ),
        1 =>
        array (
          'type' => 'attribute',
          'target' => 'Smarts',
          'value' => 'd8+',
        ),
        2 =>
        array (
          'type' => 'edge',
          'target' => 'edge',
          'value' => 'Command',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'extras_in_command_range_toughness',
          'operator' => 'add',
          'value' => 1,
          'conditions' =>
          array (
          ),
          'details' => 'Extras in Command Range gain +1 Toughness.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 63,
      ),
    ),
    73 =>
    array (
      'id' => 'inspire',
      'name' => 'Inspire',
      'category' => 'leadership',
      'summary' => 'The hero can Support a whole group at once with Battle.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Seasoned',
        ),
        1 =>
        array (
          'type' => 'edge',
          'target' => 'edge',
          'value' => 'Command',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'action',
          'polarity' => 'benefit',
          'target' => 'battle_support_for_extras_in_command_range',
          'operator' => 'grant',
          'value' => true,
          'conditions' =>
          array (
            0 => 'limited_action',
          ),
          'details' => 'Make a Battle roll to Support one type of Trait roll and apply it to Extras in Command Range.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 63,
      ),
    ),
    74 =>
    array (
      'id' => 'natural_leader',
      'name' => 'Natural Leader',
      'category' => 'leadership',
      'summary' => 'The hero’s leadership works on allied Wild Cards too.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Seasoned',
        ),
        1 =>
        array (
          'type' => 'attribute',
          'target' => 'Spirit',
          'value' => 'd8+',
        ),
        2 =>
        array (
          'type' => 'edge',
          'target' => 'edge',
          'value' => 'Command',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'grant',
          'polarity' => 'benefit',
          'target' => 'leadership_edges_affect_wild_cards',
          'operator' => 'grant',
          'value' => true,
          'conditions' =>
          array (
          ),
          'details' => 'Leadership Edges now apply to allied Wild Cards.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 63,
      ),
    ),
    75 =>
    array (
      'id' => 'tactician',
      'name' => 'Tactician',
      'category' => 'leadership',
      'summary' => 'The hero can improve allied timing with extra Action Cards.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Seasoned',
        ),
        1 =>
        array (
          'type' => 'attribute',
          'target' => 'Smarts',
          'value' => 'd8+',
        ),
        2 =>
        array (
          'type' => 'edge',
          'target' => 'edge',
          'value' => 'Command',
        ),
        3 =>
        array (
          'type' => 'trait',
          'target' => 'Battle',
          'value' => 'd6+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'action',
          'polarity' => 'benefit',
          'target' => 'allied_extra_action_cards',
          'operator' => 'draw',
          'value' => 1,
          'conditions' =>
          array (
            0 => 'each_turn',
            1 => 'command_range',
          ),
          'details' => 'Draw one extra Action Card each turn that may be assigned to an allied Extra or group of Extras in Command Range.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 63,
      ),
    ),
    76 =>
    array (
      'id' => 'master_tactician',
      'name' => 'Master Tactician',
      'category' => 'leadership',
      'summary' => 'The hero can assign even more extra Action Cards.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Veteran',
        ),
        1 =>
        array (
          'type' => 'edge',
          'target' => 'edge',
          'value' => 'Tactician',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'action',
          'polarity' => 'benefit',
          'target' => 'allied_extra_action_cards',
          'operator' => 'draw',
          'value' => 2,
          'conditions' =>
          array (
            0 => 'each_turn',
            1 => 'command_range',
          ),
          'details' => 'Draw and distribute two extra Action Cards each turn.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 63,
      ),
    ),
    77 =>
    array (
      'id' => 'artificer',
      'name' => 'Artificer',
      'category' => 'power',
      'summary' => 'The hero can create Arcane Devices.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Seasoned',
        ),
        1 =>
        array (
          'type' => 'arcane_background',
          'target' => 'arcane_background',
          'value' => 'Any',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'grant',
          'polarity' => 'benefit',
          'target' => 'arcane_devices',
          'operator' => 'grant',
          'value' => true,
          'conditions' =>
          array (
          ),
          'details' => 'Allows the character to create Arcane Devices.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 64,
      ),
    ),
    78 =>
    array (
      'id' => 'channeling',
      'name' => 'Channeling',
      'category' => 'power',
      'summary' => 'The hero channels power more efficiently on a raise.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Seasoned',
        ),
        1 =>
        array (
          'type' => 'arcane_background',
          'target' => 'arcane_background',
          'value' => 'Any',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'resource',
          'polarity' => 'benefit',
          'target' => 'power_point_cost',
          'operator' => 'subtract',
          'value' => 1,
          'conditions' =>
          array (
            0 => 'on_raise_on_activation_roll',
          ),
          'details' => 'Reduce Power Point cost by 1 on a raise. This can reduce the cost to 0.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 64,
      ),
    ),
    79 =>
    array (
      'id' => 'concentration',
      'name' => 'Concentration',
      'category' => 'power',
      'summary' => 'The hero can sustain powers for longer.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Seasoned',
        ),
        1 =>
        array (
          'type' => 'arcane_background',
          'target' => 'arcane_background',
          'value' => 'Any',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'special',
          'polarity' => 'benefit',
          'target' => 'non_instant_power_duration',
          'operator' => 'double',
          'value' => 2,
          'conditions' =>
          array (
          ),
          'details' => 'Double the base Duration of any non-Instant power, including when maintaining it.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 64,
      ),
    ),
    80 =>
    array (
      'id' => 'extra_effort',
      'name' => 'Extra Effort',
      'category' => 'power',
      'summary' => 'Gifted heroes can temporarily push Focus higher.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Seasoned',
        ),
        1 =>
        array (
          'type' => 'arcane_background',
          'target' => 'arcane_background',
          'value' => 'Gifted',
        ),
        2 =>
        array (
          'type' => 'trait',
          'target' => 'Focus',
          'value' => 'd6+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'special',
          'polarity' => 'benefit',
          'target' => 'skill.focus',
          'operator' => 'add',
          'value' => '+1_or_+2',
          'conditions' =>
          array (
            0 => 'spend_1_pp_for_plus_1',
            1 => 'spend_3_pp_for_plus_2',
          ),
          'details' => 'Increase Focus by +1 for 1 Power Point or +2 for 3 Power Points.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 64,
      ),
    ),
    81 =>
    array (
      'id' => 'gadgeteer',
      'name' => 'Gadgeteer',
      'category' => 'power',
      'summary' => 'Weird scientists can improvise devices that mimic powers.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Seasoned',
        ),
        1 =>
        array (
          'type' => 'arcane_background',
          'target' => 'arcane_background',
          'value' => 'Weird Science',
        ),
        2 =>
        array (
          'type' => 'trait',
          'target' => 'Weird Science',
          'value' => 'd6+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'special',
          'polarity' => 'benefit',
          'target' => 'temporary_device_replicating_another_power',
          'operator' => 'grant',
          'value' => true,
          'conditions' =>
          array (
            0 => 'spend_3_power_points',
          ),
          'details' => 'Spend 3 Power Points to create a device that replicates another power.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 64,
      ),
    ),
    82 =>
    array (
      'id' => 'holy_unholy_warrior',
      'name' => 'Holy/Unholy Warrior',
      'category' => 'power',
      'summary' => 'Miracle workers can spend Power Points to bolster Soak rolls.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Seasoned',
        ),
        1 =>
        array (
          'type' => 'arcane_background',
          'target' => 'arcane_background',
          'value' => 'Miracles',
        ),
        2 =>
        array (
          'type' => 'trait',
          'target' => 'Faith',
          'value' => 'd6+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'soak_roll',
          'operator' => 'add',
          'value' => 'up_to_4',
          'conditions' =>
          array (
            0 => 'spend_1_to_4_power_points',
            1 => 'plus_1_per_power_point',
          ),
          'details' => 'Add +1 to +4 to a final Soak roll, one point per Power Point spent.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 64,
      ),
    ),
    83 =>
    array (
      'id' => 'mentalist',
      'name' => 'Mentalist',
      'category' => 'power',
      'summary' => 'Psionic heroes dominate opposed mental struggles.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Seasoned',
        ),
        1 =>
        array (
          'type' => 'arcane_background',
          'target' => 'arcane_background',
          'value' => 'Psionics',
        ),
        2 =>
        array (
          'type' => 'trait',
          'target' => 'Psionics',
          'value' => 'd6+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'opposed.psionics_rolls',
          'operator' => 'add',
          'value' => 2,
          'conditions' =>
          array (
          ),
          'details' => '+2 to opposed Psionics rolls, whether attacking or defending.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 64,
      ),
    ),
    84 =>
    array (
      'id' => 'new_powers',
      'name' => 'New Powers',
      'category' => 'power',
      'summary' => 'The hero expands their arcane repertoire.',
      'repeatable' => true,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
        1 =>
        array (
          'type' => 'arcane_background',
          'target' => 'arcane_background',
          'value' => 'Any',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'grant',
          'polarity' => 'benefit',
          'target' => 'powers_known',
          'operator' => 'add',
          'value' => 2,
          'conditions' =>
          array (
          ),
          'details' => 'Learn two new powers, or add a new Trapping to an existing power.',
        ),
      ),
      'notes' =>
      array (
        0 => 'This Edge may be taken multiple times.',
      ),
      'source_pages' =>
      array (
        0 => 64,
      ),
    ),
    85 =>
    array (
      'id' => 'power_points',
      'name' => 'Power Points',
      'category' => 'power',
      'summary' => 'The hero gains a larger Power Point pool.',
      'repeatable' => true,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
        1 =>
        array (
          'type' => 'arcane_background',
          'target' => 'arcane_background',
          'value' => 'Any',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'resource',
          'polarity' => 'benefit',
          'target' => 'resource.power_points',
          'operator' => 'add',
          'value' => 5,
          'conditions' =>
          array (
            0 => 'normally_once_per_rank',
          ),
          'details' => 'Gain 5 additional Power Points.',
        ),
      ),
      'notes' =>
      array (
        0 => 'At Legendary Rank, this Edge may be taken as often as desired, but each later purchase grants only 2 Power Points.',
      ),
      'source_pages' =>
      array (
        0 => 64,
      ),
    ),
    86 =>
    array (
      'id' => 'power_surge',
      'name' => 'Power Surge',
      'category' => 'power',
      'summary' => 'A Joker restores the hero’s Power Points.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'wild_card',
          'target' => 'wild_card',
          'value' => true,
        ),
        1 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
        2 =>
        array (
          'type' => 'arcane_background',
          'target' => 'arcane_background',
          'value' => 'Any',
        ),
        3 =>
        array (
          'type' => 'trait',
          'target' => 'arcane_skill',
          'value' => 'd8+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'resource',
          'polarity' => 'benefit',
          'target' => 'resource.power_points',
          'operator' => 'recover',
          'value' => 10,
          'conditions' =>
          array (
            0 => 'when_dealt_a_joker_in_combat',
          ),
          'details' => 'Recover 10 Power Points on a Joker, up to the usual maximum.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 64,
      ),
    ),
    87 =>
    array (
      'id' => 'rapid_recharge',
      'name' => 'Rapid Recharge',
      'category' => 'power',
      'summary' => 'The hero recovers Power Points faster while resting.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Seasoned',
        ),
        1 =>
        array (
          'type' => 'attribute',
          'target' => 'Spirit',
          'value' => 'd6+',
        ),
        2 =>
        array (
          'type' => 'arcane_background',
          'target' => 'arcane_background',
          'value' => 'Any',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'resource',
          'polarity' => 'benefit',
          'target' => 'power_point_recharge_per_hour',
          'operator' => 'set',
          'value' => 10,
          'conditions' =>
          array (
            0 => 'while_resting',
          ),
          'details' => 'Recover 10 Power Points per hour of rest.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 64,
      ),
    ),
    88 =>
    array (
      'id' => 'improved_rapid_recharge',
      'name' => 'Improved Rapid Recharge',
      'category' => 'power',
      'summary' => 'The hero recharges Power Points even faster.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Veteran',
        ),
        1 =>
        array (
          'type' => 'edge',
          'target' => 'edge',
          'value' => 'Rapid Recharge',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'resource',
          'polarity' => 'benefit',
          'target' => 'power_point_recharge_per_hour',
          'operator' => 'set',
          'value' => 20,
          'conditions' =>
          array (
            0 => 'while_resting',
          ),
          'details' => 'Recover 20 Power Points per hour of rest.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 64,
      ),
    ),
    89 =>
    array (
      'id' => 'soul_drain',
      'name' => 'Soul Drain',
      'category' => 'power',
      'summary' => 'The hero can trade Fatigue for Power Points.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Seasoned',
        ),
        1 =>
        array (
          'type' => 'arcane_background',
          'target' => 'arcane_background',
          'value' => 'Any',
        ),
        2 =>
        array (
          'type' => 'trait',
          'target' => 'arcane_skill',
          'value' => 'd10+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'resource',
          'polarity' => 'mixed',
          'target' => 'resource.power_points',
          'operator' => 'recover',
          'value' => 5,
          'conditions' =>
          array (
            0 => 'per_level_of_fatigue_taken',
            1 => 'up_to_two_levels',
          ),
          'details' => 'Recover 5 Power Points for each level of Fatigue taken, up to 10 total.',
        ),
      ),
      'notes' =>
      array (
        0 => 'Fatigue from Soul Drain recovers only naturally and cannot render the hero Incapacitated.',
      ),
      'source_pages' =>
      array (
        0 => 64,
      ),
    ),
    90 =>
    array (
      'id' => 'wizard',
      'name' => 'Wizard',
      'category' => 'power',
      'summary' => 'The hero can change a spell’s Trapping on the fly.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Seasoned',
        ),
        1 =>
        array (
          'type' => 'arcane_background',
          'target' => 'arcane_background',
          'value' => 'Magic',
        ),
        2 =>
        array (
          'type' => 'trait',
          'target' => 'Spellcasting',
          'value' => 'd6+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'special',
          'polarity' => 'benefit',
          'target' => 'spell_trapping',
          'operator' => 'replace',
          'value' => true,
          'conditions' =>
          array (
            0 => 'spend_1_extra_power_point',
          ),
          'details' => 'Spend 1 extra Power Point to change a spell’s Trapping.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 64,
      ),
    ),
    91 =>
    array (
      'id' => 'ace',
      'name' => 'Ace',
      'category' => 'professional',
      'summary' => 'The hero excels at operating and protecting vehicles.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
        1 =>
        array (
          'type' => 'attribute',
          'target' => 'Agility',
          'value' => 'd8+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'boating_driving_piloting_penalties',
          'operator' => 'ignore',
          'value' => 2,
          'conditions' =>
          array (
          ),
          'details' => 'Ignore up to 2 points of penalties on Boating, Driving, or Piloting rolls.',
        ),
        1 =>
        array (
          'level' => 'base',
          'type' => 'grant',
          'polarity' => 'benefit',
          'target' => 'vehicle_soak_with_bennies',
          'operator' => 'grant',
          'value' => true,
          'conditions' =>
          array (
          ),
          'details' => 'May spend Bennies to Soak damage for a vehicle under the hero’s control or command using Boating, Driving, or Piloting.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 64,
      ),
    ),
    92 =>
    array (
      'id' => 'acrobat',
      'name' => 'Acrobat',
      'category' => 'professional',
      'summary' => 'The hero can retry acrobatic athletic maneuvers more reliably.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
        1 =>
        array (
          'type' => 'attribute',
          'target' => 'Agility',
          'value' => 'd8+',
        ),
        2 =>
        array (
          'type' => 'trait',
          'target' => 'Athletics',
          'value' => 'd8+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'reroll',
          'polarity' => 'benefit',
          'target' => 'athletics.balance_tumbling_or_grappling',
          'operator' => 'reroll',
          'value' => 1,
          'conditions' =>
          array (
            0 => 'free_reroll',
          ),
          'details' => 'Gain one free reroll on Athletics totals involving balance, tumbling, or grappling.',
        ),
      ),
      'notes' =>
      array (
        0 => 'Does not affect interrupts, climbing, swimming, or throwing.',
      ),
      'source_pages' =>
      array (
        0 => 64,
      ),
    ),
    93 =>
    array (
      'id' => 'combat_acrobat',
      'name' => 'Combat Acrobat',
      'category' => 'professional',
      'summary' => 'The hero is harder to hit while moving freely in a fight.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Seasoned',
        ),
        1 =>
        array (
          'type' => 'edge',
          'target' => 'edge',
          'value' => 'Acrobat',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'incoming.attack_total',
          'operator' => 'subtract',
          'value' => 1,
          'conditions' =>
          array (
            0 => 'aware_of_attack',
            1 => 'can_move_freely',
            2 => 'no_encumbrance_or_min_strength_penalties',
          ),
          'details' => 'Ranged and melee attacks against the hero are made at −1.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 64,
      ),
    ),
    94 =>
    array (
      'id' => 'assassin',
      'name' => 'Assassin',
      'category' => 'professional',
      'summary' => 'The hero is especially lethal against exposed targets.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
        1 =>
        array (
          'type' => 'attribute',
          'target' => 'Agility',
          'value' => 'd8+',
        ),
        2 =>
        array (
          'type' => 'trait',
          'target' => 'Fighting',
          'value' => 'd6+',
        ),
        3 =>
        array (
          'type' => 'trait',
          'target' => 'Stealth',
          'value' => 'd8+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'damage',
          'polarity' => 'benefit',
          'target' => 'damage_vs_vulnerable_or_drop_target',
          'operator' => 'add',
          'value' => 2,
          'conditions' =>
          array (
          ),
          'details' => '+2 damage against foes who are Vulnerable or when the hero has The Drop.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 64,
      ),
    ),
    95 =>
    array (
      'id' => 'investigator',
      'name' => 'Investigator',
      'category' => 'professional',
      'summary' => 'The hero excels at research and detailed searches.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
        1 =>
        array (
          'type' => 'attribute',
          'target' => 'Smarts',
          'value' => 'd8+',
        ),
        2 =>
        array (
          'type' => 'trait',
          'target' => 'Research',
          'value' => 'd8+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'skill.research',
          'operator' => 'add',
          'value' => 2,
          'conditions' =>
          array (
          ),
          'details' => '+2 to Research rolls.',
        ),
        1 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'skill.notice',
          'operator' => 'add',
          'value' => 2,
          'conditions' =>
          array (
            0 => 'when_searching_for_hidden_or_obscured_clues',
          ),
          'details' => '+2 to certain Notice rolls used to search for important clues or obscured items.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 64,
      ),
    ),
    96 =>
    array (
      'id' => 'jack_of_all_trades',
      'name' => 'Jack-of-All-Trades',
      'category' => 'professional',
      'summary' => 'The hero can temporarily pick up unfamiliar skills.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
        1 =>
        array (
          'type' => 'attribute',
          'target' => 'Smarts',
          'value' => 'd10+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'grant',
          'polarity' => 'benefit',
          'target' => 'temporary_skill',
          'operator' => 'grant',
          'value' => 'd4_or_d6_with_raise',
          'conditions' =>
          array (
            0 => 'after_smarts_roll_and_study',
          ),
          'details' => 'After observing or studying, the hero can gain a temporary d4 in a relevant skill, or d6 with a raise, until switching to a different subject.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 64,
      ),
    ),
    97 =>
    array (
      'id' => 'mcgyver',
      'name' => 'McGyver',
      'category' => 'professional',
      'summary' => 'The hero can improvise useful devices out of scrap.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
        1 =>
        array (
          'type' => 'attribute',
          'target' => 'Smarts',
          'value' => 'd6+',
        ),
        2 =>
        array (
          'type' => 'trait',
          'target' => 'Notice',
          'value' => 'd8+',
        ),
        3 =>
        array (
          'type' => 'trait',
          'target' => 'Repair',
          'value' => 'd6+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'grant',
          'polarity' => 'benefit',
          'target' => 'temporary_improvised_device',
          'operator' => 'grant',
          'value' => true,
          'conditions' =>
          array (
            0 => 'repair_roll',
            1 => 'one_full_turn',
            2 => 'common_resources_available',
          ),
          'details' => 'Create improvised weapons, explosives, tools, or similar devices from scraps.',
        ),
      ),
      'notes' =>
      array (
        0 => 'The device normally lasts until used or until the end of the encounter.',
        1 => 'A critical failure means the right materials are not available this encounter.',
      ),
      'source_pages' =>
      array (
        0 => 64,
      ),
    ),
    98 =>
    array (
      'id' => 'mr_fix_it',
      'name' => 'Mr. Fix It',
      'category' => 'professional',
      'summary' => 'The hero repairs things faster and more effectively.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
        1 =>
        array (
          'type' => 'trait',
          'target' => 'Repair',
          'value' => 'd8+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'skill.repair',
          'operator' => 'add',
          'value' => 2,
          'conditions' =>
          array (
          ),
          'details' => '+2 to Repair rolls.',
        ),
        1 =>
        array (
          'level' => 'base',
          'type' => 'special',
          'polarity' => 'benefit',
          'target' => 'repair_time',
          'operator' => 'halve',
          'value' => 0.5,
          'conditions' =>
          array (
            0 => 'on_raise',
          ),
          'details' => 'On a raise, halve the normal repair time.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 64,
      ),
    ),
    99 =>
    array (
      'id' => 'scholar',
      'name' => 'Scholar',
      'category' => 'professional',
      'summary' => 'The hero is an expert in one knowledge field.',
      'repeatable' => true,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
        1 =>
        array (
          'type' => 'trait',
          'target' => 'Research',
          'value' => 'd8+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'chosen_knowledge_skill',
          'operator' => 'add',
          'value' => 2,
          'conditions' =>
          array (
          ),
          'details' => '+2 to one chosen knowledge-style skill such as Academics, Battle, Occult, Science, or a similar Smarts-based setting skill.',
        ),
      ),
      'notes' =>
      array (
        0 => 'This Edge may be taken more than once for different skills.',
      ),
      'source_pages' =>
      array (
        0 => 64,
      ),
    ),
    100 =>
    array (
      'id' => 'soldier',
      'name' => 'Soldier',
      'category' => 'professional',
      'summary' => 'The hero carries loads well and endures harsh conditions.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
        1 =>
        array (
          'type' => 'attribute',
          'target' => 'Strength',
          'value' => 'd6+',
        ),
        2 =>
        array (
          'type' => 'attribute',
          'target' => 'Vigor',
          'value' => 'd6+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'strength_for_encumbrance_and_minimum_strength',
          'operator' => 'add',
          'value' => 'one_die_type',
          'conditions' =>
          array (
          ),
          'details' => 'Treat Strength as one die type higher for Encumbrance and Minimum Strength requirements.',
        ),
        1 =>
        array (
          'level' => 'base',
          'type' => 'reroll',
          'polarity' => 'benefit',
          'target' => 'vigor.environmental_hazards',
          'operator' => 'reroll',
          'value' => 1,
          'conditions' =>
          array (
            0 => 'free_reroll',
          ),
          'details' => 'Gain a free reroll on Vigor rolls to resist environmental Hazards.',
        ),
      ),
      'notes' =>
      array (
        0 => 'Stacks with Brawny.',
      ),
      'source_pages' =>
      array (
        0 => 64,
      ),
    ),
    101 =>
    array (
      'id' => 'thief',
      'name' => 'Thief',
      'category' => 'professional',
      'summary' => 'The hero excels at urban climbing, stealth, and thievery.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
        1 =>
        array (
          'type' => 'attribute',
          'target' => 'Agility',
          'value' => 'd8+',
        ),
        2 =>
        array (
          'type' => 'trait',
          'target' => 'Stealth',
          'value' => 'd6+',
        ),
        3 =>
        array (
          'type' => 'trait',
          'target' => 'Thievery',
          'value' => 'd6+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'skill.thievery',
          'operator' => 'add',
          'value' => 1,
          'conditions' =>
          array (
          ),
          'details' => '+1 to Thievery rolls.',
        ),
        1 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'skill.athletics',
          'operator' => 'add',
          'value' => 1,
          'conditions' =>
          array (
            0 => 'urban_climbing',
          ),
          'details' => '+1 to Athletics rolls made to climb in urban areas.',
        ),
        2 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'skill.stealth',
          'operator' => 'add',
          'value' => 1,
          'conditions' =>
          array (
            0 => 'urban_environment',
          ),
          'details' => '+1 to Stealth in urban environments.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 64,
      ),
    ),
    102 =>
    array (
      'id' => 'woodsman',
      'name' => 'Woodsman',
      'category' => 'professional',
      'summary' => 'The hero thrives in the wild.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
        1 =>
        array (
          'type' => 'attribute',
          'target' => 'Spirit',
          'value' => 'd6+',
        ),
        2 =>
        array (
          'type' => 'trait',
          'target' => 'Survival',
          'value' => 'd8+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'skill.survival',
          'operator' => 'add',
          'value' => 2,
          'conditions' =>
          array (
          ),
          'details' => '+2 to Survival rolls.',
        ),
        1 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'skill.stealth',
          'operator' => 'add',
          'value' => 2,
          'conditions' =>
          array (
            0 => 'in_the_wild',
          ),
          'details' => '+2 to Stealth in the wilds.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 64,
      ),
    ),
    103 =>
    array (
      'id' => 'bolster',
      'name' => 'Bolster',
      'category' => 'social',
      'summary' => 'The hero can steady an ally after a Test.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
        1 =>
        array (
          'type' => 'attribute',
          'target' => 'Spirit',
          'value' => 'd8+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'status',
          'polarity' => 'benefit',
          'target' => 'status.distracted_or_vulnerable_on_ally',
          'operator' => 'remove',
          'value' => true,
          'conditions' =>
          array (
            0 => 'after_a_test',
          ),
          'details' => 'May remove Distracted or Vulnerable after a Test.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 65,
      ),
    ),
    104 =>
    array (
      'id' => 'common_bond',
      'name' => 'Common Bond',
      'category' => 'social',
      'summary' => 'The hero can freely pass Bennies to allies.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'wild_card',
          'target' => 'wild_card',
          'value' => true,
        ),
        1 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
        2 =>
        array (
          'type' => 'attribute',
          'target' => 'Spirit',
          'value' => 'd8+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'grant',
          'polarity' => 'benefit',
          'target' => 'transfer_bennies_to_others',
          'operator' => 'grant',
          'value' => true,
          'conditions' =>
          array (
          ),
          'details' => 'The hero may freely give Bennies to others.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 65,
      ),
    ),
    105 =>
    array (
      'id' => 'connections',
      'name' => 'Connections',
      'category' => 'social',
      'summary' => 'The hero can call on contacts for help.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'grant',
          'polarity' => 'benefit',
          'target' => 'contact_favor',
          'operator' => 'grant',
          'value' => 1,
          'conditions' =>
          array (
            0 => 'once_per_session',
          ),
          'details' => 'Contacts can provide aid or other favors once per session.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 65,
      ),
    ),
    106 =>
    array (
      'id' => 'humiliate',
      'name' => 'Humiliate',
      'category' => 'social',
      'summary' => 'The hero can lean on Taunt more effectively.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
        1 =>
        array (
          'type' => 'trait',
          'target' => 'Taunt',
          'value' => 'd8+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'reroll',
          'polarity' => 'benefit',
          'target' => 'skill.taunt',
          'operator' => 'reroll',
          'value' => 1,
          'conditions' =>
          array (
            0 => 'free_reroll',
          ),
          'details' => 'Gain a free reroll on Taunt rolls.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 65,
      ),
    ),
    107 =>
    array (
      'id' => 'menacing',
      'name' => 'Menacing',
      'category' => 'social',
      'summary' => 'The hero turns a rough reputation into social pressure.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
        1 =>
        array (
          'type' => 'hindrance_any_of',
          'target' => 'hindrance',
          'value' =>
          array (
            0 => 'Bloodthirsty',
            1 => 'Mean',
            2 => 'Ruthless',
            3 => 'Ugly',
          ),
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'skill.intimidation',
          'operator' => 'add',
          'value' => 2,
          'conditions' =>
          array (
          ),
          'details' => '+2 to Intimidation rolls.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 65,
      ),
    ),
    108 =>
    array (
      'id' => 'provoke',
      'name' => 'Provoke',
      'category' => 'social',
      'summary' => 'The hero can draw enemy attention onto themself.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
        1 =>
        array (
          'type' => 'trait',
          'target' => 'Taunt',
          'value' => 'd6+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'provoked_target_affecting_other_targets',
          'operator' => 'subtract',
          'value' => 2,
          'conditions' =>
          array (
            0 => 'once_per_turn',
            1 => 'after_taunt_test_with_raise',
          ),
          'details' => 'A provoked foe suffers −2 to affect targets other than the character who provoked them.',
        ),
      ),
      'notes' =>
      array (
        0 => 'The effect lasts until a Joker is drawn, someone else provokes the target, or the encounter ends.',
        1 => 'Stacks with Distracted but not with additional instances of Provoke.',
      ),
      'source_pages' =>
      array (
        0 => 65,
      ),
    ),
    109 =>
    array (
      'id' => 'rabble_rouser',
      'name' => 'Rabble-Rouser',
      'category' => 'social',
      'summary' => 'The hero can apply a social Test to a group at once.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Seasoned',
        ),
        1 =>
        array (
          'type' => 'attribute',
          'target' => 'Spirit',
          'value' => 'd8+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'action',
          'polarity' => 'benefit',
          'target' => 'social_test_area_of_effect',
          'operator' => 'grant',
          'value' => 'medium_blast_template',
          'conditions' =>
          array (
            0 => 'limited_action',
            1 => 'using_intimidation_or_taunt',
          ),
          'details' => 'Affect all foes in a Medium Blast Template with an Intimidation or Taunt Test.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 65,
      ),
    ),
    110 =>
    array (
      'id' => 'reliable',
      'name' => 'Reliable',
      'category' => 'social',
      'summary' => 'The hero supports allies dependably.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
        1 =>
        array (
          'type' => 'attribute',
          'target' => 'Spirit',
          'value' => 'd8+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'reroll',
          'polarity' => 'benefit',
          'target' => 'support_rolls',
          'operator' => 'reroll',
          'value' => 1,
          'conditions' =>
          array (
            0 => 'free_reroll',
          ),
          'details' => 'Gain a free reroll on Support rolls.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 65,
      ),
    ),
    111 =>
    array (
      'id' => 'retort',
      'name' => 'Retort',
      'category' => 'social',
      'summary' => 'The hero turns social attacks back on the attacker.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
        1 =>
        array (
          'type' => 'trait',
          'target' => 'Taunt',
          'value' => 'd6+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'status',
          'polarity' => 'benefit',
          'target' => 'foe',
          'operator' => 'grant',
          'value' => 'distracted',
          'conditions' =>
          array (
            0 => 'on_raise_when_resisting_taunt_or_intimidation',
          ),
          'details' => 'If the hero gets a raise when resisting Taunt or Intimidation, the foe becomes Distracted.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 65,
      ),
    ),
    112 =>
    array (
      'id' => 'streetwise',
      'name' => 'Streetwise',
      'category' => 'social',
      'summary' => 'The hero knows how to work criminal and shady circles.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
        1 =>
        array (
          'type' => 'attribute',
          'target' => 'Smarts',
          'value' => 'd6+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'criminal_networking',
          'operator' => 'add',
          'value' => 2,
          'conditions' =>
          array (
          ),
          'details' => '+2 to Intimidation or Persuasion rolls made to Network with shady or criminal elements.',
        ),
        1 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'skill.common_knowledge',
          'operator' => 'add',
          'value' => 2,
          'conditions' =>
          array (
            0 => 'criminal_or_disreputable_topics',
          ),
          'details' => '+2 to Common Knowledge rolls about black markets, fencing, illegal weapons, and similar topics.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 65,
      ),
    ),
    113 =>
    array (
      'id' => 'strong_willed',
      'name' => 'Strong Willed',
      'category' => 'social',
      'summary' => 'The hero resists mental and social pressure better.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
        1 =>
        array (
          'type' => 'attribute',
          'target' => 'Spirit',
          'value' => 'd8+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'resist.smarts_or_spirit_tests',
          'operator' => 'add',
          'value' => 2,
          'conditions' =>
          array (
          ),
          'details' => '+2 to resist Smarts- or Spirit-based Tests.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 65,
      ),
    ),
    114 =>
    array (
      'id' => 'iron_will',
      'name' => 'Iron Will',
      'category' => 'social',
      'summary' => 'The hero’s mental resilience also applies against powers.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Seasoned',
        ),
        1 =>
        array (
          'type' => 'edge',
          'target' => 'edge',
          'value' => 'Brave',
        ),
        2 =>
        array (
          'type' => 'edge',
          'target' => 'edge',
          'value' => 'Strong Willed',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'resist_and_recover_from_powers',
          'operator' => 'add',
          'value' => 2,
          'conditions' =>
          array (
          ),
          'details' => 'Apply the Strong Willed bonus to resisting powers and negating their effects.',
        ),
      ),
      'notes' =>
      array (
        0 => 'Does not stack with Brave and does not apply to later rolls caused by powers, such as fear from a damaging effect.',
      ),
      'source_pages' =>
      array (
        0 => 65,
      ),
    ),
    115 =>
    array (
      'id' => 'work_the_room',
      'name' => 'Work the Room',
      'category' => 'social',
      'summary' => 'The hero can Support two allies at once through words or performance.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
        1 =>
        array (
          'type' => 'attribute',
          'target' => 'Spirit',
          'value' => 'd8+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'action',
          'polarity' => 'benefit',
          'target' => 'support_additional_allies',
          'operator' => 'grant',
          'value' => 1,
          'conditions' =>
          array (
            0 => 'limited_action',
            1 => 'using_performance_or_persuasion',
          ),
          'details' => 'Roll a second skill die when Supporting with Performance or Persuasion and apply it to another ally.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 65,
      ),
    ),
    116 =>
    array (
      'id' => 'work_the_crowd',
      'name' => 'Work the Crowd',
      'category' => 'social',
      'summary' => 'The hero can Support three allies at once through words or performance.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Seasoned',
        ),
        1 =>
        array (
          'type' => 'edge',
          'target' => 'edge',
          'value' => 'Work the Room',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'action',
          'polarity' => 'benefit',
          'target' => 'support_additional_allies',
          'operator' => 'grant',
          'value' => 2,
          'conditions' =>
          array (
            0 => 'limited_action',
            1 => 'using_performance_or_persuasion',
          ),
          'details' => 'As Work the Room, but roll a third skill die and Support an additional ally.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 65,
      ),
    ),
    117 =>
    array (
      'id' => 'beast_bond',
      'name' => 'Beast Bond',
      'category' => 'weird',
      'summary' => 'The hero may spend Bennies on animals under their control.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'grant',
          'polarity' => 'benefit',
          'target' => 'spend_bennies_for_controlled_animals',
          'operator' => 'grant',
          'value' => true,
          'conditions' =>
          array (
          ),
          'details' => 'The hero may spend Bennies for animals under their control, including mounts, pets, and familiars.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 65,
      ),
    ),
    118 =>
    array (
      'id' => 'beast_master',
      'name' => 'Beast Master',
      'category' => 'weird',
      'summary' => 'Animals like the hero, and one becomes a loyal companion.',
      'repeatable' => true,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
        1 =>
        array (
          'type' => 'attribute',
          'target' => 'Spirit',
          'value' => 'd8+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'grant',
          'polarity' => 'benefit',
          'target' => 'pet_companion',
          'operator' => 'grant',
          'value' => 'size_0_or_smaller_extra',
          'conditions' =>
          array (
          ),
          'details' => 'The hero gains a loyal animal companion, typically an Extra of Size 0 or smaller.',
        ),
      ),
      'notes' =>
      array (
        0 => 'Animals generally will not attack the hero unless attacked first or enraged.',
        1 => 'If the pet is lost or killed, a replacement appears in 1d4 days.',
        2 => 'This Edge may be taken multiple times for extra pets, trait improvements, bigger pets, or one pet becoming a Wild Card at Heroic Rank.',
      ),
      'source_pages' =>
      array (
        0 => 65,
      ),
    ),
    119 =>
    array (
      'id' => 'champion',
      'name' => 'Champion',
      'category' => 'weird',
      'summary' => 'The hero is chosen to battle supernatural evil or good.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
        1 =>
        array (
          'type' => 'attribute',
          'target' => 'Spirit',
          'value' => 'd8+',
        ),
        2 =>
        array (
          'type' => 'trait',
          'target' => 'Fighting',
          'value' => 'd6+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'damage',
          'polarity' => 'benefit',
          'target' => 'damage_vs_supernaturally_aligned_targets',
          'operator' => 'add',
          'value' => 2,
          'conditions' =>
          array (
          ),
          'details' => '+2 damage against supernaturally evil creatures, or supernaturally good creatures if the champion serves an evil cause.',
        ),
      ),
      'notes' =>
      array (
        0 => 'Applies to area effect damage, ranged attacks, and powers as well as melee attacks.',
      ),
      'source_pages' =>
      array (
        0 => 65,
      ),
    ),
    120 =>
    array (
      'id' => 'chi',
      'name' => 'Chi',
      'category' => 'weird',
      'summary' => 'The hero gains a mystical combat point each fight.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Veteran',
        ),
        1 =>
        array (
          'type' => 'edge',
          'target' => 'edge',
          'value' => 'Martial Warrior',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'resource',
          'polarity' => 'benefit',
          'target' => 'resource.chi_points',
          'operator' => 'set',
          'value' => 1,
          'conditions' =>
          array (
            0 => 'at_start_of_each_combat',
          ),
          'details' => 'Gain 1 Chi Point at the start of each combat.',
        ),
      ),
      'notes' =>
      array (
        0 => 'A Chi Point can reroll a failed attack, force an enemy to reroll an attack against the hero, or add +d6 damage to an unarmed or Natural Weapon Fighting attack.',
        1 => 'Unspent Chi is lost at the end of the encounter.',
      ),
      'source_pages' =>
      array (
        0 => 65,
      ),
    ),
    121 =>
    array (
      'id' => 'danger_sense',
      'name' => 'Danger Sense',
      'category' => 'weird',
      'summary' => 'The hero senses trouble before it strikes.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'notice.surprise_roll',
          'operator' => 'add',
          'value' => 2,
          'conditions' =>
          array (
          ),
          'details' => '+2 to Notice when rolling for Surprise.',
        ),
      ),
      'notes' =>
      array (
        0 => 'With a raise on Surprise, the hero starts on Hold.',
        1 => 'Outside formal Surprise, the hero usually gets a Notice roll to detect hazards or prevent The Drop.',
      ),
      'source_pages' =>
      array (
        0 => 65,
      ),
    ),
    122 =>
    array (
      'id' => 'healer',
      'name' => 'Healer',
      'category' => 'weird',
      'summary' => 'The hero is unusually good at healing of any kind.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
        1 =>
        array (
          'type' => 'attribute',
          'target' => 'Spirit',
          'value' => 'd8+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'skill.healing',
          'operator' => 'add',
          'value' => 2,
          'conditions' =>
          array (
          ),
          'details' => '+2 to all Healing rolls, magical or otherwise.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 65,
      ),
    ),
    123 =>
    array (
      'id' => 'liquid_courage',
      'name' => 'Liquid Courage',
      'category' => 'weird',
      'summary' => 'Alcohol empowers the hero before the crash hits.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
        1 =>
        array (
          'type' => 'attribute',
          'target' => 'Vigor',
          'value' => 'd8+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'mixed',
          'target' => 'attribute.vigor',
          'operator' => 'add',
          'value' => 'one_die_type',
          'conditions' =>
          array (
            0 => 'for_one_hour_after_strong_drink',
          ),
          'details' => 'Vigor increases one die type for one hour after a stiff drink.',
        ),
        1 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'wound_penalties',
          'operator' => 'ignore',
          'value' => 1,
          'conditions' =>
          array (
            0 => 'for_one_hour_after_strong_drink',
          ),
          'details' => 'Ignore one level of Wound penalties while the effect lasts.',
        ),
        2 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'penalty',
          'target' => 'agility_smarts_and_linked_skills',
          'operator' => 'subtract',
          'value' => 1,
          'conditions' =>
          array (
            0 => 'for_one_hour_after_strong_drink',
          ),
          'details' => 'Agility, Smarts, and linked skills suffer −1 while the effect lasts.',
        ),
      ),
      'notes' =>
      array (
        0 => 'After the effect ends, the hero suffers one level of Fatigue for four hours.',
      ),
      'source_pages' =>
      array (
        0 => 65,
      ),
    ),
    124 =>
    array (
      'id' => 'scavenger',
      'name' => 'Scavenger',
      'category' => 'weird',
      'summary' => 'The hero can turn up something useful when it matters.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Novice',
        ),
        1 =>
        array (
          'type' => 'edge',
          'target' => 'edge',
          'value' => 'Luck',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'grant',
          'polarity' => 'benefit',
          'target' => 'needed_item',
          'operator' => 'grant',
          'value' => 1,
          'conditions' =>
          array (
            0 => 'once_per_encounter',
          ),
          'details' => 'Once per encounter, the hero may find or produce one useful needed item, ammunition, or small device.',
        ),
      ),
      'notes' =>
      array (
        0 => 'The GM decides what counts as an encounter and what item can reasonably be found.',
      ),
      'source_pages' =>
      array (
        0 => 65,
      ),
    ),
    125 =>
    array (
      'id' => 'followers',
      'name' => 'Followers',
      'category' => 'legendary',
      'summary' => 'The hero attracts a small band of followers.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'wild_card',
          'target' => 'wild_card',
          'value' => true,
        ),
        1 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Legendary',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'grant',
          'polarity' => 'benefit',
          'target' => 'followers',
          'operator' => 'grant',
          'value' => 5,
          'conditions' =>
          array (
          ),
          'details' => 'The hero gains five followers.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 65,
      ),
    ),
    126 =>
    array (
      'id' => 'professional',
      'name' => 'Professional',
      'category' => 'legendary',
      'summary' => 'The hero pushes one Trait beyond the normal cap.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Legendary',
        ),
        1 =>
        array (
          'type' => 'special',
          'target' => 'trait_maxed',
          'value' => true,
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'chosen_trait',
          'operator' => 'add',
          'value' => 'one_die_type',
          'conditions' =>
          array (
          ),
          'details' => 'Increase one chosen Trait and its maximum by one die type.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 65,
      ),
    ),
    127 =>
    array (
      'id' => 'expert',
      'name' => 'Expert',
      'category' => 'legendary',
      'summary' => 'The hero further advances the same mastered Trait.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Legendary',
        ),
        1 =>
        array (
          'type' => 'edge',
          'target' => 'edge',
          'value' => 'Professional (same trait)',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'chosen_trait',
          'operator' => 'add',
          'value' => 'one_die_type',
          'conditions' =>
          array (
          ),
          'details' => 'Increase the same Trait and its maximum by another die type.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 65,
      ),
    ),
    128 =>
    array (
      'id' => 'master',
      'name' => 'Master',
      'category' => 'legendary',
      'summary' => 'The hero’s Wild Die improves for one mastered Trait.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'wild_card',
          'target' => 'wild_card',
          'value' => true,
        ),
        1 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Legendary',
        ),
        2 =>
        array (
          'type' => 'edge',
          'target' => 'edge',
          'value' => 'Expert (same trait)',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'special',
          'polarity' => 'benefit',
          'target' => 'wild_die_for_chosen_trait',
          'operator' => 'set',
          'value' => 'd10',
          'conditions' =>
          array (
          ),
          'details' => 'The character’s Wild Die becomes a d10 when using one chosen Trait.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 65,
      ),
    ),
    129 =>
    array (
      'id' => 'sidekick',
      'name' => 'Sidekick',
      'category' => 'legendary',
      'summary' => 'The hero gains a Wild Card companion.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'wild_card',
          'target' => 'wild_card',
          'value' => true,
        ),
        1 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Legendary',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'grant',
          'polarity' => 'benefit',
          'target' => 'sidekick',
          'operator' => 'grant',
          'value' => 'wild_card_companion',
          'conditions' =>
          array (
          ),
          'details' => 'The hero gains a Wild Card sidekick.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 65,
      ),
    ),
    130 =>
    array (
      'id' => 'tough_as_nails',
      'name' => 'Tough as Nails',
      'category' => 'legendary',
      'summary' => 'The hero can take more Wounds before going down.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Legendary',
        ),
        1 =>
        array (
          'type' => 'attribute',
          'target' => 'Vigor',
          'value' => 'd8+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'special',
          'polarity' => 'benefit',
          'target' => 'wounds_before_incapacitated',
          'operator' => 'set',
          'value' => 4,
          'conditions' =>
          array (
          ),
          'details' => 'The hero can take four Wounds before becoming Incapacitated.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 65,
      ),
    ),
    131 =>
    array (
      'id' => 'tougher_than_nails',
      'name' => 'Tougher than Nails',
      'category' => 'legendary',
      'summary' => 'The hero can survive even more punishment.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Legendary',
        ),
        1 =>
        array (
          'type' => 'edge',
          'target' => 'edge',
          'value' => 'Tough as Nails',
        ),
        2 =>
        array (
          'type' => 'attribute',
          'target' => 'Vigor',
          'value' => 'd12+',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'special',
          'polarity' => 'benefit',
          'target' => 'wounds_before_incapacitated',
          'operator' => 'set',
          'value' => 5,
          'conditions' =>
          array (
          ),
          'details' => 'The hero can take five Wounds before becoming Incapacitated.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 65,
      ),
    ),
    132 =>
    array (
      'id' => 'weapon_master',
      'name' => 'Weapon Master',
      'category' => 'legendary',
      'summary' => 'The hero reaches peerless mastery with melee weapons.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Legendary',
        ),
        1 =>
        array (
          'type' => 'trait',
          'target' => 'Fighting',
          'value' => 'd12',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'derived.parry',
          'operator' => 'add',
          'value' => 1,
          'conditions' =>
          array (
          ),
          'details' => '+1 Parry.',
        ),
        1 =>
        array (
          'level' => 'base',
          'type' => 'damage',
          'polarity' => 'benefit',
          'target' => 'fighting_bonus_damage_die',
          'operator' => 'set',
          'value' => 'd8',
          'conditions' =>
          array (
          ),
          'details' => 'The hero’s Fighting bonus damage die becomes d8.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 65,
      ),
    ),
    133 =>
    array (
      'id' => 'master_of_arms',
      'name' => 'Master of Arms',
      'category' => 'legendary',
      'summary' => 'The hero advances from weapon mastery into near perfection.',
      'repeatable' => false,
      'requirements' =>
      array (
        0 =>
        array (
          'type' => 'rank',
          'target' => 'rank',
          'value' => 'Legendary',
        ),
        1 =>
        array (
          'type' => 'edge',
          'target' => 'edge',
          'value' => 'Weapon Master',
        ),
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'base',
          'type' => 'modifier',
          'polarity' => 'benefit',
          'target' => 'derived.parry',
          'operator' => 'add',
          'value' => 1,
          'conditions' =>
          array (
          ),
          'details' => 'Gain another +1 Parry.',
        ),
        1 =>
        array (
          'level' => 'base',
          'type' => 'damage',
          'polarity' => 'benefit',
          'target' => 'fighting_bonus_damage_die',
          'operator' => 'set',
          'value' => 'd10',
          'conditions' =>
          array (
          ),
          'details' => 'The hero’s Fighting bonus damage die becomes d10.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 65,
      ),
    ),
  ),
);
