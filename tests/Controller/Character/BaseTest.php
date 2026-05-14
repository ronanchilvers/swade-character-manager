<?php

declare(strict_types=1);

namespace Tests\Controller\Character;

use App\Controller\Character\Base;
use App\Entity;
use App\Entity\Factory\Character;
use App\Entity\Factory\Result;
use Tests\Support\ControllerTestCase;
use Tests\Support\RedirectedResponse;
use Tests\Support\RenderedResponse;

class BaseTest extends ControllerTestCase
{
    public function testCreateGetRendersEmptyConceptForm(): void
    {
        $this->mapRequest('GET');
        $this->mapRenderToException();

        try {
            (new Base($this->createStub(Character::class)))->create();
            self::fail('Expected render');
        } catch (RenderedResponse $rendered) {
            self::assertSame('character/concept.twig', $rendered->template);
            self::assertSame('The Basics', $rendered->data['page_title']);
            self::assertInstanceOf(Entity::class, $rendered->data['entity']);
            self::assertSame([], $rendered->data['errors']);
        }
    }

    public function testCreatePostRedirectsOnSuccess(): void
    {
        $_POST = [
            'name' => '<b>Mara</b>',
            'concept' => '<i>Scout</i>',
        ];

        $factory = $this->getMockBuilder(Character::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['validate', 'upsert'])
            ->getMock();
        $factory->expects(self::once())
            ->method('validate')
            ->with(self::callback(fn (Entity $entity): bool => 'Mara' === $entity->name && 'Scout' === $entity->concept))
            ->willReturn([]);
        $factory->expects(self::once())
            ->method('upsert')
            ->with(self::callback(function (Entity $entity): bool {
                $entity->hash = 'charhash';

                return true;
            }))
            ->willReturn(new Result());

        $session = $this->mapSession();
        $this->mapRequest('POST');
        $this->mapRedirectToException();
        $this->mapUrls(['characters_hindrances' => '/characters/hindrances/{hash}']);

        try {
            (new Base($factory))->create();
            self::fail('Expected redirect');
        } catch (RedirectedResponse $redirected) {
            self::assertSame('/characters/hindrances/charhash', $redirected->url);
        }

        self::assertSame(['Saved character Mara successfully'], $session->successes);
    }

    public function testCreatePostRendersValidationErrors(): void
    {
        $_POST = ['name' => '', 'concept' => ''];

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
        $this->mapRequest('POST');
        $this->mapRenderToException();

        try {
            (new Base($factory))->create();
            self::fail('Expected render');
        } catch (RenderedResponse $rendered) {
            self::assertSame(['name'], $rendered->data['errors']);
        }

        self::assertSame(['Sorry! There was a problem!'], $session->errors);
    }

    public function testIndexRedirectsWhenCharacterIsMissing(): void
    {
        $factory = $this->getMockBuilder(Character::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['forHash'])
            ->getMock();
        $factory->expects(self::once())
            ->method('forHash')
            ->with('missing')
            ->willReturn(null);

        $session = $this->mapSession();
        $this->mapRedirectToException();
        $this->mapUrls(['home_page' => '/']);

        try {
            (new Base($factory))->index('missing');
            self::fail('Expected redirect');
        } catch (RedirectedResponse $redirected) {
            self::assertSame('/', $redirected->url);
        }

        self::assertSame(['Unable to find character'], $session->errors);
    }

    public function testIndexGetRendersExistingCharacter(): void
    {
        $character = new Entity(['id' => 3, 'hash' => 'charhash', 'name' => 'Mara']);
        $factory = $this->getMockBuilder(Character::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['forHash'])
            ->getMock();
        $factory->expects(self::once())
            ->method('forHash')
            ->with('charhash')
            ->willReturn($character);

        $this->mapRequest('GET');
        $this->mapRenderToException();

        try {
            (new Base($factory))->index('charhash');
            self::fail('Expected render');
        } catch (RenderedResponse $rendered) {
            self::assertSame($character, $rendered->data['entity']);
        }
    }

