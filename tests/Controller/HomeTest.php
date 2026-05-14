<?php

declare(strict_types=1);

namespace Tests\Controller;

use App\Controller\Home;
use App\Entity;
use App\Entity\Factory\Character;
use Tests\Support\ControllerTestCase;
use Tests\Support\RenderedResponse;

class HomeTest extends ControllerTestCase
{
    public function testIndexRendersCurrentUsersCharacters(): void
    {
        $characters = [new Entity(['name' => 'Mara'])];
        $factory = $this->getMockBuilder(Character::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['forUser'])
            ->getMock();
        $factory->expects(self::once())
            ->method('forUser')
            ->with(7)
            ->willReturn($characters);

        $session = $this->mapSession();
        $session->user = (object) ['id' => 7];
        $this->mapRenderToException();

        try {
            (new Home($factory))->index();
            self::fail('Expected render');
        } catch (RenderedResponse $rendered) {
            self::assertSame('home/index.twig', $rendered->template);
            self::assertSame('Characters', $rendered->data['page_title']);
            self::assertSame($characters, $rendered->data['characters']);
        }
    }
}
