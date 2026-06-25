<?php

declare(strict_types=1);

namespace App\Service\Archetype;

use App\Entity;
use App\Entity\Factory\Character as CharacterFactory;
use App\Entity\Factory\Edge as EdgeFactory;
use App\Entity\Factory\Hindrance as HindranceFactory;
use App\Entity\Factory\Skill as SkillFactory;
use App\Filter;
use App\Service\Data\Manager;
use App\Service\Data\Skills as SkillsData;
use RuntimeException;

class Applier
{
    public function __construct(
        private CharacterFactory $characterFactory,
        private SkillFactory $skillFactory,
        private HindranceFactory $hindranceFactory,
        private EdgeFactory $edgeFactory,
        private Manager $manager,
    ) {
    }

    public function applyToNewCharacter(array $archetype): Entity
    {
        $entity = new Entity();
        $names = $archetype['names'] ?? ['Hero'];
        $entity->name = Filter::noTags($names[array_rand($names)]);
        $attributes = $archetype['attributes'] ?? [];
        foreach (['agility', 'smarts', 'spirit', 'strength', 'vigor'] as $attr) {
            $entity->$attr = (int) ($attributes[$attr] ?? 4);
        }
        $entity->sources = 'core';
        $entity->sharing = 0;

        $result = $this->characterFactory->upsert($entity);
        if (!$result->isSuccess()) {
            throw new RuntimeException(implode('; ', $result->errors()));
        }

        $this->applySkills($entity, $archetype['skills'] ?? []);
        $this->applyHindrances($entity, $archetype['hindrances'] ?? []);
        $this->applyEdges($entity, $archetype['edges'] ?? []);

        return $entity;
    }

    private function applySkills(Entity $entity, array $skills): void
    {
        /** @var SkillsData $skillsData */
        $skillsData = $this->manager->getType(SkillsData::class);

        foreach ($skills as $skill) {
            $key = (string) $skill['key'];
            $die = (int) $skill['die'];

            $existing = $this->skillFactory->forCharacterAndKey($entity, $key);
            if ($existing instanceof Entity) {
                $existing->die = $die;
                $this->skillFactory->update($existing);
                continue;
            }

            $catalogEntry = $skillsData->forId($key);
            if (!is_array($catalogEntry)) {
                continue;
            }

            $skillEntity = new Entity([
                'character_id' => $entity->id,
                'key'          => $key,
                'die'          => $die,
                'attribute'    => strtolower($catalogEntry['linked_attribute']),
                'core'         => 'no',
            ]);
            $this->skillFactory->insert($skillEntity);
        }
    }

    private function applyHindrances(Entity $entity, array $hindrances): void
    {
        if (empty($hindrances)) {
            return;
        }

        $selected = [];
        foreach ($hindrances as $h) {
            $selected[(string) $h['key']] = (string) $h['level'];
        }

        $this->hindranceFactory->syncForCharacter($entity, $selected);
    }

    private function applyEdges(Entity $entity, array $edges): void
    {
        if (empty($edges)) {
            return;
        }

        $selected = [];
        foreach ($edges as $e) {
            $selected[(string) $e['key']] = 1;
        }

        $this->edgeFactory->syncForCharacter($entity, $selected);
    }
}
