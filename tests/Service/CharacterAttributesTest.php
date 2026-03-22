<?php

declare(strict_types=1);

namespace Tests\Service;

use App\Entity;
use App\Entity\Factory\Character as FactoryCharacter;
use App\Service\CharacterAttributes;
use App\Service\CharacterHindrances;
use PHPUnit\Framework\TestCase;

class CharacterAttributesTest extends TestCase
{
    public function testDefaultsUseZeroAttributeAndHindrancePoints(): void
    {
        $factory = $this->createMock(FactoryCharacter::class);
        $hindrances = $this->createMock(CharacterHindrances::class);
        $hindrances->method('selectedPointsForCharacter')->with(10)->willReturn(0);

        $service = new CharacterAttributes($factory, $hindrances);
        $result = $service->viewData(new Entity([
            'id' => 10,
            'agility' => 4,
            'smarts' => 4,
            'spirit' => 4,
            'strength' => 4,
            'vigor' => 4,
        ]));

        self::assertSame(0, $result['allocation']['attribute_points_spent']);
        self::assertSame(5, $result['allocation']['attribute_points_remaining']);
        self::assertSame(0, $result['allocation']['hindrance_points_spent']);
        self::assertSame(0, $result['allocation']['hindrance_points_remaining']);
    }

    public function testFiveBaseAttributePointsAllowFiveTotalSteps(): void
    {
        $factory = $this->createMock(FactoryCharacter::class);
        $hindrances = $this->createMock(CharacterHindrances::class);
        $hindrances->method('selectedPointsForCharacter')->with(10)->willReturn(0);

        $service = new CharacterAttributes($factory, $hindrances);
        $result = $service->viewData(new Entity([
            'id' => 10,
            'agility' => 6,
            'smarts' => 6,
            'spirit' => 6,
            'strength' => 6,
            'vigor' => 6,
        ]));

        self::assertSame(5, $result['allocation']['attribute_points_spent']);
        self::assertSame(0, $result['allocation']['attribute_points_remaining']);
        self::assertSame(0, $result['allocation']['hindrance_points_spent']);
    }

    public function testSixthStepRequiresTwoHindrancePoints(): void
    {
        $factory = $this->createMock(FactoryCharacter::class);
        $hindrances = $this->createMock(CharacterHindrances::class);
        $hindrances->method('selectedPointsForCharacter')->with(10)->willReturn(2);

        $service = new CharacterAttributes($factory, $hindrances);
        $result = $service->viewData(new Entity([
            'id' => 10,
            'agility' => 8,
            'smarts' => 6,
            'spirit' => 6,
            'strength' => 6,
            'vigor' => 6,
        ]));

        self::assertSame(5, $result['allocation']['attribute_points_spent']);
        self::assertSame(2, $result['allocation']['hindrance_points_spent']);
        self::assertSame(0, $result['allocation']['hindrance_points_remaining']);
    }

    public function testOddHindrancePointTotalsLeaveOnePointUnusedForAttributes(): void
    {
        $factory = $this->createMock(FactoryCharacter::class);
        $hindrances = $this->createMock(CharacterHindrances::class);
        $hindrances->method('selectedPointsForCharacter')->with(10)->willReturn(3);

        $service = new CharacterAttributes($factory, $hindrances);
        $result = $service->viewData(new Entity([
            'id' => 10,
            'agility' => 8,
            'smarts' => 6,
            'spirit' => 6,
            'strength' => 6,
            'vigor' => 6,
        ]));

        self::assertSame(2, $result['allocation']['hindrance_points_spent']);
        self::assertSame(1, $result['allocation']['hindrance_points_remaining']);
    }

    public function testOverspendingHindrancePointsReturnsFormError(): void
    {
        $factory = $this->createMock(FactoryCharacter::class);
        $factory->expects(self::once())
            ->method('validate')
            ->willReturn([]);
        $factory->expects(self::never())
            ->method('upsert');

        $hindrances = $this->createMock(CharacterHindrances::class);
        $hindrances->method('selectedPointsForCharacter')->with(10)->willReturn(2);

        $service = new CharacterAttributes($factory, $hindrances);
        $result = $service->processSubmission(new Entity([
            'id' => 10,
            'hash' => str_repeat('a', 32),
            'user' => 1,
            'name' => 'Mara',
            'agility' => 4,
            'smarts' => 4,
            'spirit' => 4,
            'strength' => 4,
            'vigor' => 4,
        ]), [
            'agility' => 8,
            'smarts' => 8,
            'spirit' => 8,
            'strength' => 8,
            'vigor' => 8,
        ]);

        self::assertSame([], $result['errors']);
        self::assertNotEmpty($result['form_errors']);
        self::assertStringContainsString('require 10 hindrance points', $result['form_errors'][0]);
    }

    public function testValidSubmissionPersistsCharacterAndReturnsAllocationSummary(): void
    {
        $factory = $this->createMock(FactoryCharacter::class);
        $factory->expects(self::once())
            ->method('validate')
            ->willReturn([]);
        $factory->expects(self::once())
            ->method('upsert')
            ->with(self::callback(function (Entity $entity): bool {
                return $entity->agility === 8 &&
                    $entity->smarts === 6 &&
                    $entity->spirit === 6 &&
                    $entity->strength === 6 &&
                    $entity->vigor === 6;
            }))
            ->willReturn(true);

        $hindrances = $this->createMock(CharacterHindrances::class);
        $hindrances->method('selectedPointsForCharacter')->with(10)->willReturn(2);

        $service = new CharacterAttributes($factory, $hindrances);
        $result = $service->processSubmission(new Entity([
            'id' => 10,
            'hash' => str_repeat('a', 32),
            'user' => 1,
            'name' => 'Mara',
            'agility' => 4,
            'smarts' => 4,
            'spirit' => 4,
            'strength' => 4,
            'vigor' => 4,
        ]), [
            'agility' => 8,
            'smarts' => 6,
            'spirit' => 6,
            'strength' => 6,
            'vigor' => 6,
        ]);

        self::assertSame([], $result['errors']);
        self::assertSame([], $result['form_errors']);
        self::assertSame(5, $result['allocation']['attribute_points_spent']);
        self::assertSame(2, $result['allocation']['hindrance_points_spent']);
        self::assertSame(0, $result['allocation']['hindrance_points_remaining']);
    }
}
