<?php

declare(strict_types=1);

namespace Tests\Controller\Character;

use App\Controller\Character\Archetypes;
use App\Entity;
use App\Service\Archetype\Applier;
use App\Service\Data\Archetypes as ArchetypesData;
use App\Service\Data\Manager;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\Support\ControllerTestCase;
use Tests\Support\RedirectedResponse;
use Tests\Support\RenderedResponse;

class ArchetypesTest extends ControllerTestCase
{
    public function testIndexRendersArchetypeGrid(): void
    {
        $archetypes = [
            ['id' => 'barbarian', 'name' => 'Barbarian', 'summary' => 'Fierce.'],
        ];

        $this->mapRenderToException();

        try {
            $this->controller($archetypes)->index();
            self::fail('Expected render');
        } catch (RenderedResponse $rendered) {
            self::assertSame('character/archetypes.twig', $rendered->template);
            self::assertSame('Choose an Archetype', $rendered->data['page_title']);
            self::assertSame($archetypes, $rendered->data['archetypes']);
        }
    }

    public function testCreatePostWithValidArchetypeRedirectsToSettings(): void
    {
        $_POST = ['archetype' => 'barbarian'];

        $entity = new Entity(['id' => 5, 'hash' => 'abc123hash456789012345678901234']);

        $applier = $this->getMockBuilder(Applier::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['applyToNewCharacter'])
            ->getMock();
        $applier->expects(self::once())
            ->method('applyToNewCharacter')
            ->with(self::callback(fn (array $a): bool => $a['id'] === 'barbarian'))
            ->willReturn($entity);

        $this->mapRedirectToException();
        $this->mapUrls(['characters_settings' => '/characters/settings/{hash}']);

        try {
            $this->controller(
                [['id' => 'barbarian', 'name' => 'Barbarian']],
                $applier,
            )->create();
            self::fail('Expected redirect');
        } catch (RedirectedResponse $redirected) {
            self::assertSame('/characters/settings/abc123hash456789012345678901234', $redirected->url);
        }
    }

    public function testCreatePostWithUnknownArchetypeFlashesErrorAndRedirects(): void
    {
        $_POST = ['archetype' => 'wizard'];

        $session = $this->mapSession();
        $this->mapRedirectToException();
        $this->mapUrls(['characters_new' => '/characters/new']);

        try {
            $this->controller()->create();
            self::fail('Expected redirect');
        } catch (RedirectedResponse $redirected) {
            self::assertSame('/characters/new', $redirected->url);
        }

        self::assertSame(['Unknown archetype selected'], $session->errors);
    }

    private function controller(
        array $archetypes = [],
        ?Applier $applier = null,
    ): Archetypes {
        $catalog = $this->getMockBuilder(ArchetypesData::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['all', 'forId'])
            ->getMock();
        $catalog->method('all')->willReturn($archetypes);
        $catalog->method('forId')
            ->willReturnCallback(function (string $id) use ($archetypes): ?array {
                foreach ($archetypes as $a) {
                    if ($a['id'] === $id) {
                        return $a;
                    }
                }

                return null;
            });

        $manager = $this->getMockBuilder(Manager::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getType'])
            ->getMock();
        $manager->method('getType')->willReturn($catalog);

        return new Archetypes(
            $manager,
            $applier ?? $this->createStub(Applier::class),
        );
    }
}
