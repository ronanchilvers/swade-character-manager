<?php

declare(strict_types=1);

namespace Tests\Service\Archetype;

use App\Entity;
use App\Entity\Factory\Character as CharacterFactory;
use App\Entity\Factory\Edge as EdgeFactory;
use App\Entity\Factory\Hindrance as HindranceFactory;
use App\Entity\Factory\Result;
use App\Entity\Factory\Skill as SkillFactory;
use App\Service\Archetype\Applier;
use App\Service\Data\Manager;
use App\Service\Data\Skills as SkillsData;
use PHPUnit\Framework\TestCase;

class ApplierTest extends TestCase
{
    private array $barbarian = [
        'name'       => 'Barbarian',
        'attributes' => ['agility' => 6, 'smarts' => 4, 'spirit' => 6, 'strength' => 8, 'vigor' => 8],
        'skills'     => [
            ['key' => 'fighting', 'die' => 8],
            ['key' => 'survival', 'die' => 6],
        ],
        'hindrances' => [
            ['key' => 'loyal', 'level' => 'minor'],
            ['key' => 'overconfident', 'level' => 'major'],
        ],
        'edges'      => [['key' => 'brawny']],
        'names'      => ['Conan'],
    ];

    public function testApplySetsAttributesFromArchetype(): void
    {
        $entity = new Entity(['id' => 1, 'hash' => 'testhash']);

        $characterFactory = $this->getMockBuilder(CharacterFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['upsert'])
            ->getMock();
        $characterFactory->expects(self::once())
            ->method('upsert')
            ->with(self::callback(function (Entity $built) use ($entity): bool {
                self::assertSame(6, $built->agility);
                self::assertSame(4, $built->smarts);
                self::assertSame(6, $built->spirit);
                self::assertSame(8, $built->strength);
                self::assertSame(8, $built->vigor);
                self::assertSame('core', $built->sources);
                self::assertSame(0, $built->sharing);
                // simulate the insert setting id+hash
                $built->id   = $entity->id;
                $built->hash = $entity->hash;

                return true;
            }))
            ->willReturn(new Result());

        $skillFactory = $this->stubbedSkillFactory($entity);

        $applier = new Applier(
            $characterFactory,
            $skillFactory,
            $this->createStub(HindranceFactory::class),
            $this->createStub(EdgeFactory::class),
            $this->stubbedManager(),
        );

        $result = $applier->applyToNewCharacter($this->barbarian);
        self::assertSame('testhash', $result->hash);
    }

    public function testApplyPicksNameFromList(): void
    {
        $entity = new Entity(['id' => 1, 'hash' => 'h']);

        $characterFactory = $this->getMockBuilder(CharacterFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['upsert'])
            ->getMock();
        $characterFactory->expects(self::once())
            ->method('upsert')
            ->with(self::callback(function (Entity $built) use ($entity): bool {
                self::assertSame('Conan', $built->name);
                $built->id   = $entity->id;
                $built->hash = $entity->hash;

                return true;
            }))
            ->willReturn(new Result());

        $applier = new Applier(
            $characterFactory,
            $this->stubbedSkillFactory($entity),
            $this->createStub(HindranceFactory::class),
            $this->createStub(EdgeFactory::class),
            $this->stubbedManager(),
        );

        $applier->applyToNewCharacter($this->barbarian);
    }

    public function testApplyUpdatesExistingCoreSkillDie(): void
    {
        $entity = new Entity(['id' => 1, 'hash' => 'h']);
        $existingSkill = new Entity(['id' => 10, 'character_id' => 1, 'key' => 'fighting', 'die' => 4, 'core' => 'yes']);

        $characterFactory = $this->upsertReturnsEntity($entity);

        $skillFactory = $this->getMockBuilder(SkillFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['forCharacterAndKey', 'update', 'insert'])
            ->getMock();
        $skillFactory->expects(self::atLeastOnce())
            ->method('forCharacterAndKey')
            ->willReturnCallback(fn (Entity $c, string $key): ?Entity => match ($key) {
                'fighting' => $existingSkill,
                default    => null,
            });
        $skillFactory->expects(self::once())
            ->method('update')
            ->with(self::callback(fn (Entity $s): bool => $s->die === 8))
            ->willReturn(new Result());
        $skillFactory->expects(self::once())
            ->method('insert')
            ->willReturn(new Result());

        $applier = new Applier(
            $characterFactory,
            $skillFactory,
            $this->createStub(HindranceFactory::class),
            $this->createStub(EdgeFactory::class),
            $this->stubbedManager(),
        );

        $applier->applyToNewCharacter($this->barbarian);
    }

