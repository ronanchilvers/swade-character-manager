# Character Attributes Screen Plan

## Summary

This plan adds the next character editing screen after hindrances.

The new flow will be:

`concept -> hindrances -> attributes`

The attributes screen will edit only the five core attributes already stored on `characters`:

- `agility`
- `smarts`
- `spirit`
- `strength`
- `vigor`

The screen must enforce attribute-point spending during character creation:

- every attribute starts at `d4`
- the hero gets `5` attribute points
- each die step above `d4` costs `1` attribute point
- each extra attribute step beyond those first 5 costs `2` hindrance points

No schema change is required because the five attributes already exist on [`schema/001_schema.sql`](/Users/ronan/Personal/experiments/swade-character-manager/schema/001_schema.sql).

## Chosen Approach

The earlier lightweight controller-only approach is no longer sufficient. Attribute allocation now includes real business rules, so the implementation should introduce a dedicated `CharacterAttributes` service and keep the controller thin.

This is the recommended approach because:

- the new logic depends on both the character row and the currently selected hindrances
- the screen needs an allocation summary as well as field validation
- the overspend rule is a form-level validation concern, not a simple field-level factory rule
- the service gives the repo a clean place to test the point-spending rules without adding a controller harness

### Alternative considered but not chosen

Keeping all logic inside `Character::attributes()` would work technically, but it would mix:

- request handling
- die-value normalization
- attribute-point math
- hindrance-point math
- persistence
- summary building for the view

That would make both the controller and the tests harder to maintain.

## Step 1: Normalize data terminology in the JSON files

Update the catalog data files first so the rest of the code changes can consistently use `attributes` for the five core values instead of the broader and more ambiguous `traits` wording.

The files to review are:

- [`data/hindrances.json`](/Users/ronan/Personal/experiments/swade-character-manager/data/hindrances.json)
- [`data/skills.json`](/Users/ronan/Personal/experiments/swade-character-manager/data/skills.json)
- [`data/edges.json`](/Users/ronan/Personal/experiments/swade-character-manager/data/edges.json)

### Scope of the terminology update

Change `trait` or `traits` only where the data is clearly referring to one of the five core attributes or to the shared concept that should now be called attributes in this codebase.

Do not blindly replace every `trait` string. Some entries still refer to a broader SWADE rules concept and should stay as-is.

### Example changes

If a record currently refers to a core attribute explicitly:

```json
{
  "type": "trait",
  "target": "attribute.strength"
}
```

it should be treated as a candidate to become:

```json
{
  "type": "attribute",
  "target": "attribute.strength"
}
```

If a human-readable rule text currently says:

```json
{
  "details": "Raise one trait of your choice by a die type."
}
```

it should be treated as a candidate to become:

```json
{
  "details": "Raise one attribute of your choice by a die type."
}
```

### Important caution

Entries such as `chosen_trait`, `trait_maxed`, or generic rules text in edges may need case-by-case review rather than blanket replacement. Only change wording where “attribute” is semantically correct.

## Step 2: Add a dedicated attributes service

Create `src/Service/CharacterAttributes.php` and register it in [`config/services.php`](/Users/ronan/Personal/experiments/swade-character-manager/config/services.php).

The service should own:

- attribute normalization
- die-value validation orchestration
- attribute-point and hindrance-point allocation math
- persistence on success
- allocation summary data for the view

### Service dependencies

Inject:

- [`App\Entity\Factory\Character`](/Users/ronan/Personal/experiments/swade-character-manager/src/Entity/Factory/Character.php)
- [`App\Service\CharacterHindrances`](/Users/ronan/Personal/experiments/swade-character-manager/src/Service/CharacterHindrances.php)

### Planned public API

```php
public function viewData(Entity $character): array
public function processSubmission(Entity $character, array $submitted): array
```

### Planned constants and formulas

Use explicit service-level constants:

```php
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
```

The allocation math should be:

