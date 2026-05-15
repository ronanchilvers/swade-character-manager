<?php

declare(strict_types=1);

namespace App\Character;

use App\Entity;
use App\Entity\Factory\Character as CharacterFactory;
use App\Service\Data\Edges as EdgesData;
use App\Service\Data\Hindrances as HindrancesData;
use App\Service\Data\Manager;
use App\Service\Data\Skills as SkillsData;

class Sheet
{
    public const ATTRIBUTE_ORDER = ['agility', 'smarts', 'spirit', 'strength', 'vigor'];
    public const ATTRIBUTE_DESCRIPTIONS = [
        'agility' => 'Nimbleness, dexterity, coordination',
        'smarts' => 'Intelligence, mental acuity, fast thinking',
        'spirit' => 'Self-confidence, backbone, willpower',
        'strength' => 'Physical prowess, fitness',
        'vigor' => 'Physical endurance, resistance',
    ];
    public const DIE_FACES = [4, 6, 8, 10, 12];

    public function build(
        Entity $character,
        array $hindrances,
        array $skills,
        array $edges,
        Manager $manager,
        CharacterFactory $characterFactory,
        array $gear = [],
        array $weapons = [],
    ): array {
        return [
            'identity' => $this->buildIdentity($character),
            'attributes' => $this->buildAttributes($character, $characterFactory),
            'hindrances' => $this->buildHindrances($hindrances, $manager),
            'skills' => $this->buildSkills($skills, $manager),
            'edges' => $this->buildEdges($edges, $manager),
            'state' => $this->buildState($character),
            'gear' => $this->buildGear($gear),
            'weapons' => $this->buildWeapons($weapons),
        ];
    }

    private function buildState(Entity $character): array
    {
        return [
            'wounds'        => max(0, (int) ($character->wounds ?? 0)),
            'fatigue'       => max(0, (int) ($character->fatigue ?? 0)),
            'incapacitated' => ((int) ($character->incapacitated ?? 0)) > 0,
            'bennies'       => max(0, (int) ($character->bennies ?? 0)),
            'notes'         => (string) ($character->notes ?? ''),
        ];
    }

    private function buildGear(array $gear): array
    {
        $rows = [];
        foreach ($gear as $item) {
            $rows[] = [
                'name'  => (string) ($item->name ?? ''),
                'notes' => (string) ($item->notes ?? ''),
            ];
        }

        return $rows;
    }

    private function buildWeapons(array $weapons): array
    {
        $rows = [];
        foreach ($weapons as $weapon) {
            $rows[] = [
                'name'   => (string) ($weapon->name ?? ''),
                'range'  => (string) ($weapon->range ?? ''),
                'damage' => (string) ($weapon->damage ?? ''),
                'ap'     => (string) ($weapon->ap ?? ''),
                'rof'    => (string) ($weapon->rof ?? ''),
                'weight' => (string) ($weapon->weight ?? ''),
                'notes'  => (string) ($weapon->notes ?? ''),
            ];
        }

        return $rows;
    }

    private function buildIdentity(Entity $character): array
    {
        return [
            'name' => (string) ($character->name ?? ''),
            'concept' => (string) ($character->concept ?? ''),
            'rank' => (string) ($character->rank ?? ''),
            'pace' => $this->intOrNull($character->pace),
            'parry' => $this->intOrNull($character->parry),
            'toughness' => $this->intOrNull($character->toughness),
        ];
    }

    private function buildAttributes(
        Entity $character,
        CharacterFactory $characterFactory,
    ): array {
        $fields = $characterFactory->attributeFields();
        $rows = [];
        foreach (self::ATTRIBUTE_ORDER as $key) {
            $label = $fields[$key]['name'] ?? ucfirst($key);
            $die = $this->intOrNull($character->get($key));
            $rows[] = [
                'key' => $key,
                'label' => $label,
                'die' => $die,
                'max' => $die,
                'die_faces' => self::DIE_FACES,
                'description' => self::ATTRIBUTE_DESCRIPTIONS[$key],
            ];
        }

        return $rows;
    }

    private function buildHindrances(array $hindrances, Manager $manager): array
    {
        $catalog = $manager->getType(HindrancesData::class);
        $rows = [];
        foreach ($hindrances as $hindrance) {
            $key = (string) $hindrance->key;
            $entry = $catalog->forId($key);
            $rows[] = [
                'key' => $key,
                'name' => $entry['name'] ?? $this->humanise($key),
                'summary' => $entry['summary'] ?? '',
                'effects_by_level' => $entry['effects_by_level'],
                'level' => (string) ($hindrance->level ?? ''),
            ];
        }

        return $rows;
    }

    private function buildSkills(array $skills, Manager $manager): array
    {
        $catalog = $manager->getType(SkillsData::class);
        $rows = [];
        foreach ($skills as $skill) {
            $key = (string) $skill->key;
            $entry = $catalog->forId($key);
            $die = $this->intOrNull($skill->die);
            $isCore = $this->parseCore($skill->core ?? null, $entry);
            $rows[] = [
                'key' => $key,
                'name' => $entry['name'] ?? $this->humanise($key),
                'summary' => $entry['summary'] ?? '',
                'linked_attribute' => strtolower((string) ($skill->attribute ?? $entry['linked_attribute'] ?? '')),
                'is_core' => $isCore,
                'die' => $die,
                'die_faces' => self::DIE_FACES,
            ];
        }

        usort($rows, function (array $a, array $b): int {
            if ($a['is_core'] !== $b['is_core']) {
                return $a['is_core'] ? -1 : 1;
            }

            return strcasecmp($a['name'], $b['name']);
        });

        return $rows;
    }

    private function buildEdges(array $edges, Manager $manager): array
    {
        $catalog = $manager->getType(EdgesData::class);
        $rows = [];
        foreach ($edges as $edge) {
            $key = (string) $edge->key;
            $entry = $catalog->forId($key);
            $count = max(1, (int) ($edge->count ?? 1));
            $rows[] = [
                'key' => $key,
                'name' => $entry['name'] ?? $this->humanise($key),
                'summary' => $entry['summary'] ?? '',
                'effects' => $entry['effects'] ?? '',
                'category' => (string) ($entry['category'] ?? ''),
                'count' => $count,
            ];
        }

        return $rows;
    }

    private function parseCore(mixed $stored, ?array $entry): bool
    {
        if (is_string($stored)) {
            return 'yes' === strtolower($stored);
        }
        if (is_bool($stored)) {
            return $stored;
        }
        if (is_array($entry) && array_key_exists('core_skill', $entry)) {
            return (bool) $entry['core_skill'];
        }

        return false;
    }

    private function intOrNull(mixed $value): ?int
    {
        if (is_null($value) || '' === $value) {
            return null;
        }

        return (int) $value;
    }

    private function humanise(string $key): string
    {
        return ucwords(str_replace(['_', '-'], ' ', $key));
    }
}
