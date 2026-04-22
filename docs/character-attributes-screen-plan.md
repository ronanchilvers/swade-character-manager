# Character Attributes Screen Plan

Status: historical planning document, updated on 2026-04-16 to reflect repository reality.

## What Happened
The attributes screen described in the original plan is now present in the application:

- Route: `characters_attributes`
- Controller: `App\Controller\Character\Attributes`
- View: `views/character/attributes.twig`

## What Did Not Happen
The service-oriented implementation proposed in the original plan was not carried through in the current tree.

These planned classes are not present:

- `App\Service\CharacterAttributes`
- `App\Service\CharacterHindrances`

The live implementation keeps attribute handling in the controller plus `App\Entity\Factory\Character`. The stale service-layer tests that previously targeted this unimplemented design have been removed in favor of tests for the live code that still ships.

## Current Behavior
- Attribute options are driven by `App\Dice::validSizes()`.
- Submitted values are sanitized in `App\Filter::numberArray()`.
- The controller writes the selected die values directly onto the `characters` entity and persists them through `App\Entity\Factory\Character::update()`.
- `Character::beforeUpdate()` recalculates `pace` and `toughness`.
- The attributes screen redirects to the skills step after a successful save.

## If Work Resumes Here
Use this file as background only. Before implementing anything else around attributes:

1. Decide whether the repo should introduce the missing service layer or keep the current controller/factory approach.
2. Add or adjust tests only for code that actually exists in `src/`.
3. Update `AGENTS.md` and `docs/codebase-review.md` if the architecture changes.