```php
$stepsAboveDefault = array_sum(
    array_map(
        fn (string $field): int => (int) (($character->{$field} - self::DEFAULT_ATTRIBUTE_DIE) / 2),
        array_keys(self::ATTRIBUTE_FIELDS)
    )
);

$attributePointsSpent = min($stepsAboveDefault, self::BASE_ATTRIBUTE_POINTS);
$attributePointsRemaining = max(0, self::BASE_ATTRIBUTE_POINTS - $attributePointsSpent);
$extraAttributeSteps = max(0, $stepsAboveDefault - self::BASE_ATTRIBUTE_POINTS);
$hindrancePointsSpent = $extraAttributeSteps * self::HINDRANCE_POINTS_PER_ATTRIBUTE_STEP;
```

### Planned result shape

Both `viewData()` and `processSubmission()` should return an `allocation` block like:

```php
[
    'attribute_points_total' => 5,
    'attribute_points_spent' => 5,
    'attribute_points_remaining' => 0,
    'hindrance_points_available' => 4,
    'hindrance_points_spent' => 2,
    'hindrance_points_remaining' => 2,
]
```

### Validation rule handled by the service

If `hindrance_points_spent > hindrance_points_available`, the submission should fail with a form-level error such as:

```php
sprintf(
    'These attributes require %d hindrance points, but only %d are available from selected hindrances.',
    $hindrancePointsSpent,
    $hindrancePointsAvailable
);
```

### Important design choice

Do not persist “hindrance points spent on attributes” as a new field. Derive it from the current attribute values:

- the first 5 steps always consume base attribute points
- only steps beyond those 5 consume hindrance points

## Step 3: Expose selected hindrance points cleanly

Extend [`src/Service/CharacterHindrances.php`](/Users/ronan/Personal/experiments/swade-character-manager/src/Service/CharacterHindrances.php) with a public helper for the selected hindrance-point total.

### Planned API

```php
public function selectedPointsForCharacter(int $characterId): int
{
    return $this->pointsUsed($this->selectedForCharacter($characterId));
}
```

This keeps the hindrance-point calculation in one place and lets `CharacterAttributes` reuse it without duplicating the level-to-points mapping.

## Step 4: Add the route and update the flow

Update [`config/routes.php`](/Users/ronan/Personal/experiments/swade-character-manager/config/routes.php) to register the new attributes step after hindrances.

### Planned code

```php
Flight::group('/characters', function () {
    Flight::route('GET|POST /create', [Character::class, 'create'])
        ->setAlias('characters_create');
    Flight::route('GET|POST /concept/@hash:[a-z0-9]{32}', [Character::class, 'concept'])
        ->setAlias('characters_concept');
    Flight::route('GET|POST /hindrances/@hash:[a-z0-9]{32}', [Character::class, 'hindrances'])
        ->setAlias('characters_hindrances');
    Flight::route('GET|POST /attributes/@hash:[a-z0-9]{32}', [Character::class, 'attributes'])
        ->setAlias('characters_attributes');
}, [ MiddlewareAuth::class ]);
```

Update the successful hindrances redirect in [`src/Controller/Character.php`](/Users/ronan/Personal/experiments/swade-character-manager/src/Controller/Character.php):

```php
Flight::redirect(Flight::getUrl('characters_attributes', ['hash' => $entity->hash]));
```

## Step 5: Add the controller action

Add `attributes(string $hash): void` to [`src/Controller/Character.php`](/Users/ronan/Personal/experiments/swade-character-manager/src/Controller/Character.php).

The controller should:

- load the character by hash
- redirect home with a flash error if the hash is invalid
- on GET, call `CharacterAttributes::viewData()`
- on POST, call `CharacterAttributes::processSubmission()`
- redirect back to the same attributes page on success
- rerender with field errors, form errors, and allocation summary on failure

### Planned constructor change

Inject the new service:

```php
public function __construct(
    private FactoryCharacter $factory,
    private CharacterHindrances $characterHindrances,
    private CharacterAttributes $characterAttributes,
    private GameData $gameData,
) {
}
```

### Planned action shape

