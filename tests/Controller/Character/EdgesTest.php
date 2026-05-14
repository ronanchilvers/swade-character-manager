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
        $this->mapRequest('POST');
        $this->mapRedirectToException();
        $this->mapUrls(['characters_sheet' => '/characters/sheet/{hash}']);

        try {
            $this->controller($character, $edgeFactory)->index('charhash');
            self::fail('Expected redirect');
        } catch (RedirectedResponse $redirected) {
            self::assertSame('/characters/sheet/charhash', $redirected->url);
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

    private function controller(?Entity $character, ?Edge $edgeFactory = null): Edges
    {
        $characterFactory = $this->createStub(Character::class);
        $characterFactory->method('forHash')
            ->willReturn($character);

        $manager = $this->createStub(Manager::class);
        $manager->method('getType')
            ->willReturn(new EdgesData(__DIR__ . '/../../../data'));

        return new Edges(
            $characterFactory,
            $edgeFactory ?? $this->createStub(Edge::class),
            $manager,
        );
    }
}
