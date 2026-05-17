<?php

declare(strict_types=1);

namespace Tests\Controller\Character;

use App\Character\Sheet as SheetPresenter;
use App\Controller\Character\Share;
use App\Entity;
use App\Entity\Factory\Character;
use App\Entity\Factory\Edge;
use App\Entity\Factory\Gear;
use App\Entity\Factory\Hindrance;
use App\Entity\Factory\Skill;
use App\Entity\Factory\Weapon;
use App\Service\Data\Manager;
use Flight;
use Tests\Support\ControllerTestCase;
use Tests\Support\RenderedResponse;

class ShareControllerTest extends ControllerTestCase
{
    public function testIndexReturns404ForUnknownToken(): void
    {
        $factory = $this->getMockBuilder(Character::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['forShareToken'])
            ->getMock();
        $factory->expects(self::once())
            ->method('forShareToken')
            ->with('missingtoken00000000000000000000')
            ->willReturn(null);

        Flight::map('notFound', function (): void {
            throw new \RuntimeException('not_found');
        });

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('not_found');

        $this->controller($factory)->index('missingtoken00000000000000000000');
    }

    public function testIndexRendersSharedSheet(): void
    {
        $character = new Entity(['id' => 3, 'share_token' => str_repeat('a', 32), 'name' => 'Mara']);
        $hindrances = [new Entity(['key' => 'bad_eyes'])];
        $skills     = [new Entity(['key' => 'athletics'])];
        $edges      = [new Entity(['key' => 'alertness'])];
        $gear       = [new Entity(['name' => 'Rope'])];
        $weapons    = [new Entity(['name' => 'Knife'])];

        $factory = $this->getMockBuilder(Character::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['forShareToken'])
            ->getMock();
        $factory->expects(self::once())
            ->method('forShareToken')
            ->with(str_repeat('a', 32))
            ->willReturn($character);

        $hindranceFactory = $this->forCharacterFactory(Hindrance::class, $character, $hindrances);
        $skillFactory     = $this->forCharacterFactory(Skill::class,     $character, $skills);
        $edgeFactory      = $this->forCharacterFactory(Edge::class,      $character, $edges);
        $gearFactory      = $this->forCharacterFactory(Gear::class,      $character, $gear);
        $weaponFactory    = $this->forCharacterFactory(Weapon::class,    $character, $weapons);

        $manager = $this->createStub(Manager::class);

        $presenter = $this->getMockBuilder(SheetPresenter::class)
            ->onlyMethods(['build'])
            ->getMock();
        $presenter->expects(self::once())
            ->method('build')
            ->with($character, $hindrances, $skills, $edges, $manager, $factory, $gear, $weapons)
            ->willReturn(['identity' => ['name' => 'Mara']]);

        $this->mapRenderToException();

        try {
            $this->controller(
                characterFactory: $factory,
                hindranceFactory: $hindranceFactory,
                skillFactory: $skillFactory,
                edgeFactory: $edgeFactory,
                gearFactory: $gearFactory,
                weaponFactory: $weaponFactory,
                manager: $manager,
                presenter: $presenter,
            )->index(str_repeat('a', 32));
            self::fail('Expected render');
        } catch (RenderedResponse $rendered) {
            self::assertSame('character/share.twig', $rendered->template);
            self::assertSame('Mara', $rendered->data['page_title']);
            self::assertSame($character, $rendered->data['entity']);
            self::assertSame(['identity' => ['name' => 'Mara']], $rendered->data['sheet']);
        }
    }

    private function forCharacterFactory(string $class, Entity $character, array $rows): object
    {
        $factory = $this->getMockBuilder($class)
            ->disableOriginalConstructor()
            ->onlyMethods(['forCharacter'])
            ->getMock();
        $factory->expects(self::once())
            ->method('forCharacter')
            ->with($character)
            ->willReturn($rows);

        return $factory;
    }

    private function controller(
        ?Character $characterFactory = null,
        ?Hindrance $hindranceFactory = null,
        ?Skill $skillFactory = null,
        ?Edge $edgeFactory = null,
        ?Gear $gearFactory = null,
        ?Weapon $weaponFactory = null,
        ?Manager $manager = null,
        ?SheetPresenter $presenter = null,
    ): Share {
        return new Share(
            $characterFactory ?? $this->createStub(Character::class),
            $hindranceFactory ?? $this->createStub(Hindrance::class),
            $skillFactory     ?? $this->createStub(Skill::class),
            $edgeFactory      ?? $this->createStub(Edge::class),
            $gearFactory      ?? $this->createStub(Gear::class),
            $weaponFactory    ?? $this->createStub(Weapon::class),
            $manager          ?? $this->createStub(Manager::class),
            $presenter        ?? $this->createStub(SheetPresenter::class),
        );
    }
}
