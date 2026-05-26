<?php

declare(strict_types=1);

namespace Tests\Controller\Character;

use App\Character\Sheet as SheetPresenter;
use App\Controller\Character\Sheet;
use App\Entity;
use App\Entity\Factory\Campaign as FactoryCampaign;
use App\Entity\Factory\Campaign\Member as FactoryMember;
use App\Entity\Factory\Character;
use App\Entity\Factory\Edge;
use App\Entity\Factory\Gear;
use App\Entity\Factory\Hindrance;
use App\Entity\Factory\Result;
use App\Entity\Factory\Skill;
use App\Entity\Factory\User;
use App\Entity\Factory\Weapon;
use App\Service\Data\Manager;
use Flight;
use Tests\Support\ControllerTestCase;
use Tests\Support\RedirectedResponse;
use Tests\Support\RenderedResponse;

class SheetControllerTest extends ControllerTestCase
{
    public function testIndexRedirectsMissingCharacter(): void
    {
        $characterFactory = $this->characterLookup(null);
        $session = $this->mapSession();
        $this->mapRedirectToException();
        $this->mapUrls(['home_page' => '/']);

        try {
            $this->controller($characterFactory)->index('missing');
            self::fail('Expected redirect');
        } catch (RedirectedResponse $redirected) {
            self::assertSame('/', $redirected->url);
        }

        self::assertSame(['Unable to find character'], $session->errors);
    }

    public function testIndexRedirectsWhenNonOwnerViewsAnotherCharacter(): void
    {
        $character = new Entity(['id' => 3, 'hash' => 'charhash', 'user' => 7, 'name' => 'Mara']);
        $session = $this->mapSession();
        $this->mapUser((object) ['id' => 8], false, false);
        $this->mapRedirectToException();
        $this->mapUrls(['home_page' => '/']);

        try {
            $this->controller($this->characterLookup($character))->index('charhash');
            self::fail('Expected redirect');
        } catch (RedirectedResponse $redirected) {
            self::assertSame('/', $redirected->url);
        }

        self::assertSame(['Unable to find character'], $session->errors);
    }

