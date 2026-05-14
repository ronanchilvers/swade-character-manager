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
        $this->mapRequest('POST');
        $this->mapRedirectToException();
        $this->mapUrls(['characters_attributes' => '/characters/attributes/{hash}']);

        try {
            $this->controller($character, $hindranceFactory)->index('charhash');
            self::fail('Expected redirect');
        } catch (RedirectedResponse $redirected) {
            self::assertSame('/characters/attributes/charhash', $redirected->url);
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

    private function controller(?Entity $character, ?Hindrance $hindranceFactory = null): Hindrances
    {
        $characterFactory = $this->createStub(Character::class);
        $characterFactory->method('forHash')
            ->willReturn($character);

        $manager = $this->createStub(Manager::class);
        $manager->method('getType')
            ->willReturn(new HindrancesData(__DIR__ . '/../../../data'));

        return new Hindrances(
            $characterFactory,
            $hindranceFactory ?? $this->createStub(Hindrance::class),
            $manager,
        );
    }
}