    public function testDeleteRedirectsWhenCharacterIsMissing(): void
    {
        $factory = $this->getMockBuilder(Character::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['forUserHash', 'delete'])
            ->getMock();
        $factory->expects(self::once())
            ->method('forUserHash')
            ->with(7, 'missing')
            ->willReturn(null);
        $factory->expects(self::never())
            ->method('delete');

        $session = $this->mapSession();
        $session->user = (object) ['id' => 7];
        $this->mapRedirectToException();
        $this->mapUrls(['home_page' => '/']);

        try {
            (new Base($factory))->delete('missing');
            self::fail('Expected redirect');
        } catch (RedirectedResponse $redirected) {
            self::assertSame('/', $redirected->url);
        }

        self::assertSame(['Unable to find character'], $session->errors);
    }

    public function testDeleteRejectsConfirmationMismatch(): void
    {
        $_POST = ['confirm_name' => 'Wrong'];
        $factory = $this->getMockBuilder(Character::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['forUserHash', 'delete'])
            ->getMock();
        $factory->expects(self::once())
            ->method('forUserHash')
            ->with(7, 'charhash')
            ->willReturn(new Entity(['id' => 3, 'name' => 'Mara']));
        $factory->expects(self::never())
            ->method('delete');

        $session = $this->mapSession();
        $session->user = (object) ['id' => 7];
        $this->mapRedirectToException();
        $this->mapUrls(['home_page' => '/']);

        try {
            (new Base($factory))->delete('charhash');
            self::fail('Expected redirect');
        } catch (RedirectedResponse $redirected) {
            self::assertSame('/', $redirected->url);
        }

        self::assertSame(['Type the character name to confirm deletion'], $session->errors);
    }

    public function testDeleteRejectsCampaignCharacters(): void
    {
        $_POST = ['confirm_name' => 'Mara'];
        $factory = $this->getMockBuilder(Character::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['forUserHash', 'delete'])
            ->getMock();
        $factory->method('forUserHash')
            ->willReturn(new Entity(['id' => 3, 'name' => 'Mara', 'campaign' => 4]));
        $factory->expects(self::never())
            ->method('delete');

        $session = $this->mapSession();
        $session->user = (object) ['id' => 7];
        $this->mapRedirectToException();
        $this->mapUrls(['home_page' => '/']);

        try {
            (new Base($factory))->delete('charhash');
            self::fail('Expected redirect');
        } catch (RedirectedResponse) {
        }

        self::assertSame(['This character must leave the campaign before deletion'], $session->errors);
    }

    public function testDeleteFlashesSuccess(): void
    {
        $_POST = ['confirm_name' => 'Mara'];
        $character = new Entity(['id' => 3, 'name' => 'Mara']);
        $factory = $this->getMockBuilder(Character::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['forUserHash', 'delete'])
            ->getMock();
        $factory->method('forUserHash')
            ->willReturn($character);
        $factory->expects(self::once())
            ->method('delete')
            ->with($character)
            ->willReturn(new Result());

        $session = $this->mapSession();
        $session->user = (object) ['id' => 7];
        $this->mapRedirectToException();
        $this->mapUrls(['home_page' => '/']);

        try {
            (new Base($factory))->delete('charhash');
            self::fail('Expected redirect');
        } catch (RedirectedResponse) {
        }

        self::assertSame(['Deleted character Mara successfully'], $session->successes);
    }

    public function testDeleteFlashesFailure(): void
    {
        $_POST = ['confirm_name' => 'Mara'];
        $character = new Entity(['id' => 3, 'name' => 'Mara']);
        $factory = $this->getMockBuilder(Character::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['forUserHash', 'delete'])
            ->getMock();
        $factory->expects(self::once())
            ->method('forUserHash')
            ->with(7, 'charhash')
            ->willReturn($character);
        $factory->expects(self::once())
            ->method('delete')
            ->with($character)
            ->willReturn(new Result(['delete failed']));

        $session = $this->mapSession();
        $session->user = (object) ['id' => 7];
        $this->mapRedirectToException();
        $this->mapUrls(['home_page' => '/']);

        try {
            (new Base($factory))->delete('charhash');
            self::fail('Expected redirect');
        } catch (RedirectedResponse $redirected) {
            self::assertSame('/', $redirected->url);
        }

        self::assertSame(['Sorry! There was a problem deleting that character'], $session->errors);
    }
}
