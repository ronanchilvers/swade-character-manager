<?php

declare(strict_types=1);

namespace Tests\Controller\Character;

use App\Controller\Character\Edges;
use App\Entity;
use App\Entity\Factory\Character;
use App\Entity\Factory\Edge;
use App\Entity\Factory\Result;
use App\Service\Data\Edges as EdgesData;
use App\Service\Data\Manager;
use App\Service\Sources;
use flight\database\SimplePdo;
use Tests\Support\ControllerTestCase;
use Tests\Support\RedirectedResponse;
use Tests\Support\RenderedResponse;

class EdgesTest extends ControllerTestCase
{
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

    public function testGetLoadsPersistedEdgesAndGroupsCatalogByCategory(): void
    {
        $character = new Entity(['hash' => 'charhash', 'name' => 'Mara']);
        $edgeFactory = $this->getMockBuilder(Edge::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['forCharacter'])
            ->getMock();
        $edgeFactory->expects(self::once())
            ->method('forCharacter')
            ->with($character)
            ->willReturn([new Entity(['key' => 'alertness', 'count' => 1])]);

        $this->mapRequest('GET');
        $this->mapRenderToException();

        try {
            $this->controller($character, $edgeFactory)->index('charhash');
            self::fail('Expected render');
        } catch (RenderedResponse $rendered) {
            self::assertSame('character/edges.twig', $rendered->template);
            self::assertSame(['alertness' => 1], $rendered->data['selected']);
            self::assertArrayHasKey('background', $rendered->data['edges_by_category']);
        }
    }

    public function testPostNormalisesSelectedEdgesAndRedirects(): void
    {
        $_POST = ['edges' => ['alertness' => '3', 'new_powers' => '2', 'not_real' => '4']];
        $character = new Entity(['hash' => 'charhash', 'name' => 'Mara']);

        $edgeFactory = $this->getMockBuilder(Edge::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['syncForCharacter'])
            ->getMock();
        $edgeFactory->expects(self::once())
            ->method('syncForCharacter')
            ->with($character, ['alertness' => 1, 'new_powers' => 2])
            ->willReturn(new Result());

        $session = $this->mapSession();
        $this->mapRequest('POST', url: '/characters/edges/charhash');
        \Flight::map('reload', function (): void {
            throw new RedirectedResponse(\Flight::request()->url);
        });

        try {
            $this->controller($character, $edgeFactory)->index('charhash');
            self::fail('Expected redirect');
        } catch (RedirectedResponse $redirected) {
            self::assertSame('/characters/edges/charhash', $redirected->url);
        }

        self::assertSame(['Saved character Mara successfully'], $session->successes);
    }

    public function testPostRendersSyncErrors(): void
    {
        $_POST = ['edges' => ['alertness' => '1']];
        $character = new Entity(['hash' => 'charhash', 'name' => 'Mara']);

        $edgeFactory = $this->getMockBuilder(Edge::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['syncForCharacter'])
            ->getMock();
        $edgeFactory->expects(self::once())
            ->method('syncForCharacter')
            ->willReturn(new Result(['database failed']));

        $session = $this->mapSession();
        $this->mapRequest('POST');
        $this->mapRenderToException();

        try {
            $this->controller($character, $edgeFactory)->index('charhash');
            self::fail('Expected render');
        } catch (RenderedResponse $rendered) {
            self::assertSame(['database failed'], $rendered->data['errors']);
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
                $this->catalogRow('alertness', 'core', 'Alertness', 'background'),
                $this->catalogRow('fantasy_edge', 'fantasy', 'Fantasy Edge', 'fantasy'),
            ]);

        $manager = $this->createStub(Manager::class);
        $manager->method('getType')
            ->willReturn(new EdgesData(__DIR__ . '/../../../data', $pdo));

        $this->mapRequest('GET');
        $this->mapRenderToException();

        try {
            $this->controller($character, manager: $manager)->index('charhash');
            self::fail('Expected render');
        } catch (RenderedResponse $rendered) {
            self::assertSame(['background'], array_keys($rendered->data['edges_by_category']));
            self::assertSame('alertness', $rendered->data['edges_by_category']['background'][0]['id']);
        }
    }

    private function controller(
        ?Entity $character,
        ?Edge $edgeFactory = null,
        ?Manager $manager = null,
    ): Edges
    {
        $characterFactory = $this->createStub(Character::class);
        $characterFactory->method('forHash')
            ->willReturn($character);

        if (!$manager instanceof Manager) {
            $manager = $this->createStub(Manager::class);
            $manager->method('getType')
                ->willReturn(new EdgesData(__DIR__ . '/../../../data'));
        }

        return new Edges(
            $characterFactory,
            $edgeFactory ?? $this->createStub(Edge::class),
            $manager,
            new Sources(),
        );
    }

    private function catalogRow(string $key, string $source, string $name, string $category): array
    {
        return [
            'edge_catalog_key' => $key,
            'edge_catalog_source' => $source,
            'edge_catalog_name' => $name,
            'edge_catalog_category' => $category,
            'edge_catalog_repeatable' => '0',
            'edge_catalog_summary' => '',
            'edge_catalog_requirements' => '[]',
            'edge_catalog_effects' => '[]',
            'edge_catalog_notes' => '[]',
            'edge_catalog_source_pages' => '[]',
        ];
    }
}
