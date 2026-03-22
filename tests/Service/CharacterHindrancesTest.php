<?php

declare(strict_types=1);

namespace Tests\Service;

use App\Entity\Factory\Hindrance;
use App\Service\CharacterHindrances;
use App\Service\GameData;
use flight\database\SimplePdo;
use PHPUnit\Framework\TestCase;

class CharacterHindrancesTest extends TestCase
{
    public function testRejectsUnknownKeysBeforeWriting(): void
    {
        $pdo = $this->createMock(SimplePdo::class);
        $pdo->expects(self::never())->method('transaction');

        $factory = $this->createMock(Hindrance::class);
        $factory->expects(self::never())->method('insert');

        $service = new CharacterHindrances(
            $pdo,
            new GameData(__DIR__ . '/../../data'),
            $factory
        );

        $result = $service->processSubmission(10, [
            'not_real' => 'minor',
        ]);

        self::assertNotEmpty($result['errors']);
    }

    public function testRejectsTotalsAbovePointCap(): void
    {
        $pdo = $this->createMock(SimplePdo::class);
        $pdo->expects(self::never())->method('transaction');

        $factory = $this->createMock(Hindrance::class);
        $factory->expects(self::never())->method('insert');

        $service = new CharacterHindrances(
            $pdo,
            new GameData(__DIR__ . '/../../data'),
            $factory
        );

        $result = $service->processSubmission(10, [
            'bad_eyes' => 'major',
            'blind' => 'major',
            'bad_luck' => 'major',
        ]);

        self::assertContains('You may select up to 4 hindrance points.', $result['errors']);
    }

    public function testRemainingPointsCountsMinorAndMajorSelections(): void
    {
        $pdo = $this->createMock(SimplePdo::class);
        $factory = $this->createMock(Hindrance::class);

        $service = new CharacterHindrances(
            $pdo,
            new GameData(__DIR__ . '/../../data'),
            $factory
        );

        self::assertSame(1, $service->remainingPoints([
            'all_thumbs' => 'minor',
            'bad_eyes' => 'major',
        ]));
    }
}