```php
public function attributes(string $hash): void
{
    $entity = $this->factory->forHash($hash);
    if (!$entity instanceof Entity) {
        Flight::session()->flash('Unable to find character', 'error');
        Flight::redirect(Flight::getUrl('home_page'));
        return;
    }

    if ('POST' === Flight::request()->getMethod()) {
        $result = $this->characterAttributes->processSubmission($entity, $_POST);

        if (empty($result['errors']) && empty($result['form_errors'])) {
            Flight::redirect(Flight::getUrl('characters_attributes', ['hash' => $entity->hash]));
            return;
        }
    } else {
        $result = $this->characterAttributes->viewData($entity);
    }

    Flight::render('character/attributes.twig', [
        'page_title' => 'Choose Attributes',
        'entity' => $result['entity'],
        'errors' => $result['errors'],
        'form_errors' => $result['form_errors'],
        'attribute_fields' => $result['attribute_fields'],
        'attribute_options' => $result['attribute_options'],
        'allocation' => $result['allocation'],
    ]);
}
```

## Step 6: Keep die-value validation in the character factory

Update [`src/Entity/Factory/Character.php`](/Users/ronan/Personal/experiments/swade-character-manager/src/Entity/Factory/Character.php) so the five core attributes only accept valid SWADE die values.

### Planned validation rule

```php
public function getValidationRules(): array
{
    return [
        'hash' => v::not(v::blank()),
        'user' => v::intVal()->greaterThan(0),
        'name' => v::not(v::blank()),
        'agility' => v::intVal()->in([4, 6, 8, 10, 12]),
        'smarts' => v::intVal()->in([4, 6, 8, 10, 12]),
        'spirit' => v::intVal()->in([4, 6, 8, 10, 12]),
        'strength' => v::intVal()->in([4, 6, 8, 10, 12]),
        'vigor' => v::intVal()->in([4, 6, 8, 10, 12]),
    ];
}
```

Field validity stays here. Point allocation stays in `CharacterAttributes`.

## Step 7: Add the attributes view

Create [`views/character/attributes.twig`](/Users/ronan/Personal/experiments/swade-character-manager/views/character/attributes.twig).

The view should:

- follow the same layout style as `concept.twig` and `hindrances.twig`
- show field-level errors for invalid die values
- show form-level errors for point overspend or persistence failure
- render one `<select>` per attribute
- display the allocation summary above or below the fields
- provide a `Previous` button back to hindrances
- provide a final save button

### Planned summary block

Show all of the following:

- attribute points spent out of 5
- attribute points remaining
- hindrance points available from selected hindrances
- hindrance points spent on attributes
- hindrance points remaining

### Planned Twig structure

```twig
{% extends 'base.twig' %}

{% block content %}
<form class="form" method="post" action="{{ get_url('characters_attributes', {hash: entity.hash}) }}">

    {% if form_errors %}
    <div class="field field--error">
        {% for error in form_errors %}
        <p class="field__error">{{ error }}</p>
        {% endfor %}
    </div>
    {% endif %}

    <div class="field">
        <label class="field__label">Attribute Point Summary</label>
        <div class="field__inner">
            <p>Attribute points: {{ allocation.attribute_points_spent }} / {{ allocation.attribute_points_total }}</p>
            <p>Attribute points remaining: {{ allocation.attribute_points_remaining }}</p>
            <p>Hindrance points available: {{ allocation.hindrance_points_available }}</p>
            <p>Hindrance points spent on attributes: {{ allocation.hindrance_points_spent }}</p>
            <p>Hindrance points remaining: {{ allocation.hindrance_points_remaining }}</p>
        </div>
    </div>

    {% for field, label in attribute_fields %}
    <div class="field {% if field_has_error(field, errors) %}field--error{% endif %}">
        <label class="field__label" for="attribute_{{ field }}">{{ label }}</label>
        <div class="field__inner">
            <select class="field__input" id="attribute_{{ field }}" name="{{ field }}">
                {% for value in attribute_options %}
                <option value="{{ value }}" {% if attribute(entity, field) == value %}selected{% endif %}>d{{ value }}</option>
                {% endfor %}
            </select>
        </div>
        <p class="field__error">Choose a valid die type.</p>
    </div>
    {% endfor %}

    <div class="form__buttons">
        <a class="button" href="{{ get_url('characters_hindrances', {hash: entity.hash}) }}">Previous</a>
        <button class="button button--primary" type="submit">Save Attributes</button>
    </div>

</form>
{% endblock %}
```

### Important copy

