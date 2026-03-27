<?php

declare(strict_types=1);

return array (
  'schema_version' => '1.0',
  'source_pdf' => 'Savage Worlds Adventure Edition v5.pdf',
  'entry_type' => 'hindrance',
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
      'id' => 'all_thumbs',
      'name' => 'All Thumbs',
      'levels' =>
      array (
        0 => 'minor',
      ),
      'summary' => 'The hero is bad with mechanical or electrical devices.',
      'requirements' =>
      array (
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'minor',
          'type' => 'modifier',
          'polarity' => 'penalty',
          'target' => 'trait.using_mechanical_or_electrical_device',
          'operator' => 'subtract',
          'value' => 2,
          'conditions' =>
          array (
          ),
          'details' => 'Applies to Trait rolls made while using mechanical or electrical devices.',
        ),
        1 =>
        array (
          'level' => 'minor',
          'type' => 'special',
          'polarity' => 'penalty',
          'target' => 'device.used_by_character',
          'operator' => 'break',
          'value' => true,
          'conditions' =>
          array (
            0 => 'on_critical_failure',
          ),
          'details' => 'A critical failure can break the device.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 24,
      ),
    ),
    1 =>
    array (
      'id' => 'anemic',
      'name' => 'Anemic',
      'levels' =>
      array (
        0 => 'minor',
      ),
      'summary' => 'The hero is especially vulnerable to fatigue and related hazards.',
      'requirements' =>
      array (
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'minor',
          'type' => 'modifier',
          'polarity' => 'penalty',
          'target' => 'trait.vigor_resist_fatigue',
          'operator' => 'subtract',
          'value' => 2,
          'conditions' =>
          array (
          ),
          'details' => 'Subtract 2 from Vigor rolls made to resist Fatigue.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 24,
      ),
    ),
    2 =>
    array (
      'id' => 'arrogant',
      'name' => 'Arrogant',
      'levels' =>
      array (
        0 => 'major',
      ),
      'summary' => 'The hero believes they are the best and wants to prove it.',
      'requirements' =>
      array (
      ),
      'effects' =>
      array (
      ),
      'notes' =>
      array (
        0 => 'Roleplaying Hindrance that pushes the character to challenge the greatest threat and dominate opponents.',
      ),
      'source_pages' =>
      array (
        0 => 24,
      ),
    ),
    3 =>
    array (
      'id' => 'bad_eyes',
      'name' => 'Bad Eyes',
      'levels' =>
      array (
        0 => 'minor',
        1 => 'major',
      ),
      'summary' => 'Poor eyesight reduces performance on vision-dependent tasks.',
      'requirements' =>
      array (
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'minor',
          'type' => 'modifier',
          'polarity' => 'penalty',
          'target' => 'trait.vision_dependent',
          'operator' => 'subtract',
          'value' => 1,
          'conditions' =>
          array (
          ),
          'details' => 'Subtract 1 from any Trait roll dependent on vision.',
        ),
        1 =>
        array (
          'level' => 'major',
          'type' => 'modifier',
          'polarity' => 'penalty',
          'target' => 'trait.vision_dependent',
          'operator' => 'subtract',
          'value' => 2,
          'conditions' =>
          array (
          ),
          'details' => 'Subtract 2 from any Trait roll dependent on vision.',
        ),
        2 =>
        array (
          'level' => 'minor',
          'type' => 'status',
          'polarity' => 'penalty',
          'target' => 'status.distracted',
          'operator' => 'grant',
          'value' => true,
          'conditions' =>
          array (
            0 => 'if_corrective_glasses_lost_or_broken',
            1 => 'until_end_of_next_turn',
          ),
          'details' => 'If glasses are lost or broken in a setting where they exist, the character is Distracted.',
        ),
        3 =>
        array (
          'level' => 'major',
          'type' => 'status',
          'polarity' => 'penalty',
          'target' => 'status.vulnerable',
          'operator' => 'grant',
          'value' => true,
          'conditions' =>
          array (
            0 => 'if_corrective_glasses_lost_or_broken',
            1 => 'until_end_of_next_turn',
          ),
          'details' => 'If glasses are lost or broken in a setting where they exist, the character is also Vulnerable.',
        ),
      ),
      'notes' =>
      array (
        0 => 'Glasses negate the vision penalty when worn in settings where they are available.',
      ),
      'source_pages' =>
      array (
        0 => 24,
      ),
    ),
    4 =>
    array (
      'id' => 'bad_luck',
      'name' => 'Bad Luck',
      'levels' =>
      array (
        0 => 'major',
      ),
      'summary' => 'The hero begins each session with less luck than normal.',
      'requirements' =>
      array (
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'major',
          'type' => 'resource',
          'polarity' => 'penalty',
          'target' => 'resource.bennies.start_session',
          'operator' => 'subtract',
          'value' => 1,
          'conditions' =>
          array (
          ),
          'details' => 'The character gets one fewer Benny per session than normal.',
        ),
      ),
      'notes' =>
      array (
        0 => 'A character cannot have both Bad Luck and the Luck Edge.',
      ),
      'source_pages' =>
      array (
        0 => 24,
      ),
    ),
    5 =>
    array (
      'id' => 'big_mouth',
      'name' => 'Big Mouth',
      'levels' =>
      array (
        0 => 'minor',
      ),
      'summary' => 'The hero has trouble keeping secrets.',
      'requirements' =>
      array (
      ),
      'effects' =>
      array (
      ),
      'notes' =>
      array (
        0 => 'Roleplaying Hindrance that causes plans or private information to be revealed at bad times.',
      ),
      'source_pages' =>
      array (
        0 => 25,
      ),
    ),
    6 =>
    array (
      'id' => 'blind',
      'name' => 'Blind',
      'levels' =>
      array (
        0 => 'major',
      ),
      'summary' => 'The hero is completely blind.',
      'requirements' =>
      array (
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'major',
          'type' => 'modifier',
          'polarity' => 'penalty',
          'target' => 'physical_task.vision_dependent',
          'operator' => 'subtract',
          'value' => 6,
          'conditions' =>
          array (
          ),
          'details' => 'Subtract 6 from all physical tasks that require sight.',
        ),
        1 =>
        array (
          'level' => 'major',
          'type' => 'grant',
          'polarity' => 'benefit',
          'target' => 'character.edge_choice',
          'operator' => 'grant',
          'value' => 1,
          'conditions' =>
          array (
          ),
          'details' => 'Gain one free Edge as compensation.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 25,
      ),
    ),
    7 =>
    array (
      'id' => 'bloodthirsty',
      'name' => 'Bloodthirsty',
      'levels' =>
      array (
        0 => 'major',
      ),
      'summary' => 'The hero does not take prisoners unless strictly supervised.',
      'requirements' =>
      array (
      ),
      'effects' =>
      array (
      ),
      'notes' =>
      array (
        0 => 'Roleplaying Hindrance that creates enemies, loses information, and causes trouble with allies or authorities.',
      ),
      'source_pages' =>
      array (
        0 => 25,
      ),
    ),
    8 =>
    array (
      'id' => 'cant_swim',
      'name' => 'Can\'t Swim',
      'levels' =>
      array (
        0 => 'minor',
      ),
      'summary' => 'The hero is especially poor at swimming.',
      'requirements' =>
      array (
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'minor',
          'type' => 'modifier',
          'polarity' => 'penalty',
          'target' => 'skill.athletics_swimming',
          'operator' => 'subtract',
          'value' => 2,
          'conditions' =>
          array (
          ),
          'details' => 'Subtract 2 from Athletics when swimming.',
        ),
        1 =>
        array (
          'level' => 'minor',
          'type' => 'special',
          'polarity' => 'penalty',
          'target' => 'movement.water_cost_per_inch',
          'operator' => 'set',
          'value' => 3,
          'conditions' =>
          array (
          ),
          'details' => 'Each inch moved in water costs 3 inches of Pace.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 25,
      ),
    ),
    9 =>
    array (
      'id' => 'cautious',
      'name' => 'Cautious',
      'levels' =>
      array (
        0 => 'minor',
      ),
      'summary' => 'The hero avoids rash action and overplans.',
      'requirements' =>
      array (
      ),
      'effects' =>
      array (
      ),
      'notes' =>
      array (
        0 => 'Roleplaying Hindrance focused on restraint and careful planning.',
      ),
      'source_pages' =>
      array (
        0 => 25,
      ),
    ),
    10 =>
    array (
      'id' => 'clueless',
      'name' => 'Clueless',
      'levels' =>
      array (
        0 => 'major',
      ),
      'summary' => 'The hero misses obvious facts and surroundings.',
      'requirements' =>
      array (
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'major',
          'type' => 'modifier',
          'polarity' => 'penalty',
          'target' => 'skill.common_knowledge',
          'operator' => 'subtract',
          'value' => 1,
          'conditions' =>
          array (
          ),
          'details' => 'Subtract 1 from Common Knowledge rolls.',
        ),
        1 =>
        array (
          'level' => 'major',
          'type' => 'modifier',
          'polarity' => 'penalty',
          'target' => 'skill.notice',
          'operator' => 'subtract',
          'value' => 1,
          'conditions' =>
          array (
          ),
          'details' => 'Subtract 1 from Notice rolls.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 25,
      ),
    ),
    11 =>
    array (
      'id' => 'clumsy',
      'name' => 'Clumsy',
      'levels' =>
      array (
        0 => 'major',
      ),
      'summary' => 'The hero is badly uncoordinated.',
      'requirements' =>
      array (
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'major',
          'type' => 'modifier',
          'polarity' => 'penalty',
          'target' => 'skill.athletics',
          'operator' => 'subtract',
          'value' => 2,
          'conditions' =>
          array (
          ),
          'details' => 'Subtract 2 from Athletics rolls.',
        ),
        1 =>
        array (
          'level' => 'major',
          'type' => 'modifier',
          'polarity' => 'penalty',
          'target' => 'skill.stealth',
          'operator' => 'subtract',
          'value' => 2,
          'conditions' =>
          array (
          ),
          'details' => 'Subtract 2 from Stealth rolls.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 25,
      ),
    ),
    12 =>
    array (
      'id' => 'code_of_honor',
      'name' => 'Code of Honor',
      'levels' =>
      array (
        0 => 'major',
      ),
      'summary' => 'The hero follows a strict moral code.',
      'requirements' =>
      array (
      ),
      'effects' =>
      array (
      ),
      'notes' =>
      array (
        0 => 'Roleplaying Hindrance centered on keeping one’s word and avoiding cruel or dishonorable conduct.',
      ),
      'source_pages' =>
      array (
        0 => 25,
      ),
    ),
    13 =>
    array (
      'id' => 'curious',
      'name' => 'Curious',
      'levels' =>
      array (
        0 => 'major',
      ),
      'summary' => 'The hero must investigate secrets and mysteries.',
      'requirements' =>
      array (
      ),
      'effects' =>
      array (
      ),
      'notes' =>
      array (
        0 => 'Roleplaying Hindrance that pushes the character to explore dangerous unknowns.',
      ),
      'source_pages' =>
      array (
        0 => 25,
      ),
    ),
    14 =>
    array (
      'id' => 'death_wish',
      'name' => 'Death Wish',
      'levels' =>
      array (
        0 => 'minor',
      ),
      'summary' => 'The hero will take extreme risks in pursuit of a noble but deadly goal.',
      'requirements' =>
      array (
      ),
      'effects' =>
      array (
      ),
      'notes' =>
      array (
        0 => 'Roleplaying Hindrance; not suicidal, but willing to risk everything for the goal.',
      ),
      'source_pages' =>
      array (
        0 => 25,
      ),
    ),
    15 =>
    array (
      'id' => 'delusional',
      'name' => 'Delusional',
      'levels' =>
      array (
        0 => 'minor',
        1 => 'major',
      ),
      'summary' => 'The hero strongly believes something bizarre or false.',
      'requirements' =>
      array (
      ),
      'effects' =>
      array (
      ),
      'notes' =>
      array (
        0 => 'Minor Delusions are mostly private or harmless.',
        1 => 'Major Delusions are voiced openly and can create danger.',
      ),
      'source_pages' =>
      array (
        0 => 25,
      ),
    ),
    16 =>
    array (
      'id' => 'doubting_thomas',
      'name' => 'Doubting Thomas',
      'levels' =>
      array (
        0 => 'minor',
      ),
      'summary' => 'The hero rationalizes away the supernatural.',
      'requirements' =>
      array (
      ),
      'effects' =>
      array (
      ),
      'notes' =>
      array (
        0 => 'Roleplaying Hindrance that makes the character slow to accept supernatural danger even after seeing it.',
      ),
      'source_pages' =>
      array (
        0 => 26,
      ),
    ),
    17 =>
    array (
      'id' => 'driven',
      'name' => 'Driven',
      'levels' =>
      array (
        0 => 'minor',
        1 => 'major',
      ),
      'summary' => 'The hero is consumed by a personal ambition.',
      'requirements' =>
      array (
      ),
      'effects' =>
      array (
      ),
      'notes' =>
      array (
        0 => 'Minor Drive shapes decisions but is infrequent or fairly harmless.',
        1 => 'Major Drive is overriding and regularly creates danger.',
      ),
      'source_pages' =>
      array (
        0 => 26,
      ),
    ),
    18 =>
    array (
      'id' => 'elderly',
      'name' => 'Elderly',
      'levels' =>
      array (
        0 => 'major',
      ),
      'summary' => 'Age slows the hero physically but grants extra learned skill.',
      'requirements' =>
      array (
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'major',
          'type' => 'modifier',
          'polarity' => 'penalty',
          'target' => 'derived.pace',
          'operator' => 'subtract',
          'value' => 1,
          'conditions' =>
          array (
          ),
          'details' => 'Reduce Pace by 1.',
        ),
        1 =>
        array (
          'level' => 'major',
          'type' => 'modifier',
          'polarity' => 'penalty',
          'target' => 'movement.running_total',
          'operator' => 'subtract',
          'value' => 1,
          'conditions' =>
          array (
          ),
          'details' => 'Subtract 1 from running rolls, minimum 1.',
        ),
        2 =>
        array (
          'level' => 'major',
          'type' => 'modifier',
          'polarity' => 'penalty',
          'target' => 'attribute.agility',
          'operator' => 'subtract',
          'value' => 1,
          'conditions' =>
          array (
            0 => 'rolls_only',
          ),
          'details' => 'Subtract 1 from Agility rolls, but not linked skills.',
        ),
        3 =>
        array (
          'level' => 'major',
          'type' => 'modifier',
          'polarity' => 'penalty',
          'target' => 'attribute.strength',
          'operator' => 'subtract',
          'value' => 1,
          'conditions' =>
          array (
            0 => 'rolls_only',
          ),
          'details' => 'Subtract 1 from Strength rolls and Strength-based damage, but not linked skills.',
        ),
        4 =>
        array (
          'level' => 'major',
          'type' => 'modifier',
          'polarity' => 'penalty',
          'target' => 'attribute.vigor',
          'operator' => 'subtract',
          'value' => 1,
          'conditions' =>
          array (
            0 => 'rolls_only',
          ),
          'details' => 'Subtract 1 from Vigor rolls, but not linked skills.',
        ),
        5 =>
        array (
          'level' => 'major',
          'type' => 'grant',
          'polarity' => 'benefit',
          'target' => 'character.skill_points',
          'operator' => 'grant',
          'value' => 5,
          'conditions' =>
          array (
            0 => 'smarts_linked_skills_only',
          ),
          'details' => 'Gain 5 extra skill points for Smarts-linked skills.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 26,
      ),
    ),
    19 =>
    array (
      'id' => 'enemy',
      'name' => 'Enemy',
      'levels' =>
      array (
        0 => 'minor',
        1 => 'major',
      ),
      'summary' => 'A foe wants the hero ruined, imprisoned, or dead.',
      'requirements' =>
      array (
      ),
      'effects' =>
      array (
      ),
      'notes' =>
      array (
        0 => 'Severity depends on how powerful and active the enemy is.',
        1 => 'If the enemy is defeated permanently, the GM should replace it or the Hindrance may be bought off with an Advance.',
      ),
      'source_pages' =>
      array (
        0 => 26,
      ),
    ),
    20 =>
    array (
      'id' => 'greedy',
      'name' => 'Greedy',
      'levels' =>
      array (
        0 => 'minor',
        1 => 'major',
      ),
      'summary' => 'The hero covets wealth and possessions.',
      'requirements' =>
      array (
      ),
      'effects' =>
      array (
      ),
      'notes' =>
      array (
        0 => 'Minor Greed causes constant arguments over treasure.',
        1 => 'Major Greed can lead to serious harm over wealth or unfairness.',
      ),
      'source_pages' =>
      array (
        0 => 26,
      ),
    ),
    21 =>
    array (
      'id' => 'habit',
      'name' => 'Habit',
      'levels' =>
      array (
        0 => 'minor',
        1 => 'major',
      ),
      'summary' => 'The hero has a compulsion or addiction.',
      'requirements' =>
      array (
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'major',
          'type' => 'special',
          'polarity' => 'penalty',
          'target' => 'fatigue.addiction_withdrawal',
          'operator' => 'grant',
          'value' => 1,
          'conditions' =>
          array (
            0 => 'every_24_hours_without_fix',
            1 => 'on_failed_vigor_roll',
          ),
          'details' => 'An addict who goes without the habit must make a Vigor roll every 24 hours or take a level of Fatigue.',
        ),
      ),
      'notes' =>
      array (
        0 => 'A Healing roll with proper medicine can remove one withdrawal Fatigue level for four hours, after which it returns unless the character gets the addictive substance.',
      ),
      'source_pages' =>
      array (
        0 => 26,
      ),
    ),
    22 =>
    array (
      'id' => 'hard_of_hearing',
      'name' => 'Hard of Hearing',
      'levels' =>
      array (
        0 => 'minor',
        1 => 'major',
      ),
      'summary' => 'The hero has serious hearing loss or is deaf.',
      'requirements' =>
      array (
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'minor',
          'type' => 'modifier',
          'polarity' => 'penalty',
          'target' => 'notice.hearing_based',
          'operator' => 'subtract',
          'value' => 4,
          'conditions' =>
          array (
          ),
          'details' => 'Subtract 4 from all Notice rolls made to hear, including waking from noise.',
        ),
        1 =>
        array (
          'level' => 'major',
          'type' => 'special',
          'polarity' => 'penalty',
          'target' => 'notice.hearing_based',
          'operator' => 'fail',
          'value' => true,
          'conditions' =>
          array (
          ),
          'details' => 'The character is deaf and automatically fails all hearing-based Notice rolls.',
        ),
      ),
      'notes' =>
      array (
        0 => 'Hearing aids reduce the penalty by 2 but can fall out on trauma.',
      ),
      'source_pages' =>
      array (
        0 => 26,
      ),
    ),
    23 =>
    array (
      'id' => 'heroic',
      'name' => 'Heroic',
      'levels' =>
      array (
        0 => 'major',
      ),
      'summary' => 'The hero always comes to the aid of those in need.',
      'requirements' =>
      array (
      ),
      'effects' =>
      array (
      ),
      'notes' =>
      array (
        0 => 'Roleplaying Hindrance that consistently pushes the character into dangerous rescue situations.',
      ),
      'source_pages' =>
      array (
        0 => 26,
        1 => 27,
      ),
    ),
    24 =>
    array (
      'id' => 'hesitant',
      'name' => 'Hesitant',
      'levels' =>
      array (
        0 => 'minor',
      ),
      'summary' => 'The hero falters in stressful situations.',
      'requirements' =>
      array (
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'minor',
          'type' => 'action',
          'polarity' => 'penalty',
          'target' => 'combat.action_cards_drawn',
          'operator' => 'draw',
          'value' => 2,
          'conditions' =>
          array (
            0 => 'use_lowest_card',
            1 => 'joker_ignores_penalty_for_round',
          ),
          'details' => 'Draw two Action Cards and act on the lower one.',
        ),
        1 =>
        array (
          'level' => 'minor',
          'type' => 'restriction',
          'polarity' => 'penalty',
          'target' => 'edge_access',
          'operator' => 'set',
          'value' => false,
          'conditions' =>
          array (
            0 => 'edge:Quick',
            1 => 'edge:Level Headed',
          ),
          'details' => 'Characters with Hesitant cannot take Quick or Level Headed.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 27,
      ),
    ),
    25 =>
    array (
      'id' => 'illiterate',
      'name' => 'Illiterate',
      'levels' =>
      array (
        0 => 'minor',
      ),
      'summary' => 'The hero cannot properly read or write.',
      'requirements' =>
      array (
      ),
      'effects' =>
      array (
      ),
      'notes' =>
      array (
        0 => 'Cannot read or write any language, regardless of languages spoken.',
      ),
      'source_pages' =>
      array (
        0 => 27,
      ),
    ),
    26 =>
    array (
      'id' => 'impulsive',
      'name' => 'Impulsive',
      'levels' =>
      array (
        0 => 'major',
      ),
      'summary' => 'The hero acts before thinking things through.',
      'requirements' =>
      array (
      ),
      'effects' =>
      array (
      ),
      'notes' =>
      array (
        0 => 'Roleplaying Hindrance that favors hasty action.',
      ),
      'source_pages' =>
      array (
        0 => 27,
      ),
    ),
    27 =>
    array (
      'id' => 'jealous',
      'name' => 'Jealous',
      'levels' =>
      array (
        0 => 'minor',
        1 => 'major',
      ),
      'summary' => 'The hero envies others and resents being outshined.',
      'requirements' =>
      array (
      ),
      'effects' =>
      array (
      ),
      'notes' =>
      array (
        0 => 'Minor Jealousy centers on one subject.',
        1 => 'Major Jealousy is broad and often becomes sabotage or slander.',
      ),
      'source_pages' =>
      array (
        0 => 27,
      ),
    ),
    28 =>
    array (
      'id' => 'loyal',
      'name' => 'Loyal',
      'levels' =>
      array (
        0 => 'minor',
      ),
      'summary' => 'The hero stands by friends and risks much for them.',
      'requirements' =>
      array (
      ),
      'effects' =>
      array (
      ),
      'notes' =>
      array (
        0 => 'Roleplaying Hindrance that prioritizes allies over personal safety.',
      ),
      'source_pages' =>
      array (
        0 => 27,
      ),
    ),
    29 =>
    array (
      'id' => 'mean',
      'name' => 'Mean',
      'levels' =>
      array (
        0 => 'minor',
      ),
      'summary' => 'The hero is disagreeable and struggles to be kind.',
      'requirements' =>
      array (
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'minor',
          'type' => 'modifier',
          'polarity' => 'penalty',
          'target' => 'skill.persuasion',
          'operator' => 'subtract',
          'value' => 1,
          'conditions' =>
          array (
          ),
          'details' => 'Subtract 1 from Persuasion rolls.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 27,
      ),
    ),
    30 =>
    array (
      'id' => 'mild_mannered',
      'name' => 'Mild Mannered',
      'levels' =>
      array (
        0 => 'minor',
      ),
      'summary' => 'The hero has trouble appearing threatening.',
      'requirements' =>
      array (
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'minor',
          'type' => 'modifier',
          'polarity' => 'penalty',
          'target' => 'skill.intimidation',
          'operator' => 'subtract',
          'value' => 2,
          'conditions' =>
          array (
          ),
          'details' => 'Subtract 2 from Intimidation rolls.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 27,
      ),
    ),
    31 =>
    array (
      'id' => 'mute',
      'name' => 'Mute',
      'levels' =>
      array (
        0 => 'major',
      ),
      'summary' => 'The hero cannot speak.',
      'requirements' =>
      array (
      ),
      'effects' =>
      array (
      ),
      'notes' =>
      array (
        0 => 'The character must communicate through writing, sign language, or other visual methods.',
      ),
      'source_pages' =>
      array (
        0 => 27,
      ),
    ),
    32 =>
    array (
      'id' => 'obese',
      'name' => 'Obese',
      'levels' =>
      array (
        0 => 'minor',
      ),
      'summary' => 'The hero carries excess weight in a way that hinders mobility.',
      'requirements' =>
      array (
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'minor',
          'type' => 'modifier',
          'polarity' => 'mixed',
          'target' => 'derived.size',
          'operator' => 'add',
          'value' => 1,
          'conditions' =>
          array (
          ),
          'details' => 'Increase Size by 1, which also increases Toughness by 1.',
        ),
        1 =>
        array (
          'level' => 'minor',
          'type' => 'modifier',
          'polarity' => 'penalty',
          'target' => 'derived.pace',
          'operator' => 'subtract',
          'value' => 1,
          'conditions' =>
          array (
          ),
          'details' => 'Reduce Pace by 1.',
        ),
        2 =>
        array (
          'level' => 'minor',
          'type' => 'modifier',
          'polarity' => 'penalty',
          'target' => 'movement.running_die',
          'operator' => 'subtract',
          'value' => 1,
          'conditions' =>
          array (
            0 => 'die_step',
            1 => 'minimum_d4',
          ),
          'details' => 'Reduce the running die by one die type, minimum d4.',
        ),
        3 =>
        array (
          'level' => 'minor',
          'type' => 'modifier',
          'polarity' => 'penalty',
          'target' => 'attribute.strength_for_armor_and_worn_gear',
          'operator' => 'subtract',
          'value' => 1,
          'conditions' =>
          array (
            0 => 'die_step',
            1 => 'minimum_d4',
          ),
          'details' => 'Treat Strength as one die type lower for armor and worn gear, but not weapons.',
        ),
      ),
      'notes' =>
      array (
        0 => 'Cannot be combined with Brawny.',
        1 => 'This Hindrance cannot raise Size above +3.',
      ),
      'source_pages' =>
      array (
        0 => 27,
      ),
    ),
    33 =>
    array (
      'id' => 'obligation',
      'name' => 'Obligation',
      'levels' =>
      array (
        0 => 'minor',
        1 => 'major',
      ),
      'summary' => 'The hero has a recurring responsibility that consumes time.',
      'requirements' =>
      array (
      ),
      'effects' =>
      array (
      ),
      'notes' =>
      array (
        0 => 'A Minor Obligation takes about 20 hours most weeks.',
        1 => 'A Major Obligation takes 40 or more hours most weeks.',
      ),
      'source_pages' =>
      array (
        0 => 27,
      ),
    ),
    34 =>
    array (
      'id' => 'one_arm',
      'name' => 'One Arm',
      'levels' =>
      array (
        0 => 'major',
      ),
      'summary' => 'The hero has only one usable arm.',
      'requirements' =>
      array (
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'major',
          'type' => 'modifier',
          'polarity' => 'penalty',
          'target' => 'task.requires_two_hands',
          'operator' => 'subtract',
          'value' => 4,
          'conditions' =>
          array (
          ),
          'details' => 'Subtract 4 from tasks that require two hands, such as some Athletics rolls or using a two-handed weapon.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 28,
      ),
    ),
    35 =>
    array (
      'id' => 'one_eye',
      'name' => 'One Eye',
      'levels' =>
      array (
        0 => 'major',
      ),
      'summary' => 'Depth perception is impaired by the loss of one eye.',
      'requirements' =>
      array (
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'major',
          'type' => 'modifier',
          'polarity' => 'penalty',
          'target' => 'trait.vision_dependent',
          'operator' => 'subtract',
          'value' => 2,
          'conditions' =>
          array (
            0 => 'target_more_than_5_inches_away',
          ),
          'details' => 'Subtract 2 from vision-dependent Trait rolls against targets more than 5 inches away.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 28,
      ),
    ),
    36 =>
    array (
      'id' => 'outsider',
      'name' => 'Outsider',
      'levels' =>
      array (
        0 => 'minor',
        1 => 'major',
      ),
      'summary' => 'The hero is an obvious social outsider.',
      'requirements' =>
      array (
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'minor',
          'type' => 'modifier',
          'polarity' => 'penalty',
          'target' => 'skill.persuasion',
          'operator' => 'subtract',
          'value' => 2,
          'conditions' =>
          array (
            0 => 'when_influencing_those_not_of_your_kind',
          ),
          'details' => 'Subtract 2 from Persuasion rolls against those outside the hero’s own kind.',
        ),
      ),
      'notes' =>
      array (
        0 => 'The Major version also means the hero has few or no legal rights in the main campaign area.',
      ),
      'source_pages' =>
      array (
        0 => 28,
      ),
    ),
    37 =>
    array (
      'id' => 'overconfident',
      'name' => 'Overconfident',
      'levels' =>
      array (
        0 => 'major',
      ),
      'summary' => 'The hero believes they can overcome any challenge.',
      'requirements' =>
      array (
      ),
      'effects' =>
      array (
      ),
      'notes' =>
      array (
        0 => 'Roleplaying Hindrance that discourages retreat and pushes the character into risks.',
      ),
      'source_pages' =>
      array (
        0 => 28,
      ),
    ),
    38 =>
    array (
      'id' => 'pacifist',
      'name' => 'Pacifist',
      'levels' =>
      array (
        0 => 'minor',
        1 => 'major',
      ),
      'summary' => 'The hero avoids harming others, especially living foes.',
      'requirements' =>
      array (
      ),
      'effects' =>
      array (
      ),
      'notes' =>
      array (
        0 => 'Minor Pacifists fight only when forced and forbid killing helpless victims.',
        1 => 'Major Pacifists do not fight living, sapient beings except in defense and with nonlethal methods.',
      ),
      'source_pages' =>
      array (
        0 => 28,
      ),
    ),
    39 =>
    array (
      'id' => 'phobia',
      'name' => 'Phobia',
      'levels' =>
      array (
        0 => 'minor',
        1 => 'major',
      ),
      'summary' => 'An irrational fear undermines the hero in its presence.',
      'requirements' =>
      array (
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'minor',
          'type' => 'modifier',
          'polarity' => 'penalty',
          'target' => 'trait.all',
          'operator' => 'subtract',
          'value' => 1,
          'conditions' =>
          array (
            0 => 'in_presence_of_phobia',
          ),
          'details' => 'Subtract 1 from all Trait rolls when confronted by the phobia.',
        ),
        1 =>
        array (
          'level' => 'major',
          'type' => 'modifier',
          'polarity' => 'penalty',
          'target' => 'trait.all',
          'operator' => 'subtract',
          'value' => 2,
          'conditions' =>
          array (
            0 => 'in_presence_of_phobia',
          ),
          'details' => 'Subtract 2 from all Trait rolls when confronted by the phobia.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 28,
      ),
    ),
    40 =>
    array (
      'id' => 'poverty',
      'name' => 'Poverty',
      'levels' =>
      array (
        0 => 'minor',
      ),
      'summary' => 'The hero starts poor and struggles to keep money.',
      'requirements' =>
      array (
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'minor',
          'type' => 'resource',
          'polarity' => 'penalty',
          'target' => 'resource.starting_funds',
          'operator' => 'halve',
          'value' => 0.5,
          'conditions' =>
          array (
          ),
          'details' => 'Start with half the usual setting funds.',
        ),
        1 =>
        array (
          'level' => 'minor',
          'type' => 'resource',
          'polarity' => 'penalty',
          'target' => 'resource.total_funds',
          'operator' => 'halve',
          'value' => 0.5,
          'conditions' =>
          array (
            0 => 'every_game_week',
          ),
          'details' => 'In general, total funds are halved every game week.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 28,
      ),
    ),
    41 =>
    array (
      'id' => 'quirk',
      'name' => 'Quirk',
      'levels' =>
      array (
        0 => 'minor',
      ),
      'summary' => 'The hero has a small but troublesome personal foible.',
      'requirements' =>
      array (
      ),
      'effects' =>
      array (
      ),
      'notes' =>
      array (
        0 => 'Usually roleplaying-focused, though the quirk should sometimes create complications.',
      ),
      'source_pages' =>
      array (
        0 => 28,
      ),
    ),
    42 =>
    array (
      'id' => 'ruthless',
      'name' => 'Ruthless',
      'levels' =>
      array (
        0 => 'minor',
        1 => 'major',
      ),
      'summary' => 'The hero will go very far to achieve a goal.',
      'requirements' =>
      array (
      ),
      'effects' =>
      array (
      ),
      'notes' =>
      array (
        0 => 'Minor Ruthlessness stops short of serious harm except against direct opposition.',
        1 => 'Major Ruthlessness harms anyone who stands in the way.',
      ),
      'source_pages' =>
      array (
        0 => 28,
      ),
    ),
    43 =>
    array (
      'id' => 'secret',
      'name' => 'Secret',
      'levels' =>
      array (
        0 => 'minor',
        1 => 'major',
      ),
      'summary' => 'The hero is hiding something dangerous or damaging.',
      'requirements' =>
      array (
      ),
      'effects' =>
      array (
      ),
      'notes' =>
      array (
        0 => 'If the secret becomes public, it should typically become a different Hindrance such as Enemy, Shamed, or Wanted.',
      ),
      'source_pages' =>
      array (
        0 => 28,
      ),
    ),
    44 =>
    array (
      'id' => 'shamed',
      'name' => 'Shamed',
      'levels' =>
      array (
        0 => 'minor',
        1 => 'major',
      ),
      'summary' => 'The hero bears a disgrace from the past.',
      'requirements' =>
      array (
      ),
      'effects' =>
      array (
      ),
      'notes' =>
      array (
        0 => 'Minor Shame is private and internal.',
        1 => 'Major Shame is known to others and can be used against the hero.',
      ),
      'source_pages' =>
      array (
        0 => 29,
      ),
    ),
    45 =>
    array (
      'id' => 'slow',
      'name' => 'Slow',
      'levels' =>
      array (
        0 => 'minor',
        1 => 'major',
      ),
      'summary' => 'The hero’s mobility is reduced by disability or past injury.',
      'requirements' =>
      array (
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'minor',
          'type' => 'modifier',
          'polarity' => 'penalty',
          'target' => 'derived.pace',
          'operator' => 'subtract',
          'value' => 1,
          'conditions' =>
          array (
          ),
          'details' => 'Reduce Pace by 1.',
        ),
        1 =>
        array (
          'level' => 'minor',
          'type' => 'modifier',
          'polarity' => 'penalty',
          'target' => 'movement.running_die',
          'operator' => 'subtract',
          'value' => 1,
          'conditions' =>
          array (
            0 => 'die_step',
            1 => 'if_already_d4_reduce_to_d4_minus_1',
          ),
          'details' => 'Reduce the running die one step.',
        ),
        2 =>
        array (
          'level' => 'major',
          'type' => 'modifier',
          'polarity' => 'penalty',
          'target' => 'derived.pace',
          'operator' => 'subtract',
          'value' => 2,
          'conditions' =>
          array (
          ),
          'details' => 'Reduce Pace by 2.',
        ),
        3 =>
        array (
          'level' => 'major',
          'type' => 'modifier',
          'polarity' => 'penalty',
          'target' => 'movement.running_die',
          'operator' => 'subtract',
          'value' => 1,
          'conditions' =>
          array (
            0 => 'die_step',
          ),
          'details' => 'Reduce the running die one step.',
        ),
        4 =>
        array (
          'level' => 'major',
          'type' => 'modifier',
          'polarity' => 'penalty',
          'target' => 'skill.athletics',
          'operator' => 'subtract',
          'value' => 2,
          'conditions' =>
          array (
          ),
          'details' => 'Subtract 2 from Athletics rolls.',
        ),
        5 =>
        array (
          'level' => 'major',
          'type' => 'modifier',
          'polarity' => 'penalty',
          'target' => 'trait.resist_athletics',
          'operator' => 'subtract',
          'value' => 2,
          'conditions' =>
          array (
          ),
          'details' => 'Subtract 2 from rolls to resist Athletics-based effects such as Tests or Grappling.',
        ),
        6 =>
        array (
          'level' => 'major',
          'type' => 'restriction',
          'polarity' => 'penalty',
          'target' => 'edge_access',
          'operator' => 'set',
          'value' => false,
          'conditions' =>
          array (
            0 => 'edge:Fleet-Footed',
          ),
          'details' => 'Slow characters may not take Fleet-Footed.',
        ),
      ),
      'notes' =>
      array (
        0 => 'A Minor Slow character may have a prosthesis; if it is lost, treat them as Major Slow.',
        1 => 'Wheelchair options are available in later-tech settings.',
      ),
      'source_pages' =>
      array (
        0 => 29,
      ),
    ),
    46 =>
    array (
      'id' => 'small',
      'name' => 'Small',
      'levels' =>
      array (
        0 => 'minor',
      ),
      'summary' => 'The hero is especially small or frail.',
      'requirements' =>
      array (
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'minor',
          'type' => 'modifier',
          'polarity' => 'penalty',
          'target' => 'derived.size',
          'operator' => 'subtract',
          'value' => 1,
          'conditions' =>
          array (
            0 => 'minimum_negative_1',
          ),
          'details' => 'Reduce Size by 1.',
        ),
        1 =>
        array (
          'level' => 'minor',
          'type' => 'modifier',
          'polarity' => 'penalty',
          'target' => 'derived.toughness',
          'operator' => 'subtract',
          'value' => 1,
          'conditions' =>
          array (
          ),
          'details' => 'Reduce Toughness by 1, even if Size cannot go below −1.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 29,
      ),
    ),
    47 =>
    array (
      'id' => 'stubborn',
      'name' => 'Stubborn',
      'levels' =>
      array (
        0 => 'minor',
      ),
      'summary' => 'The hero refuses to yield or admit mistakes.',
      'requirements' =>
      array (
      ),
      'effects' =>
      array (
      ),
      'notes' =>
      array (
        0 => 'Roleplaying Hindrance centered on insisting on one’s own way.',
      ),
      'source_pages' =>
      array (
        0 => 29,
        1 => 30,
      ),
    ),
    48 =>
    array (
      'id' => 'suspicious',
      'name' => 'Suspicious',
      'levels' =>
      array (
        0 => 'minor',
        1 => 'major',
      ),
      'summary' => 'The hero distrusts others.',
      'requirements' =>
      array (
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'major',
          'type' => 'modifier',
          'polarity' => 'penalty',
          'target' => 'support_rolls_targeting_character',
          'operator' => 'subtract',
          'value' => 2,
          'conditions' =>
          array (
          ),
          'details' => 'Support rolls made to aid the character are made at −2.',
        ),
      ),
      'notes' =>
      array (
        0 => 'Minor Suspicion is mostly roleplaying-driven distrust and paranoia.',
      ),
      'source_pages' =>
      array (
        0 => 30,
      ),
    ),
    49 =>
    array (
      'id' => 'thin_skinned',
      'name' => 'Thin Skinned',
      'levels' =>
      array (
        0 => 'minor',
        1 => 'major',
      ),
      'summary' => 'Personal attacks hit the hero especially hard.',
      'requirements' =>
      array (
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'minor',
          'type' => 'modifier',
          'polarity' => 'penalty',
          'target' => 'resist.taunt',
          'operator' => 'subtract',
          'value' => 2,
          'conditions' =>
          array (
          ),
          'details' => 'Subtract 2 when resisting Taunt attacks.',
        ),
        1 =>
        array (
          'level' => 'major',
          'type' => 'modifier',
          'polarity' => 'penalty',
          'target' => 'resist.taunt',
          'operator' => 'subtract',
          'value' => 4,
          'conditions' =>
          array (
          ),
          'details' => 'Subtract 4 when resisting Taunt attacks.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 30,
      ),
    ),
    50 =>
    array (
      'id' => 'timid',
      'name' => 'Timid',
      'levels' =>
      array (
        0 => 'major',
      ),
      'summary' => 'The hero is squeamish and easily cowed.',
      'requirements' =>
      array (
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'major',
          'type' => 'modifier',
          'polarity' => 'penalty',
          'target' => 'resist.fear',
          'operator' => 'subtract',
          'value' => 2,
          'conditions' =>
          array (
          ),
          'details' => 'Subtract 2 from Fear checks.',
        ),
        1 =>
        array (
          'level' => 'major',
          'type' => 'modifier',
          'polarity' => 'penalty',
          'target' => 'resist.intimidation',
          'operator' => 'subtract',
          'value' => 2,
          'conditions' =>
          array (
          ),
          'details' => 'Subtract 2 when resisting Intimidation.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 30,
      ),
    ),
    51 =>
    array (
      'id' => 'tongue_tied',
      'name' => 'Tongue-Tied',
      'levels' =>
      array (
        0 => 'major',
      ),
      'summary' => 'The hero routinely says the wrong thing.',
      'requirements' =>
      array (
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'major',
          'type' => 'modifier',
          'polarity' => 'penalty',
          'target' => 'skill.intimidation',
          'operator' => 'subtract',
          'value' => 1,
          'conditions' =>
          array (
            0 => 'speech_based',
          ),
          'details' => 'Subtract 1 from speech-based Intimidation rolls.',
        ),
        1 =>
        array (
          'level' => 'major',
          'type' => 'modifier',
          'polarity' => 'penalty',
          'target' => 'skill.performance',
          'operator' => 'subtract',
          'value' => 1,
          'conditions' =>
          array (
            0 => 'speech_based',
          ),
          'details' => 'Subtract 1 from speech-based Performance rolls.',
        ),
        2 =>
        array (
          'level' => 'major',
          'type' => 'modifier',
          'polarity' => 'penalty',
          'target' => 'skill.persuasion',
          'operator' => 'subtract',
          'value' => 1,
          'conditions' =>
          array (
            0 => 'speech_based',
          ),
          'details' => 'Subtract 1 from speech-based Persuasion rolls.',
        ),
        3 =>
        array (
          'level' => 'major',
          'type' => 'modifier',
          'polarity' => 'penalty',
          'target' => 'skill.taunt',
          'operator' => 'subtract',
          'value' => 1,
          'conditions' =>
          array (
            0 => 'speech_based',
          ),
          'details' => 'Subtract 1 from speech-based Taunt rolls.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 30,
      ),
    ),
    52 =>
    array (
      'id' => 'ugly',
      'name' => 'Ugly',
      'levels' =>
      array (
        0 => 'minor',
        1 => 'major',
      ),
      'summary' => 'The hero’s appearance makes social influence harder.',
      'requirements' =>
      array (
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'minor',
          'type' => 'modifier',
          'polarity' => 'penalty',
          'target' => 'skill.persuasion',
          'operator' => 'subtract',
          'value' => 1,
          'conditions' =>
          array (
          ),
          'details' => 'Subtract 1 from Persuasion rolls.',
        ),
        1 =>
        array (
          'level' => 'major',
          'type' => 'modifier',
          'polarity' => 'penalty',
          'target' => 'skill.persuasion',
          'operator' => 'subtract',
          'value' => 2,
          'conditions' =>
          array (
          ),
          'details' => 'Subtract 2 from Persuasion rolls.',
        ),
      ),
      'notes' =>
      array (
      ),
      'source_pages' =>
      array (
        0 => 30,
      ),
    ),
    53 =>
    array (
      'id' => 'vengeful',
      'name' => 'Vengeful',
      'levels' =>
      array (
        0 => 'minor',
        1 => 'major',
      ),
      'summary' => 'The hero always seeks payback.',
      'requirements' =>
      array (
      ),
      'effects' =>
      array (
      ),
      'notes' =>
      array (
        0 => 'Minor Vengefulness usually seeks lawful or limited revenge.',
        1 => 'Major Vengefulness escalates until the character feels fully satisfied.',
      ),
      'source_pages' =>
      array (
        0 => 30,
      ),
    ),
    54 =>
    array (
      'id' => 'vow',
      'name' => 'Vow',
      'levels' =>
      array (
        0 => 'minor',
        1 => 'major',
      ),
      'summary' => 'The hero has sworn an oath that makes demands on time and risk.',
      'requirements' =>
      array (
      ),
      'effects' =>
      array (
      ),
      'notes' =>
      array (
        0 => 'Severity depends on how dangerous and demanding the oath is.',
      ),
      'source_pages' =>
      array (
        0 => 30,
      ),
    ),
    55 =>
    array (
      'id' => 'wanted',
      'name' => 'Wanted',
      'levels' =>
      array (
        0 => 'minor',
        1 => 'major',
      ),
      'summary' => 'Authorities are looking for the hero over a crime.',
      'requirements' =>
      array (
      ),
      'effects' =>
      array (
      ),
      'notes' =>
      array (
        0 => 'Severity depends on the seriousness of the crime and how active the pursuit is.',
      ),
      'source_pages' =>
      array (
        0 => 30,
      ),
    ),
    56 =>
    array (
      'id' => 'young',
      'name' => 'Young',
      'levels' =>
      array (
        0 => 'minor',
        1 => 'major',
      ),
      'summary' => 'The hero is notably young, with fewer starting points but more luck.',
      'requirements' =>
      array (
      ),
      'effects' =>
      array (
        0 =>
        array (
          'level' => 'minor',
          'type' => 'resource',
          'polarity' => 'penalty',
          'target' => 'character.attribute_points',
          'operator' => 'set',
          'value' => 4,
          'conditions' =>
          array (
            0 => 'during_character_creation',
          ),
          'details' => 'A Young hero gets 4 attribute points instead of 5.',
        ),
        1 =>
        array (
          'level' => 'minor',
          'type' => 'resource',
          'polarity' => 'penalty',
          'target' => 'character.skill_points',
          'operator' => 'set',
          'value' => 10,
          'conditions' =>
          array (
            0 => 'during_character_creation',
          ),
          'details' => 'A Young hero gets 10 skill points instead of 12.',
        ),
        2 =>
        array (
          'level' => 'minor',
          'type' => 'resource',
          'polarity' => 'benefit',
          'target' => 'resource.bennies.start_session',
          'operator' => 'add',
          'value' => 1,
          'conditions' =>
          array (
          ),
          'details' => 'A Young hero draws one extra Benny at the start of each session.',
        ),
        3 =>
        array (
          'level' => 'major',
          'type' => 'resource',
          'polarity' => 'penalty',
          'target' => 'character.attribute_points',
          'operator' => 'set',
          'value' => 3,
          'conditions' =>
          array (
            0 => 'during_character_creation',
          ),
          'details' => 'A Very Young hero gets 3 attribute points instead of 5.',
        ),
        4 =>
        array (
          'level' => 'major',
          'type' => 'resource',
          'polarity' => 'penalty',
          'target' => 'character.skill_points',
          'operator' => 'set',
          'value' => 10,
          'conditions' =>
          array (
            0 => 'during_character_creation',
          ),
          'details' => 'A Very Young hero gets 10 skill points.',
        ),
        5 =>
        array (
          'level' => 'major',
          'type' => 'grant',
          'polarity' => 'penalty',
          'target' => 'hindrance.small',
          'operator' => 'grant',
          'value' => true,
          'conditions' =>
          array (
          ),
          'details' => 'A Very Young hero also has the Small Hindrance.',
        ),
        6 =>
        array (
          'level' => 'major',
          'type' => 'resource',
          'polarity' => 'benefit',
          'target' => 'resource.bennies.start_session',
          'operator' => 'add',
          'value' => 2,
          'conditions' =>
          array (
          ),
          'details' => 'A Very Young hero draws two extra Bennies at the start of each session.',
        ),
      ),
      'notes' =>
      array (
        0 => 'Young heroes may also face setting-specific legal restrictions such as driving or weapon ownership.',
        1 => 'Most Minor Young characters should also take Small, but it is not mandatory.',
      ),
      'source_pages' =>
      array (
        0 => 30,
      ),
    ),
  ),
);
