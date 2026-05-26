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
use App\Service\Sources;
use flight\database\SimplePdo;
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
        $this->mapRequest('POST', url: '/characters/skills/charhash');
        \Flight::map('reload', function (): void {
            throw new SkillsControllerRedirected(\Flight::request()->url);
        });

        try {
            (new Skills($characterFactory, $skillFactory, $this->manager($skillService), new Sources()))->index('charhash');
            self::fail('Expected redirect');
        } catch (SkillsControllerRedirected $redirected) {
            self::assertSame('/characters/skills/charhash', $redirected->url);
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
            (new Skills(
                $characterFactory,
                $skillFactory,
                $this->manager(new SkillsData(__DIR__ . '/../../../data')),
                new Sources(),
            ))->index('charhash');
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
            (new Skills(
                $characterFactory,
                $skillFactory,
                $this->manager(new SkillsData(__DIR__ . '/../../../data')),
                new Sources(),
            ))->index('charhash');
            self::fail('Expected render');
        } catch (RenderedResponse $rendered) {
            self::assertSame('character/skills.twig', $rendered->template);
        }

        self::assertSame(['Unable to update character'], $session->errors);
    }

    public function testGetFiltersCatalogByCharacterSources(): void
    {
        $character = new Entity(['hash' => 'charhash', 'name' => 'Mara', 'sources' => 'core', 'agility' => 4, 'spirit' => 4]);
        $pdo = $this->createMock(SimplePdo::class);
        $pdo->expects(self::once())
            ->method('fetchAll')
            ->willReturn([
                $this->catalogRow('athletics', 'core', 'Athletics', 'agility', true),
                $this->catalogRow('fantasy_magic', 'fantasy', 'Fantasy Magic', 'spirit', false),
            ]);

        $this->mapRequest('GET');
        $this->mapRenderToException();

        try {
            (new Skills(
                $this->characterFactory($character),
                $this->createStub(Skill::class),
                $this->manager(new SkillsData(__DIR__ . '/../../../data', $pdo)),
                new Sources(),
            ))->index('charhash');
            self::fail('Expected render');
        } catch (RenderedResponse $rendered) {
            self::assertSame(['athletics'], array_keys($rendered->data['skills']));
        }
    }

    private function controller(?Entity $character, ?Skill $skillFactory = null): Skills
    {
        return new Skills(
            $this->characterFactory($character),
            $skillFactory ?? $this->createStub(Skill::class),
            $this->manager(new SkillsData(__DIR__ . '/../../../data')),
            new Sources(),
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

    private function catalogRow(
        string $key,
        string $source,
        string $name,
        string $attribute,
        bool $core,
    ): array {
        return [
            'skill_catalog_key' => $key,
            'skill_catalog_source' => $source,
            'skill_catalog_name' => $name,
            'skill_catalog_linked_attribute' => $attribute,
            'skill_catalog_core_skill' => $core ? '1' : '0',
            'skill_catalog_arcane_background' => null,
            'skill_catalog_summary' => '',
            'skill_catalog_requirements' => '[]',
            'skill_catalog_effects' => '[]',
            'skill_catalog_notes' => '[]',
            'skill_catalog_source_pages' => '[]',
        ];
    }
}

class SkillsControllerRedirected extends \Error
{
    public function __construct(public string $url)
    {
        parent::__construct($url);
    }
}
