<?php

declare(strict_types=1);

namespace Tests\Controller\Character;

use App\Controller\Character\Skills;
use App\Entity;
use App\Entity\Factory\Character;
use App\Entity\Factory\Result;
use App\Entity\Factory\Skill;
use App\Service\Data\Manager;
use App\Service\Data\Skills as SkillsData;
use Tests\Support\ControllerTestCase;
use Tests\Support\RedirectedResponse;
use Tests\Support\RenderedResponse;

class SkillsTest extends ControllerTestCase
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

    public function testGetLoadsPersistedSkillsAndCatalogData(): void
    {
        $character = new Entity(['hash' => 'charhash', 'name' => 'Mara']);
        $skillFactory = $this->getMockBuilder(Skill::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['forCharacter'])
            ->getMock();
        $skillFactory->expects(self::once())
            ->method('forCharacter')
            ->with($character)
            ->willReturn([new Entity(['key' => 'athletics', 'die' => 6])]);

        $this->mapRequest('GET');
        $this->mapRenderToException();

        try {
            $this->controller($character, $skillFactory)->index('charhash');
            self::fail('Expected render');
        } catch (RenderedResponse $rendered) {
            self::assertSame('character/skills.twig', $rendered->template);
            self::assertSame(['athletics' => 6], $rendered->data['selected']);
            self::assertArrayHasKey('athletics', $rendered->data['skills']);
            self::assertSame([0, 4, 6, 8, 10, 12], $rendered->data['dice_options']);
        }
    }

    public function testPostSyncsSkillsUpdatesCharacterAndRedirects(): void
    {
        $_POST = ['skills' => ['athletics' => '6', 'fighting' => '0']];
        $character = new Entity(['hash' => 'charhash', 'name' => 'Mara']);
        $skillService = new SkillsData(__DIR__ . '/../../../data');

        $skillFactory = $this->getMockBuilder(Skill::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['syncForCharacter'])
            ->getMock();
        $skillFactory->expects(self::once())
            ->method('syncForCharacter')
            ->with($character, ['athletics' => 6], $skillService)
            ->willReturn(new Result());

        $characterFactory = $this->characterFactory($character);
        $characterFactory->expects(self::once())
            ->method('update')
            ->with($character)
            ->willReturn(new Result());

        $session = $this->mapSession();
        $this->mapRequest('POST');
        $this->mapUrls(['characters_edges' => '/characters/edges/{hash}']);
        \Flight::map('redirect', function (string $url): void {
            throw new SkillsControllerRedirected($url);
        });

        try {
            (new Skills($characterFactory, $skillFactory, $this->manager($skillService)))->index('charhash');
            self::fail('Expected redirect');
        } catch (SkillsControllerRedirected $redirected) {
            self::assertSame('/characters/edges/charhash', $redirected->url);
        }

        self::assertSame(['Saved character Mara successfully'], $session->successes);
    }

    public function testPostFlashesSkillSyncFailureAndRendersForm(): void
    {
        $_POST = ['skills' => ['athletics' => '6']];
        $character = new Entity(['hash' => 'charhash', 'name' => 'Mara']);

        $skillFactory = $this->getMockBuilder(Skill::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['syncForCharacter'])
            ->getMock();
        $skillFactory->expects(self::once())
            ->method('syncForCharacter')
            ->willReturn(new Result(['database failed']));

        $characterFactory = $this->characterFactory($character);
        $characterFactory->expects(self::never())
            ->method('update');

        $session = $this->mapSession();
        $this->mapRequest('POST');
        $this->mapRenderToException();

        try {
            (new Skills($characterFactory, $skillFactory, $this->manager(new SkillsData(__DIR__ . '/../../../data'))))->index('charhash');
            self::fail('Expected render');
        } catch (RenderedResponse $rendered) {
            self::assertSame('character/skills.twig', $rendered->template);
        }

        self::assertSame(['Unable to save character skills'], $session->errors);
    }

    public function testPostFlashesCharacterUpdateFailureAndRendersForm(): void
    {
        $_POST = ['skills' => ['athletics' => '6']];
        $character = new Entity(['hash' => 'charhash', 'name' => 'Mara']);

        $skillFactory = $this->getMockBuilder(Skill::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['syncForCharacter'])
            ->getMock();
        $skillFactory->expects(self::once())
            ->method('syncForCharacter')
            ->willReturn(new Result());

        $characterFactory = $this->characterFactory($character);
        $characterFactory->expects(self::once())
            ->method('update')
            ->with($character)
            ->willReturn(new Result(['database failed']));

        $session = $this->mapSession();
        $this->mapRequest('POST');
        $this->mapRenderToException();

        try {
            (new Skills($characterFactory, $skillFactory, $this->manager(new SkillsData(__DIR__ . '/../../../data'))))->index('charhash');
            self::fail('Expected render');
        } catch (RenderedResponse $rendered) {
            self::assertSame('character/skills.twig', $rendered->template);
        }

        self::assertSame(['Unable to update character'], $session->errors);
    }

    private function controller(?Entity $character, ?Skill $skillFactory = null): Skills
    {
        return new Skills(
            $this->characterFactory($character),
            $skillFactory ?? $this->createStub(Skill::class),
            $this->manager(new SkillsData(__DIR__ . '/../../../data')),
        );
    }

    private function characterFactory(?Entity $character): Character
    {
        $factory = $this->getMockBuilder(Character::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['forHash', 'update'])
            ->getMock();
        $factory->expects(self::once())
            ->method('forHash')
            ->willReturn($character);

        return $factory;
    }

    private function manager(SkillsData $skills): Manager
    {
        $manager = $this->createStub(Manager::class);
        $manager->method('getType')
            ->willReturn($skills);

        return $manager;
    }
}

class SkillsControllerRedirected extends \Error
{
    public function __construct(public string $url)
    {
        parent::__construct($url);
    }
}
