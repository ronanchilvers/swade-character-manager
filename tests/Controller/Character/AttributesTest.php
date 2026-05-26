<?php

declare(strict_types=1);

namespace Tests\Controller\Character;

use App\Controller\Character\Attributes;
use App\Entity;
use App\Entity\Factory\Character;
use App\Entity\Factory\Result;
use Tests\Support\ControllerTestCase;
use Tests\Support\RedirectedResponse;
use Tests\Support\RenderedResponse;

class AttributesTest extends ControllerTestCase
{
    public function testMissingCharacterRedirectsHome(): void
    {
        $factory = $this->factoryForHash(null);
        $session = $this->mapSession();
        $this->mapRedirectToException();
        $this->mapUrls(['home_page' => '/']);

        try {
            (new Attributes($factory))->index('missing');
            self::fail('Expected redirect');
        } catch (RedirectedResponse $redirected) {
            self::assertSame('/', $redirected->url);
        }

        self::assertSame(['Unable to find character'], $session->errors);
    }

    public function testGetRendersAttributeOptionsAndFields(): void
    {
        $character = new Entity(['hash' => 'charhash', 'name' => 'Mara']);
        $factory = $this->getMockBuilder(Character::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['forHash', 'attributeFields'])
            ->getMock();
        $factory->method('forHash')
            ->willReturn($character);
        $factory->expects(self::once())
            ->method('attributeFields')
            ->willReturn(['agility' => ['name' => 'Agility']]);

        $this->mapRequest('GET');
        $this->mapRenderToException();

        try {
            (new Attributes($factory))->index('charhash');
            self::fail('Expected render');
        } catch (RenderedResponse $rendered) {
            self::assertSame('character/attributes.twig', $rendered->template);
            self::assertSame([4, 6, 8, 10, 12], $rendered->data['attribute_options']);
            self::assertSame(['agility' => ['name' => 'Agility']], $rendered->data['attribute_fields']);
        }
    }

    public function testPostUpdatesAttributesAndRedirects(): void
    {
        $_POST = ['attributes' => ['agility' => '8', 'smarts' => '99']];
        $character = new Entity(['hash' => 'charhash', 'name' => 'Mara']);
        $factory = $this->getMockBuilder(Character::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['forHash', 'update'])
            ->getMock();
        $factory->method('forHash')
            ->willReturn($character);
        $factory->expects(self::once())
            ->method('update')
            ->with(self::callback(fn (Entity $entity): bool => 8 === $entity->agility && !isset($entity->smarts)))
            ->willReturn(new Result());

        $session = $this->mapSession();
        $this->mapRequest('POST', url: '/characters/attributes/charhash');
        \Flight::map('reload', function (): void {
            throw new RedirectedResponse(\Flight::request()->url);
        });

        try {
            (new Attributes($factory))->index('charhash');
            self::fail('Expected redirect');
        } catch (RedirectedResponse $redirected) {
            self::assertSame('/characters/attributes/charhash', $redirected->url);
        }

        self::assertSame(['Saved character Mara successfully'], $session->successes);
    }

    public function testPostRendersUpdateErrors(): void
    {
        $_POST = ['attributes' => ['agility' => '8']];
        $character = new Entity(['hash' => 'charhash', 'name' => 'Mara']);
        $factory = $this->getMockBuilder(Character::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['forHash', 'update', 'attributeFields'])
            ->getMock();
        $factory->expects(self::once())
            ->method('forHash')
            ->willReturn($character);
        $factory->expects(self::once())
            ->method('update')
            ->willReturn(new Result(['database failed']));
        $factory->expects(self::once())
            ->method('attributeFields')
            ->willReturn([]);

        $session = $this->mapSession();
        $this->mapRequest('POST');
        $this->mapRenderToException();

        try {
            (new Attributes($factory))->index('charhash');
            self::fail('Expected render');
        } catch (RenderedResponse $rendered) {
            self::assertSame(['database failed'], $rendered->data['errors']);
        }

        self::assertSame(['Sorry! There was a problem!'], $session->errors);
    }

    private function factoryForHash(?Entity $entity): Character
    {
        $factory = $this->getMockBuilder(Character::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['forHash'])
            ->getMock();
        $factory->expects(self::once())
            ->method('forHash')
            ->willReturn($entity);

        return $factory;
    }
}