    public function testApplyInsertsNonCoreSkill(): void
    {
        $entity = new Entity(['id' => 1, 'hash' => 'h']);
        $characterFactory = $this->upsertReturnsEntity($entity);

        $skillFactory = $this->getMockBuilder(SkillFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['forCharacterAndKey', 'update', 'insert'])
            ->getMock();
        $skillFactory->method('forCharacterAndKey')->willReturn(null);
        $skillFactory->expects(self::exactly(2))
            ->method('insert')
            ->willReturn(new Result());
        $skillFactory->expects(self::never())->method('update');

        $applier = new Applier(
            $characterFactory,
            $skillFactory,
            $this->createStub(HindranceFactory::class),
            $this->createStub(EdgeFactory::class),
            $this->stubbedManager(),
        );

        $applier->applyToNewCharacter($this->barbarian);
    }

    public function testApplySyncsHindrances(): void
    {
        $entity = new Entity(['id' => 1, 'hash' => 'h']);
        $characterFactory = $this->upsertReturnsEntity($entity);

        $hindranceFactory = $this->getMockBuilder(HindranceFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['syncForCharacter'])
            ->getMock();
        $hindranceFactory->expects(self::once())
            ->method('syncForCharacter')
            ->with(
                self::callback(fn (Entity $e): bool => 1 === (int) $e->id),
                ['loyal' => 'minor', 'overconfident' => 'major'],
            )
            ->willReturn(new Result());

        $applier = new Applier(
            $characterFactory,
            $this->stubbedSkillFactory($entity),
            $hindranceFactory,
            $this->createStub(EdgeFactory::class),
            $this->stubbedManager(),
        );

        $applier->applyToNewCharacter($this->barbarian);
    }

    public function testApplySyncsEdges(): void
    {
        $entity = new Entity(['id' => 1, 'hash' => 'h']);
        $characterFactory = $this->upsertReturnsEntity($entity);

        $edgeFactory = $this->getMockBuilder(EdgeFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['syncForCharacter'])
            ->getMock();
        $edgeFactory->expects(self::once())
            ->method('syncForCharacter')
            ->with(
                self::callback(fn (Entity $e): bool => 1 === (int) $e->id),
                ['brawny' => 1],
            )
            ->willReturn(new Result());

        $applier = new Applier(
            $characterFactory,
            $this->stubbedSkillFactory($entity),
            $this->createStub(HindranceFactory::class),
            $edgeFactory,
            $this->stubbedManager(),
        );

        $applier->applyToNewCharacter($this->barbarian);
    }

    public function testApplyThrowsOnUpsertFailure(): void
    {
        $characterFactory = $this->getMockBuilder(CharacterFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['upsert'])
            ->getMock();
        $characterFactory->method('upsert')
            ->willReturn(new Result(['database error']));

        $applier = new Applier(
            $characterFactory,
            $this->createStub(SkillFactory::class),
            $this->createStub(HindranceFactory::class),
            $this->createStub(EdgeFactory::class),
            $this->stubbedManager(),
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('database error');

        $applier->applyToNewCharacter($this->barbarian);
    }

    private function upsertReturnsEntity(Entity $entity): CharacterFactory
    {
        $factory = $this->getMockBuilder(CharacterFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['upsert'])
            ->getMock();
        $factory->method('upsert')
            ->willReturnCallback(function (Entity $built) use ($entity): Result {
                $built->id   = $entity->id;
                $built->hash = $entity->hash;

                return new Result();
            });

        return $factory;
    }

    private function stubbedSkillFactory(Entity $entity): SkillFactory
    {
        $factory = $this->getMockBuilder(SkillFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['forCharacterAndKey', 'update', 'insert'])
            ->getMock();
        $factory->method('forCharacterAndKey')->willReturn(null);
        $factory->method('insert')->willReturn(new Result());
        $factory->method('update')->willReturn(new Result());

        return $factory;
    }

    private function stubbedManager(): Manager
    {
        $skillsData = $this->getMockBuilder(SkillsData::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['forId'])
            ->getMock();
        $skillsData->method('forId')
            ->willReturnCallback(fn (string $key): ?array => match ($key) {
                'fighting'   => ['id' => 'fighting', 'linked_attribute' => 'Agility', 'core_skill' => true],
                'survival'   => ['id' => 'survival', 'linked_attribute' => 'Smarts', 'core_skill' => false],
                default      => null,
            });

        $manager = $this->getMockBuilder(Manager::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getType'])
            ->getMock();
        $manager->method('getType')->willReturn($skillsData);

        return $manager;
    }
}
