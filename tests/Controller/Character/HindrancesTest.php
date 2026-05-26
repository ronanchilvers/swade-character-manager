<?php

declare(strict_types=1);

namespace Tests\Controller\Character;

use App\Controller\Character\Hindrances;
use App\Entity;
use App\Entity\Factory\Character;
use App\Entity\Factory\Hindrance;
use App\Entity\Factory\Result;
use App\Service\Data\Hindrances as HindrancesData;
use App\Service\Data\Manager;
use App\Service\Sources;
use flight\database\SimplePdo;
use Tests\Support\ControllerTestCase;
use Tests\Support\RedirectedResponse;
use Tests\Support\RenderedResponse;

class HindrancesTest extends ControllerTestCase
{
    public function testGetLoadsPersistedSelectionsAndCatalogData(): void
    {
        $character = new Entity(['hash' => 'charhash', 'name' => 'Mara']);
        $hindranceFactory = $this->getMockBuilder(Hindrance::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['forCharacter'])
            ->getMock();
        $hindranceFactory->expects(self::once())
            ->method('forCharacter')
            ->with($character)
            ->willReturn([new Entity(['key' => 'bad_eyes', 'level' => 'minor'])]);

        $this->mapRequest('GET');
        $this->mapRenderToException();

        try {
            $this->controller($character, $hindranceFactory)->index('charhash');
            self::fail('Expected render');
        } catch (RenderedResponse $rendered) {
            self::assertSame('character/hindrances.twig', $rendered->template);
            self::assertSame(['bad_eyes' => 'minor'], $rendered->data['selected']);
            self::assertNotEmpty($rendered->data['hindrances']);
        }
    }

    public function testPostSyncsSelectionsAndRedirects(): void
    {
        $_POST = ['hindrances' => ['bad_eyes' => 'minor', '<bad>' => 'major']];
        $character = new Entity(['hash' => 'charhash', 'name' => 'Mara']);
        $hindranceFactory = $this->getMockBuilder(Hindrance::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['syncForCharacter'])
            ->getMock();
        $hindranceFactory->expects(self::once())
            ->method('syncForCharacter')
            ->with($character, ['bad_eyes' => 'minor', '<bad>' => 'major'])
            ->willReturn(new Result());

        $session = $this->mapSession();
        $this->mapRequest('POST', url: '/characters/hindrances/charhash');
        \Flight::map('reload', function (): void {
            throw new RedirectedResponse(\Flight::request()->url);
        });

        try {
            $this->controller($character, $hindranceFactory)->index('charhash');
            self::fail('Expected redirect');
        } catch (RedirectedResponse $redirected) {
            self::assertSame('/characters/hindrances/charhash', $redirected->url);
        }

        self::assertSame(['Saved character Mara successfully'], $session->successes);
    }

    public function testPostRendersSyncErrorsAndPreservesInput(): void
    {
        $_POST = ['hindrances' => ['bad_eyes' => 'minor']];
        $character = new Entity(['hash' => 'charhash', 'name' => 'Mara']);
        $hindranceFactory = $this->getMockBuilder(Hindrance::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['syncForCharacter'])
            ->getMock();
        $hindranceFactory->expects(self::once())
            ->method('syncForCharacter')
            ->willReturn(new Result(['database failed']));

        $session = $this->mapSession();
        $this->mapRequest('POST');
        $this->mapRenderToException();

        try {
            $this->controller($character, $hindranceFactory)->index('charhash');
            self::fail('Expected render');
        } catch (RenderedResponse $rendered) {
            self::assertSame(['database failed'], $rendered->data['errors']);
            self::assertSame(['bad_eyes' => 'minor'], $rendered->data['selected']);
        }

        self::assertSame(['Sorry! There was a problem!'], $session->errors);
    }

    public function testGetFiltersCatalogByCharacterSources(): void
    {
        $character = new Entity(['hash' => 'charhash', 'name' => 'Mara', 'sources' => 'core']);
        $pdo = $this->createMock(SimplePdo::class);
        $pdo->expects(self::once())
            ->method('fetchAll')
            ->willReturn([
                $this->catalogRow('all_thumbs', 'core', 'All Thumbs'),
                $this->catalogRow('fantasy_flaw', 'fantasy', 'Fantasy Flaw'),
            ]);

        $manager = $this->createStub(Manager::class);
        $manager->method('getType')
            ->willReturn(new HindrancesData(__DIR__ . '/../../../data', $pdo));

        $this->mapRequest('GET');
        $this->mapRenderToException();

        try {
            $this->controller($character, manager: $manager)->index('charhash');
            self::fail('Expected render');
        } catch (RenderedResponse $rendered) {
            self::assertSame(['all_thumbs'], array_column($rendered->data['hindrances'], 'id'));
        }
    }

    public function testMissingCharacterRedirectsHome(): void
    {
        $session = $this->mapSession();
        $this->mapRedirectToException();
        $this->mapUrls(['home_page' => '/']);

        try {
            $this->controller(null)->index('missing');
            self::fail('Expected redirect');
        } catch (RedirectedResponse $redirected) {
            self::assertSame('/', $redirected->url);
        }

        self::assertSame(['Unable to find character'], $session->errors);
    }

    private function controller(
        ?Entity $character,
        ?Hindrance $hindranceFactory = null,
        ?Manager $manager = null,
    ): Hindrances
    {
        $characterFactory = $this->createStub(Character::class);
        $characterFactory->method('forHash')
            ->willReturn($character);

        if (!$manager instanceof Manager) {
            $manager = $this->createStub(Manager::class);
            $manager->method('getType')
                ->willReturn(new HindrancesData(__DIR__ . '/../../../data'));
        }

        return new Hindrances(
            $characterFactory,
            $hindranceFactory ?? $this->createStub(Hindrance::class),
            $manager,
            new Sources(),
        );
    }

    private function catalogRow(string $key, string $source, string $name): array
    {
        return [
            'hindrance_catalog_key' => $key,
            'hindrance_catalog_source' => $source,
            'hindrance_catalog_name' => $name,
            'hindrance_catalog_summary' => '',
            'hindrance_catalog_levels' => '["minor"]',
            'hindrance_catalog_requirements' => '[]',
            'hindrance_catalog_effects' => '[]',
            'hindrance_catalog_notes' => '[]',
            'hindrance_catalog_source_pages' => '[]',
        ];
    }
}