Add brief guidance text near the summary so the rule is explicit:

- “Each attribute starts at d4.”
- “The first 5 die steps cost attribute points.”
- “Each extra attribute step costs 2 hindrance points.”

## Step 8: Add automated tests

Add PHPUnit coverage centered on the new service and the new helper.

### New test files

- `tests/Service/CharacterAttributesTest.php`
- extend `tests/Service/CharacterHindrancesTest.php`
- keep or add `tests/Entity/Factory/CharacterTest.php` for die-value validation

### Planned `CharacterAttributes` service test cases

1. `testDefaultsUseZeroAttributeAndHindrancePoints`
2. `testFiveBaseAttributePointsAllowFiveTotalSteps`
3. `testSixthStepRequiresTwoHindrancePoints`
4. `testSeventhStepRequiresFourHindrancePoints`
5. `testOddHindrancePointTotalsLeaveOnePointUnusedForAttributes`
6. `testOverspendingHindrancePointsReturnsFormError`
7. `testValidSubmissionPersistsCharacterAndReturnsAllocationSummary`

### Example overspend scenario

If a player selects only 2 hindrance points, this should fail:

```php
[
    'agility' => 8,
    'smarts' => 8,
    'spirit' => 8,
    'strength' => 8,
    'vigor' => 8,
]
```

That build takes 10 total steps above `d4`:

- first 5 use the base attribute pool
- extra 5 would require 10 hindrance points

The service should reject that build with a form-level allocation error.

### Planned `CharacterHindrances` test addition

Add a focused case proving `selectedPointsForCharacter()` returns the same point total implied by selected minor and major hindrances.

## Step 9: Manual verification

After implementation, manually verify the following flow in the browser:

1. Create a new character and confirm concept still redirects to hindrances.
2. Save hindrances and confirm the redirect goes to `/characters/attributes/{hash}`.
3. Load the attributes page for a new character and confirm all five selects default to `d4`.
4. Confirm the summary starts at:
   - 0 attribute points spent
   - 5 attribute points remaining
   - hindrance points available based on the selected hindrances
5. Spend exactly 5 total attribute steps and confirm no hindrance points are consumed.
6. Spend a 6th attribute step and confirm the screen requires 2 hindrance points.
7. Use a character with 3 hindrance points selected and confirm only 1 extra attribute step is affordable, with 1 hindrance point remaining.
8. Submit an overspent build and confirm:
   - no redirect occurs
   - the form-level allocation error is shown
   - the submitted values remain visible
9. Visit the attributes route with an invalid hash and confirm it flashes `Unable to find character` and redirects home.

## Step 10: Update repository guidance

Update [`AGENTS.md`](/Users/ronan/Personal/experiments/swade-character-manager/AGENTS.md) so the PHPUnit guidance reflects the current config filename.

### Current wording

```md
Run tests with `vendor/bin/phpunit`, which uses `phpunit.xml` and the `tests/` directory.
```

### Planned replacement

```md
Run tests with `vendor/bin/phpunit --configuration phpunit.xml.dist`, which loads the `tests/` directory via `phpunit.xml.dist`.
```

## Acceptance criteria

The implementation is complete when all of the following are true:

- `data/hindrances.json`, `data/skills.json`, and `data/edges.json` use `attribute` terminology where it is semantically correct
- the app has a new `characters_attributes` route
- hindrances redirects to attributes after a successful save
- the attributes screen shows both editable values and an allocation summary
- the first 5 attribute steps are validated against the base attribute-point pool
- extra attribute steps are validated at 2 hindrance points per step
- odd hindrance point totals leave the correct remainder on the summary
- invalid die values produce field-level errors
- overspending produces a form-level allocation error and does not persist
- PHPUnit includes service coverage for the allocation rules
- `AGENTS.md` references `phpunit.xml.dist` instead of `phpunit.xml`

## Assumptions

- “attributes” means only the five stored core attributes, not derived statistics such as Pace, Parry, or Toughness
- base attribute points are always consumed before hindrance points
- hindrance points spent on attributes are derived from the final attribute values rather than stored separately
- unspent hindrance points remain available for later steps
- no schema migration is needed
- no new packages should be installed
