<?php

declare(strict_types=1);

namespace Tests\Service;

use App\Entity;
use App\Entity\Factory\Skill;
use App\Service\CharacterSkills;
use App\Service\GameData;
use flight\database\SimplePdo;
use PHPUnit\Framework\TestCase;

class CharacterSkillsTest extends TestCase
{
    public function testViewDataDefaultsCoreSkillsToD4(): void
    {
        $pdo = $this->createMock(SimplePdo::class);
        $factory = $this->createMock(Skill::class);
        $factory->expects(self::once())
            ->method('forCharacter')
            ->with(10)
            ->willReturn([]);

        $service = new CharacterSkills($pdo, new GameData(__DIR__ . '/../../data'), $factory);
        $result = $service->viewData(new Entity([
            'id' => 10,
            'agility' => 6,
            'smarts' => 6,
            'spirit' => 6,
        ]));

        self::assertSame(0, $result['allocation']['skill_points_spent']);
        self::assertSame(12, $result['allocation']['skill_points_remaining']);
        self::assertSame(4, $this->skillById($result['skill_groups'], 'athletics')['selected_die']);
        self::assertSame(4, $this->skillById($result['skill_groups'], 'notice')['selected_die']);
        self::assertSame(0, $this->skillById($result['skill_groups'], 'fighting')['selected_die']);
    }

    public function testViewDataChargesOnePointPerStepUpToLinkedAttribute(): void
    {
        $pdo = $this->createMock(SimplePdo::class);
        $factory = $this->createMock(Skill::class);
        $factory->expects(self::once())
            ->method('forCharacter')
            ->with(10)
            ->willReturn([
                new Entity([
                    'key' => 'fighting',
                    'die' => 8,
                ]),
            ]);

        $service = new CharacterSkills($pdo, new GameData(__DIR__ . '/../../data'), $factory);
        $result = $service->viewData(new Entity([
            'id' => 10,
            'agility' => 8,
            'smarts' => 6,
            'spirit' => 6,
        ]));

        self::assertSame(3, $result['allocation']['skill_points_spent']);
        self::assertSame(9, $result['allocation']['skill_points_remaining']);
    }

    public function testViewDataChargesTwoPointsForStepsAboveLinkedAttribute(): void
    {
        $pdo = $this->createMock(SimplePdo::class);
        $factory = $this->createMock(Skill::class);
        $factory->expects(self::once())
            ->method('forCharacter')
            ->with(10)
            ->willReturn([
                new Entity([
                    'key' => 'stealth',
                    'die' => 6,
                ]),
            ]);

        $service = new CharacterSkills($pdo, new GameData(__DIR__ . '/../../data'), $factory);
        $result = $service->viewData(new Entity([
            'id' => 10,
            'agility' => 4,
            'smarts' => 6,
            'spirit' => 6,
        ]));

        self::assertSame(2, $result['allocation']['skill_points_spent']);
        self::assertSame(10, $result['allocation']['skill_points_remaining']);
    }

    public function testProcessSubmissionRejectsUnknownSkillKeys(): void
    {
        $pdo = $this->createMock(SimplePdo::class);
        $pdo->expects(self::never())->method('transaction');

        $factory = $this->createMock(Skill::class);
        $factory->expects(self::never())->method('insert');

        $service = new CharacterSkills($pdo, new GameData(__DIR__ . '/../../data'), $factory);
        $result = $service->processSubmission(new Entity([
            'id' => 10,
            'agility' => 6,
            'smarts' => 6,
            'spirit' => 6,
        ]), [
            'not_real' => 4,
        ]);

        self::assertContains('Unknown skill selected: not_real.', $result['form_errors']);
    }

    public function testProcessSubmissionRejectsOverspend(): void
    {
        $pdo = $this->createMock(SimplePdo::class);
        $pdo->expects(self::never())->method('transaction');

        $factory = $this->createMock(Skill::class);
        $factory->expects(self::never())->method('insert');

        $service = new CharacterSkills($pdo, new GameData(__DIR__ . '/../../data'), $factory);
        $result = $service->processSubmission(new Entity([
            'id' => 10,
            'agility' => 4,
            'smarts' => 4,
            'spirit' => 4,
        ]), [
            'athletics' => 12,
            'common_knowledge' => 12,
        ]);

        self::assertSame([], $result['errors']);
        self::assertNotEmpty($result['form_errors']);
        self::assertStringContainsString('require 16 skill points', $result['form_errors'][0]);
    }

    public function testProcessSubmissionReplacesPersistedRows(): void
    {
        $pdo = $this->createMock(SimplePdo::class);
        $pdo->expects(self::once())
            ->method('runQuery')
            ->with('DELETE FROM skills WHERE skill_character_id = ?', [10]);
        $pdo->expects(self::once())
            ->method('transaction')
            ->willReturnCallback(function (callable $callback) use ($pdo): void {
                $callback($pdo);
            });

        $factory = $this->createMock(Skill::class);
        $factory->expects(self::exactly(6))
            ->method('validate')
            ->willReturn([]);

        $inserted = [];
        $factory->expects(self::exactly(6))
            ->method('insert')
            ->willReturnCallback(function (Entity $entity) use (&$inserted): bool {
                $inserted[$entity->key] = $entity->die;
                return true;
            });

        $service = new CharacterSkills($pdo, new GameData(__DIR__ . '/../../data'), $factory);
        $result = $service->processSubmission(new Entity([
            'id' => 10,
            'agility' => 8,
            'smarts' => 6,
            'spirit' => 6,
        ]), [
            'fighting' => 8,
        ]);

        self::assertSame([], $result['errors']);
        self::assertSame([], $result['form_errors']);
        self::assertSame([
            'athletics' => 4,
            'common_knowledge' => 4,
            'notice' => 4,
            'persuasion' => 4,
            'stealth' => 4,
            'fighting' => 8,
        ], $inserted);
    }

    public function testViewDataShowsOverspendWhenAttributesDrop(): void
    {
        $pdo = $this->createMock(SimplePdo::class);
        $factory = $this->createMock(Skill::class);
        $factory->expects(self::once())
            ->method('forCharacter')
            ->with(10)
            ->willReturn([
                new Entity([
                    'key' => 'fighting',
                    'die' => 12,
                ]),
                new Entity([
                    'key' => 'academics',
                    'die' => 8,
                ]),
            ]);

        $service = new CharacterSkills($pdo, new GameData(__DIR__ . '/../../data'), $factory);
        $result = $service->viewData(new Entity([
            'id' => 10,
            'agility' => 4,
            'smarts' => 4,
            'spirit' => 4,
        ]));

        self::assertNotEmpty($result['form_errors']);
        self::assertStringContainsString('require 14 skill points', $result['form_errors'][0]);
    }

    private function skillById(array $groups, string $id): array
    {
        foreach ($groups as $group) {
            foreach ($group['skills'] as $skill) {
                if ($skill['id'] === $id) {
                    return $skill;
                }
            }
        }

        self::fail(sprintf('Unable to find skill %s.', $id));
    }
}
