<?php

declare(strict_types=1);

namespace Tests\Controller;

use App\Controller\Admin\Campaigns;
use App\Entity;
use App\Entity\Factory\Campaign;
use App\Entity\Factory\Campaign\Member;
use App\Entity\Factory\Character;
use App\Entity\Factory\User;
use Tests\Support\ControllerTestCase;
use Tests\Support\RedirectedResponse;
use Tests\Support\RenderedResponse;

class AdminCampaignsTest extends ControllerTestCase
{
    public function testIndexRendersCampaignSummaries(): void
    {
        $campaigns = [new Entity(['name' => 'The Flood'])];
        $campaignFactory = $this->getMockBuilder(Campaign::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['allWithSummary'])
            ->getMock();
        $campaignFactory->expects(self::once())
            ->method('allWithSummary')
            ->willReturn($campaigns);

        $this->mapRenderToException();

        try {
            $this->controller($campaignFactory)->index();
            self::fail('Expected render');
        } catch (RenderedResponse $rendered) {
            self::assertSame('admin/campaigns/index.twig', $rendered->template);
            self::assertSame('Manage Campaigns', $rendered->data['page_title']);
            self::assertSame($campaigns, $rendered->data['campaigns']);
        }
    }

    public function testViewRedirectsWhenCampaignIsMissing(): void
    {
        $campaignFactory = $this->getMockBuilder(Campaign::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['forHash'])
            ->getMock();
        $campaignFactory->expects(self::once())
            ->method('forHash')
            ->with('missing')
            ->willReturn(null);

        $session = $this->mapSession();
        $this->mapRedirectToException();
        $this->mapUrls(['admin_campaigns_index' => '/admin/campaigns']);

        try {
            $this->controller($campaignFactory)->view('missing');
            self::fail('Expected redirect');
        } catch (RedirectedResponse $redirected) {
            self::assertSame('/admin/campaigns', $redirected->url);
        }

        self::assertSame(['Unable to find campaign'], $session->errors);
    }

    public function testViewRendersCampaignOwnerAndRoster(): void
    {
        $campaign = new Entity(['id' => 3, 'user' => 7, 'name' => 'The Flood']);
        $member = new Entity(['user_id' => 8, 'role' => 'member']);
        $owner = new Entity(['id' => 7, 'email' => 'owner@example.com']);
        $memberUser = new Entity(['id' => 8, 'email' => 'player@example.com']);
        $characters = [new Entity(['name' => 'Mara'])];

        $campaignFactory = $this->getMockBuilder(Campaign::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['forHash'])
            ->getMock();
        $campaignFactory->expects(self::once())
            ->method('forHash')
            ->with('abc')
            ->willReturn($campaign);

        $memberFactory = $this->getMockBuilder(Member::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['forCampaign'])
            ->getMock();
        $memberFactory->expects(self::once())
            ->method('forCampaign')
            ->with($campaign)
            ->willReturn([$member, new Entity(['user_id' => 99])]);

        $userFactory = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['byId'])
            ->getMock();
        $userFactory->expects(self::exactly(3))
            ->method('byId')
            ->willReturnMap([
                [7, $owner],
                [8, $memberUser],
                [99, null],
            ]);

        $characterFactory = $this->getMockBuilder(Character::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['forCampaignAndUser'])
            ->getMock();
        $characterFactory->expects(self::once())
            ->method('forCampaignAndUser')
            ->with($campaign, 8)
            ->willReturn($characters);

        $this->mapRenderToException();

        try {
            $this->controller($campaignFactory, $memberFactory, $characterFactory, $userFactory)->view('abc');
            self::fail('Expected render');
        } catch (RenderedResponse $rendered) {
            self::assertSame('admin/campaigns/view.twig', $rendered->template);
            self::assertSame('The Flood', $rendered->data['page_title']);
            self::assertSame($campaign, $rendered->data['campaign']);
            self::assertSame($owner, $rendered->data['owner']);
            self::assertSame($memberUser, $rendered->data['roster'][0]['user']);
            self::assertSame($characters, $rendered->data['roster'][0]['characters']);
            self::assertNull($rendered->data['roster'][1]['user']);
            self::assertSame([], $rendered->data['roster'][1]['characters']);
        }
    }

    private function controller(
        ?Campaign $campaignFactory = null,
        ?Member $memberFactory = null,
        ?Character $characterFactory = null,
        ?User $userFactory = null,
    ): Campaigns {
        return new Campaigns(
            $campaignFactory ?? $this->createStub(Campaign::class),
            $memberFactory ?? $this->createStub(Member::class),
            $characterFactory ?? $this->createStub(Character::class),
            $userFactory ?? $this->createStub(User::class),
        );
    }
}