    public function testIndexAllowsCampaignMemberAsReadOnly(): void
    {
        $character = new Entity(['id' => 3, 'hash' => 'charhash', 'user' => 7, 'campaign' => 5, 'name' => 'Mara']);
        $campaign = new Entity(['id' => 5]);

        $campaignFactory = $this->getMockBuilder(FactoryCampaign::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['byId'])
            ->getMock();
        $campaignFactory->expects(self::exactly(2))
            ->method('byId')
            ->with(5)
            ->willReturn($campaign);

        $memberFactory = $this->getMockBuilder(FactoryMember::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isMember'])
            ->getMock();
        $memberFactory->expects(self::once())
            ->method('isMember')
            ->with($campaign, 8)
            ->willReturn(true);

        $this->mapUser((object) ['id' => 8], false, false);
        $this->mapRenderToException();

        try {
            $this->controller(
                characterFactory: $this->characterLookup($character),
                campaignFactory: $campaignFactory,
                memberFactory: $memberFactory,
                presenter: $this->presenterReturning([]),
            )->index('charhash');
            self::fail('Expected render');
        } catch (RenderedResponse $rendered) {
            self::assertSame('character/sheet.twig', $rendered->template);
            self::assertTrue($rendered->data['read_only']);
        }
    }

    public function testIndexRedirectsNonMemberCampaignViewer(): void
    {
        $character = new Entity(['id' => 3, 'hash' => 'charhash', 'user' => 7, 'campaign' => 5]);
        $campaign = new Entity(['id' => 5]);

        $campaignFactory = $this->getMockBuilder(FactoryCampaign::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['byId'])
            ->getMock();
        $campaignFactory->expects(self::once())
            ->method('byId')
            ->with(5)
            ->willReturn($campaign);

        $memberFactory = $this->getMockBuilder(FactoryMember::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isMember'])
            ->getMock();
        $memberFactory->expects(self::once())
            ->method('isMember')
            ->with($campaign, 8)
            ->willReturn(false);

        $session = $this->mapSession();
        $this->mapUser((object) ['id' => 8], false, false);
        $this->mapRedirectToException();
        $this->mapUrls(['home_page' => '/']);

        try {
            $this->controller(
                characterFactory: $this->characterLookup($character),
                campaignFactory: $campaignFactory,
                memberFactory: $memberFactory,
            )->index('charhash');
            self::fail('Expected redirect');
        } catch (RedirectedResponse $redirected) {
            self::assertSame('/', $redirected->url);
        }

        self::assertSame(['Unable to find character'], $session->errors);
    }

    public function testIndexRendersOwnerSheet(): void
    {
        $character = new Entity(['id' => 3, 'hash' => 'charhash', 'user' => 7, 'name' => 'Mara']);
        $hindrances = [new Entity(['key' => 'bad_eyes'])];
        $skills = [new Entity(['key' => 'athletics'])];
        $edges = [new Entity(['key' => 'alertness'])];
        $gear = [new Entity(['name' => 'Rope'])];
        $weapons = [new Entity(['name' => 'Knife'])];

        $hindranceFactory = $this->forCharacterFactory(Hindrance::class, $character, $hindrances);
        $skillFactory = $this->forCharacterFactory(Skill::class, $character, $skills);
        $edgeFactory = $this->forCharacterFactory(Edge::class, $character, $edges);
        $gearFactory = $this->forCharacterFactory(Gear::class, $character, $gear);
        $weaponFactory = $this->forCharacterFactory(Weapon::class, $character, $weapons);

        $manager = $this->createStub(Manager::class);
        $characterFactory = $this->characterLookup($character);
        $presenter = $this->getMockBuilder(SheetPresenter::class)
            ->onlyMethods(['build'])
            ->getMock();
        $presenter->expects(self::once())
            ->method('build')
            ->with($character, $hindrances, $skills, $edges, $manager, $characterFactory, $gear, $weapons)
            ->willReturn(['identity' => ['name' => 'Mara']]);

        $this->mapUser((object) ['id' => 7], false, false);
        $this->mapRenderToException();

        try {
            $this->controller(
                characterFactory: $characterFactory,
                hindranceFactory: $hindranceFactory,
                skillFactory: $skillFactory,
                edgeFactory: $edgeFactory,
                gearFactory: $gearFactory,
                weaponFactory: $weaponFactory,
                manager: $manager,
                presenter: $presenter,
            )->index('charhash');
            self::fail('Expected render');
        } catch (RenderedResponse $rendered) {
            self::assertSame('character/sheet.twig', $rendered->template);
            self::assertSame('Mara', $rendered->data['page_title']);
            self::assertSame($character, $rendered->data['entity']);
            self::assertFalse($rendered->data['user']);
            self::assertSame(['identity' => ['name' => 'Mara']], $rendered->data['sheet']);
            self::assertFalse($rendered->data['read_only']);
        }
    }

    public function testIndexAllowsSuperuserAndIncludesOwner(): void
    {
        $character = new Entity(['id' => 3, 'hash' => 'charhash', 'user' => 7, 'name' => 'Mara']);
        $owner = new Entity(['id' => 7, 'email' => 'owner@example.com']);
        $userFactory = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['byId'])
            ->getMock();
        $userFactory->expects(self::once())
            ->method('byId')
            ->with(7)
            ->willReturn($owner);

        $this->mapUser((object) ['id' => 99], true, true);
        $this->mapRenderToException();

        try {
            $this->controller(
                characterFactory: $this->characterLookup($character),
                userFactory: $userFactory,
                presenter: $this->presenterReturning([]),
            )->index('charhash');
            self::fail('Expected render');
        } catch (RenderedResponse $rendered) {
            self::assertSame($owner, $rendered->data['user']);
            self::assertFalse($rendered->data['read_only']);
        }
    }

    public function testSharedRendersReadOnlyPublicSheet(): void
    {
        $character = new Entity([
            'id' => 3,
            'hash' => 'charhash',
            'share_token' => str_repeat('a', 64),
            'sharing' => 1,
            'user' => 7,
            'name' => 'Mara',
            'campaign' => 5,
        ]);
        $characterFactory = $this->getMockBuilder(Character::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['forShareToken'])
            ->getMock();
        $characterFactory->expects(self::once())
            ->method('forShareToken')
            ->with(str_repeat('a', 64))
            ->willReturn($character);

        $campaignFactory = $this->getMockBuilder(FactoryCampaign::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['byId'])
            ->getMock();
        $campaignFactory->expects(self::never())
            ->method('byId');

        $this->mapRenderToException();

        try {
            $this->controller(
                characterFactory: $characterFactory,
                campaignFactory: $campaignFactory,
                presenter: $this->presenterReturning(['identity' => ['name' => 'Mara']]),
            )->shared(str_repeat('a', 64));
            self::fail('Expected render');
        } catch (RenderedResponse $rendered) {
            self::assertSame('character/sheet.twig', $rendered->template);
            self::assertTrue($rendered->data['read_only']);
            self::assertTrue($rendered->data['public_sheet']);
            self::assertNull($rendered->data['campaign']);
            self::assertSame(['identity' => ['name' => 'Mara']], $rendered->data['sheet']);
        }
    }

    public function testSharedReturnsNotFoundForMissingOrDisabledToken(): void
    {
        $characterFactory = $this->getMockBuilder(Character::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['forShareToken'])
            ->getMock();
        $characterFactory->expects(self::once())
            ->method('forShareToken')
            ->with(str_repeat('a', 64))
            ->willReturn(null);
        Flight::map('notFound', function (): void {
            throw new SheetNotFound();
        });

        try {
            $this->controller(characterFactory: $characterFactory)->shared(str_repeat('a', 64));
            self::fail('Expected not found');
        } catch (SheetNotFound) {
            self::assertTrue(true);
        }
    }

    public function testUpdateStateClampsKnownFieldsAndReturnsJsonSuccess(): void
    {
        $character = new Entity(['id' => 3, 'hash' => 'charhash', 'user' => 7]);
        $characterFactory = $this->getMockBuilder(Character::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['forHash', 'update'])
            ->getMock();
        $characterFactory->method('forHash')
            ->willReturn($character);
        $characterFactory->expects(self::once())
            ->method('update')
            ->with(self::callback(fn (Entity $entity): bool => 0 === $entity->wounds
                && 2 === $entity->fatigue
                && 1 === $entity->incapacitated
                && 3 === $entity->bennies
                && !isset($entity->ignored)))
            ->willReturn(new Result());

        $this->mapUser((object) ['id' => 7], false, false);
        $response = $this->mapJsonToResponse();
        $this->mapRequest('POST', body: json_encode([
            'wounds' => -2,
            'fatigue' => 2,
            'incapacitated' => 1,
            'bennies' => 3,
            'ignored' => 99,
        ]));

        $this->controller(characterFactory: $characterFactory)->updateState('charhash');

        self::assertSame(200, $response->statusCode);
        self::assertSame('{"ok":true}', $response->body);
    }

    public function testUpdateNotesStoresStringAndReturnsJsonSuccess(): void
    {
        $character = new Entity(['id' => 3, 'hash' => 'charhash', 'user' => 7]);
        $characterFactory = $this->getMockBuilder(Character::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['forHash', 'update'])
            ->getMock();
        $characterFactory->method('forHash')
            ->willReturn($character);
        $characterFactory->expects(self::once())
            ->method('update')
            ->with(self::callback(fn (Entity $entity): bool => 'Session notes' === $entity->notes))
            ->willReturn(new Result());

        $this->mapUser((object) ['id' => 7], false, false);
        $response = $this->mapJsonToResponse();
        $this->mapRequest('POST', body: json_encode(['notes' => 'Session notes']));

        $this->controller(characterFactory: $characterFactory)->updateNotes('charhash');

        self::assertSame('{"ok":true}', $response->body);
    }

    public function testJsonUpdateReturnsErrorsForFailedResult(): void
    {
        $character = new Entity(['id' => 3, 'hash' => 'charhash', 'user' => 7]);
        $characterFactory = $this->getMockBuilder(Character::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['forHash', 'update'])
            ->getMock();
        $characterFactory->expects(self::once())
            ->method('forHash')
            ->willReturn($character);
        $characterFactory->expects(self::once())
            ->method('update')
            ->willReturn(new Result(['database failed']));

        $this->mapUser((object) ['id' => 7], false, false);
        $response = $this->mapJsonToResponse();
        $this->mapRequest('POST', body: '{}');

        $this->controller(characterFactory: $characterFactory)->updateState('charhash');

        self::assertSame(422, $response->statusCode);
        self::assertSame('{"ok":false,"errors":["database failed"]}', $response->body);
    }

    public function testJsonUpdateReturnsNotFoundForMissingHash(): void
    {
        $response = $this->mapJsonToResponse();
        $this->mapRequest('POST', body: '{}');

        $this->controller(characterFactory: $this->characterLookup(null))->updateState('missing');

        self::assertSame(404, $response->statusCode);
        self::assertSame('{"ok":false,"errors":["Not found"]}', $response->body);
    }

    public function testUpdateNotesReturnsNotFoundForMissingHash(): void
    {
        $response = $this->mapJsonToResponse();
        $this->mapRequest('POST', body: '{}');

        $this->controller(characterFactory: $this->characterLookup(null))->updateNotes('missing');

        self::assertSame(404, $response->statusCode);
        self::assertSame('{"ok":false,"errors":["Not found"]}', $response->body);
    }

    public function testUpdateGearReturnsNotFoundForMissingHash(): void
    {
        $response = $this->mapJsonToResponse();
        $this->mapRequest('POST', body: '{}');

        $this->controller(characterFactory: $this->characterLookup(null))->updateGear('missing');

        self::assertSame(404, $response->statusCode);
        self::assertSame('{"ok":false,"errors":["Not found"]}', $response->body);
    }

    public function testUpdateWeaponsReturnsNotFoundForMissingHash(): void
    {
        $response = $this->mapJsonToResponse();
        $this->mapRequest('POST', body: '{}');

        $this->controller(characterFactory: $this->characterLookup(null))->updateWeapons('missing');

        self::assertSame(404, $response->statusCode);
        self::assertSame('{"ok":false,"errors":["Not found"]}', $response->body);
    }

    public function testUpdateGearPassesRowsArray(): void
    {
        $character = new Entity(['id' => 3, 'hash' => 'charhash', 'user' => 7]);
        $rows = [['name' => 'Rope']];
        $gearFactory = $this->getMockBuilder(Gear::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['syncForCharacter'])
            ->getMock();
        $gearFactory->expects(self::once())
            ->method('syncForCharacter')
            ->with($character, $rows)
            ->willReturn(new Result());

        $this->mapUser((object) ['id' => 7], false, false);
        $this->mapJsonToResponse();
        $this->mapRequest('POST', body: json_encode(['rows' => $rows]));

        $this->controller(
            characterFactory: $this->characterLookup($character),
            gearFactory: $gearFactory,
        )->updateGear('charhash');
    }

    public function testUpdateGearTreatsNonArrayRowsAsEmptyArray(): void
    {
        $character = new Entity(['id' => 3, 'hash' => 'charhash', 'user' => 7]);
        $gearFactory = $this->getMockBuilder(Gear::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['syncForCharacter'])
            ->getMock();
        $gearFactory->expects(self::once())
            ->method('syncForCharacter')
            ->with($character, [])
            ->willReturn(new Result());

        $this->mapUser((object) ['id' => 7], false, false);
        $this->mapJsonToResponse();
        $this->mapRequest('POST', body: json_encode(['rows' => 'not-array']));

        $this->controller(
            characterFactory: $this->characterLookup($character),
            gearFactory: $gearFactory,
        )->updateGear('charhash');
    }

    public function testUpdateWeaponsPassesRowsArray(): void
    {
        $character = new Entity(['id' => 3, 'hash' => 'charhash', 'user' => 7]);
        $rows = [['name' => 'Knife']];
        $weaponFactory = $this->getMockBuilder(Weapon::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['syncForCharacter'])
            ->getMock();
        $weaponFactory->expects(self::once())
            ->method('syncForCharacter')
            ->with($character, $rows)
            ->willReturn(new Result());

        $this->mapUser((object) ['id' => 7], false, false);
        $this->mapJsonToResponse();
        $this->mapRequest('POST', body: json_encode(['rows' => $rows]));

        $this->controller(
            characterFactory: $this->characterLookup($character),
            weaponFactory: $weaponFactory,
        )->updateWeapons('charhash');
    }

    public function testUpdateWeaponsTreatsNonArrayRowsAsEmptyArray(): void
    {
        $character = new Entity(['id' => 3, 'hash' => 'charhash', 'user' => 7]);
        $weaponFactory = $this->getMockBuilder(Weapon::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['syncForCharacter'])
            ->getMock();
        $weaponFactory->expects(self::once())
            ->method('syncForCharacter')
            ->with($character, [])
            ->willReturn(new Result());

        $this->mapUser((object) ['id' => 7], false, false);
        $this->mapJsonToResponse();
        $this->mapRequest('POST', body: json_encode(['rows' => 'not-array']));

        $this->controller(
            characterFactory: $this->characterLookup($character),
            weaponFactory: $weaponFactory,
        )->updateWeapons('charhash');
    }

    public function testJsonUpdateBlocksNonOwner(): void
    {
        $character = new Entity(['id' => 3, 'hash' => 'charhash', 'user' => 7]);
        $characterFactory = $this->getMockBuilder(Character::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['forHash', 'update'])
            ->getMock();
        $characterFactory->method('forHash')
            ->willReturn($character);
        $characterFactory->expects(self::never())
            ->method('update');

        $this->mapUser((object) ['id' => 99], false, false);
        $response = $this->mapJsonToResponse();
        $this->mapRequest('POST', body: '{}');

        $this->controller(characterFactory: $characterFactory)->updateNotes('charhash');

        self::assertSame(404, $response->statusCode);
        self::assertSame('{"ok":false,"errors":["Not found"]}', $response->body);
    }

    private function mapUser(object $user, bool $isSuperUser, bool $isSuperSession): void
    {
        Flight::map('user', fn (): object => $user);
        Flight::map('isSuperUser', fn (?object $current = null): bool => $isSuperUser);
        Flight::map('isSuperSession', fn (): bool => $isSuperSession);
    }

    private function characterLookup(?Entity $character): Character
    {
        $factory = $this->getMockBuilder(Character::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['forHash'])
            ->getMock();
        $factory->expects(self::once())
            ->method('forHash')
            ->willReturn($character);

        return $factory;
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

    private function presenterReturning(array $sheet): SheetPresenter
    {
        $presenter = $this->createStub(SheetPresenter::class);
        $presenter->method('build')
            ->willReturn($sheet);

        return $presenter;
    }

    private function controller(
        ?Character $characterFactory = null,
        ?Hindrance $hindranceFactory = null,
        ?Skill $skillFactory = null,
        ?Edge $edgeFactory = null,
        ?Gear $gearFactory = null,
        ?Weapon $weaponFactory = null,
        ?User $userFactory = null,
        ?Manager $manager = null,
        ?SheetPresenter $presenter = null,
        ?FactoryCampaign $campaignFactory = null,
        ?FactoryMember $memberFactory = null,
    ): Sheet {
        return new Sheet(
            $characterFactory ?? $this->createStub(Character::class),
            $hindranceFactory ?? $this->createStub(Hindrance::class),
            $skillFactory ?? $this->createStub(Skill::class),
            $edgeFactory ?? $this->createStub(Edge::class),
            $gearFactory ?? $this->createStub(Gear::class),
            $weaponFactory ?? $this->createStub(Weapon::class),
            $userFactory ?? $this->createStub(User::class),
            $manager ?? $this->createStub(Manager::class),
            $presenter ?? $this->presenterReturning([]),
            $campaignFactory ?? $this->createStub(FactoryCampaign::class),
            $memberFactory ?? $this->createStub(FactoryMember::class),
        );
    }
}

class SheetNotFound extends \Error
{
}
