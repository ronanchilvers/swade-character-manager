<?php

declare(strict_types=1);

namespace Tests\Controller\Character;

use App\Controller\Character\Settings;
use App\Entity;
use App\Entity\Factory\Character;
use App\Entity\Factory\Result;
use App\Service\Sources;
use flight\database\SimplePdo;
use Tests\Support\ControllerTestCase;
use Tests\Support\RedirectedResponse;
use Tests\Support\RenderedResponse;

class SettingsTest extends ControllerTestCase
{
    public function testGetRendersDatabaseBackedSourcesAndSelectedSources(): void
    {
        $character = new Entity(['id' => 3, 'hash' => 'charhash', 'name' => 'Mara', 'sources' => 'core,fantasy']);
        $factory = $this->characterFactory($character);
        $pdo = $this->sourcePdo();

        $this->mapRequest('GET');
        $this->mapRenderToException();

        try {
            (new Settings($factory, new Sources($pdo)))->index('charhash');
            self::fail('Expected render');
        } catch (RenderedResponse $rendered) {
            self::assertSame('character/settings.twig', $rendered->template);
            self::assertSame('Settings', $rendered->data['page_title']);
            self::assertSame($character, $rendered->data['entity']);
            self::assertSame(['core', 'fantasy'], $rendered->data['selected_sources']);
            self::assertSame('Fantasy Companion', $rendered->data['sources']['fantasy']['name']);
        }
    }

    public function testCreateGetRendersEmptySettingsForm(): void
    {
        $this->mapRequest('GET');
        $this->mapRenderToException();

        try {
            (new Settings($this->createStub(Character::class), new Sources($this->sourcePdo())))->create();
            self::fail('Expected render');
        } catch (RenderedResponse $rendered) {
            self::assertSame('character/settings.twig', $rendered->template);
            self::assertSame('Settings', $rendered->data['page_title']);
            self::assertInstanceOf(Entity::class, $rendered->data['entity']);
            self::assertSame([], $rendered->data['errors']);
        }
    }

    public function testPostFiltersSourcesAgainstConfiguredRowsAndUpdatesCharacter(): void
    {
        $_POST = [
            'name' => '<b>Mara Jade</b>',
            'sources' => [
                'fantasy' => 'on',
                'unknown' => 'on',
            ],
            'sharing' => 'on',
        ];
        $character = new Entity(['id' => 3, 'hash' => 'charhash', 'name' => 'Mara']);
        $factory = $this->getMockBuilder(Character::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['forHash', 'validate', 'upsert'])
            ->getMock();
        $factory->expects(self::once())
            ->method('forHash')
            ->with('charhash')
            ->willReturn($character);
        $factory->expects(self::once())
            ->method('validate')
            ->with(self::callback(fn (Entity $entity): bool => 'Mara Jade' === $entity->name
                && 'core,fantasy' === $entity->sources
                && 1 === $entity->sharing))
            ->willReturn([]);
        $factory->expects(self::once())
            ->method('upsert')
            ->with(self::callback(fn (Entity $entity): bool => 'Mara Jade' === $entity->name
                && 'core,fantasy' === $entity->sources
                && 1 === $entity->sharing))
            ->willReturn(new Result());

        $session = $this->mapSession();
        $this->mapRequest('POST', url: '/characters/settings/charhash');
        \Flight::map('reload', function (): void {
            throw new RedirectedResponse('/characters/settings/charhash');
        });

        try {
            (new Settings($factory, new Sources($this->sourcePdo())))->index('charhash');
            self::fail('Expected reload');
        } catch (RedirectedResponse $redirected) {
            self::assertSame('/characters/settings/charhash', $redirected->url);
        }

        self::assertSame(['Saved character Mara Jade successfully'], $session->successes);
    }

    public function testCreatePostRedirectsToSettingsAfterInsert(): void
    {
        $_POST = [
            'name' => '<b>Mara</b>',
            'sources' => [
                'fantasy' => 'on',
            ],
        ];

        $factory = $this->getMockBuilder(Character::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['validate', 'upsert'])
            ->getMock();
        $factory->expects(self::once())
            ->method('validate')
            ->with(self::callback(fn (Entity $entity): bool => 'Mara' === $entity->name
                && 'core,fantasy' === $entity->sources
                && 0 === $entity->sharing))
            ->willReturn([]);
        $factory->expects(self::once())
            ->method('upsert')
            ->with(self::callback(function (Entity $entity): bool {
                $entity->hash = 'charhash';

                return true;
            }))
            ->willReturn(new Result());

        $session = $this->mapSession();
        $this->mapRequest('POST', url: '/characters/create');
        $this->mapRedirectToException();
        $this->mapUrls(['characters_settings' => '/characters/settings/{hash}']);

        try {
            (new Settings($factory, new Sources($this->sourcePdo())))->create();
            self::fail('Expected redirect');
        } catch (RedirectedResponse $redirected) {
            self::assertSame('/characters/settings/charhash', $redirected->url);
        }

        self::assertSame(['Saved character Mara successfully'], $session->successes);
    }

    public function testCreatePostRendersValidationErrors(): void
    {
        $_POST = ['name' => '', 'sources' => []];

        $factory = $this->getMockBuilder(Character::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['validate', 'upsert'])
            ->getMock();
        $factory->expects(self::once())
            ->method('validate')
            ->willReturn(['name']);
        $factory->expects(self::never())
            ->method('upsert');

        $session = $this->mapSession();
        $this->mapRequest('POST', url: '/characters/create');
        $this->mapRenderToException();

        try {
            (new Settings($factory, new Sources($this->sourcePdo())))->create();
            self::fail('Expected render');
        } catch (RenderedResponse $rendered) {
            self::assertSame('character/settings.twig', $rendered->template);
            self::assertSame(['name'], $rendered->data['errors']);
        }

        self::assertSame(['Sorry! There was a problem!'], $session->errors);
    }

    private function characterFactory(?Entity $character): Character
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

    private function sourcePdo(): SimplePdo
    {
        $pdo = $this->createMock(SimplePdo::class);
        $pdo->expects(self::once())
            ->method('fetchAll')
            ->willReturn([
                $this->sourceRow('core', 'Core Rules', '1', 0),
                $this->sourceRow('fantasy', 'Fantasy Companion', '0', 10),
            ]);

        return $pdo;
    }

    private function sourceRow(string $key, string $name, string $alwaysEnabled, int $position): array
    {
        return [
            'catalog_source_key' => $key,
            'catalog_source_name' => $name,
            'catalog_source_always_enabled' => $alwaysEnabled,
            'catalog_source_position' => $position,
        ];
    }
}
