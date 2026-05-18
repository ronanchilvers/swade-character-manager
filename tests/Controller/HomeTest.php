<?php

declare(strict_types=1);

namespace Tests\Controller;

use App\Controller\Home;
use App\Entity;
use App\Entity\Factory\Campaign;
use App\Entity\Factory\Character;
use Flight;
use Tests\Support\ControllerTestCase;
use Tests\Support\RenderedResponse;

class HomeTest extends ControllerTestCase
{
    public function testIndexRendersCurrentUsersCharacters(): void
    {
        $characters = [
            new Entity(['name' => 'Mara', 'campaign' => 12]),
            new Entity(['name' => 'Silas', 'campaign' => 12]),
            new Entity(['name' => 'Tess', 'campaign' => 34]),
        ];
        $campaigns = [12 => 'The Flood', 34 => 'Deadlands'];
        $factory = $this->getMockBuilder(Character::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['forUser'])
            ->getMock();
        $factory->expects(self::once())
            ->method('forUser')
            ->with(7)
            ->willReturn($characters);

        $campaignFactory = $this->getMockBuilder(Campaign::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['namesForIds'])
            ->getMock();
        $campaignFactory->expects(self::once())
            ->method('namesForIds')
            ->with([12 => 12, 34 => 34])
            ->willReturn($campaigns);

        Flight::map('user', fn (): object => (object) ['id' => 7]);
        $this->mapRenderToException();

        try {
            (new Home($factory, $campaignFactory))->index();
            self::fail('Expected render');
        } catch (RenderedResponse $rendered) {
            self::assertSame('home/index.twig', $rendered->template);
            self::assertSame('Characters', $rendered->data['page_title']);
            self::assertSame($characters, $rendered->data['characters']);
            self::assertSame($campaigns, $rendered->data['campaigns']);
        }
    }
}
