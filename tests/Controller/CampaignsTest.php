<?php

declare(strict_types=1);

namespace Tests\Controller;

use App\Controller\Campaigns;
use App\Entity;
use App\Entity\Factory\Campaign as CampaignFactory;
use App\Entity\Factory\Campaign\Member;
use App\Entity\Factory\Character;
use App\Entity\Factory\Result;
use App\Entity\Factory\User;
use Tests\Support\ControllerTestCase;
use Tests\Support\RedirectedResponse;
use Tests\Support\RenderedResponse;

class CampaignsTest extends ControllerTestCase
{
    public function testIndexRendersCurrentUsersCampaigns(): void
    {
        $campaigns = [new Entity(['name' => 'The Flood'])];
        $campaignFactory = $this->getMockBuilder(CampaignFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['forMemberUser'])
            ->getMock();
        $campaignFactory->expects(self::once())
            ->method('forMemberUser')
            ->with(7)
            ->willReturn($campaigns);

        $this->mapCurrentUser(7);
        $this->mapRenderToException();

        try {
            $this->controller($campaignFactory)->index();
            self::fail('Expected render');
        } catch (RenderedResponse $rendered) {
            self::assertSame('campaigns/index.twig', $rendered->template);
            self::assertSame('Campaigns', $rendered->data['page_title']);
            self::assertSame($campaigns, $rendered->data['campaigns']);
        }
    }

    public function testCreateGetRendersEmptyCampaign(): void
    {
        $this->mapRequest('GET');
        $this->mapRenderToException();

        try {
            $this->controller()->create();
            self::fail('Expected render');
        } catch (RenderedResponse $rendered) {
            self::assertSame('campaigns/create.twig', $rendered->template);
            self::assertSame('Create Campaign', $rendered->data['page_title']);
            self::assertInstanceOf(Entity::class, $rendered->data['entity']);
            self::assertSame([], $rendered->data['errors']);
        }
    }

    public function testCreatePostRedirectsOnSuccess(): void
    {
        $_POST = [
            'name' => '<b>The Flood</b>',
            'description' => '<script>bad</script>Watery',
        ];

        $campaignFactory = $this->getMockBuilder(CampaignFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['validate', 'insert'])
            ->getMock();
        $campaignFactory->expects(self::once())
            ->method('validate')
            ->with(self::callback(fn (Entity $campaign): bool => 'The Flood' === $campaign->name))
            ->willReturn([]);
        $campaignFactory->expects(self::once())
            ->method('insert')
            ->with(self::callback(function (Entity $campaign): bool {
                $campaign->hash = 'createdhash';

                return 'The Flood' === $campaign->name
                    && 'badWatery' === $campaign->description;
            }))
            ->willReturn(new Result());

        $session = $this->mapSession();
        $this->mapRequest('POST');
        $this->mapRedirectToException();
        $this->mapUrls(['campaigns_view' => '/campaigns/{hash}']);

        try {
            $this->controller($campaignFactory)->create();
            self::fail('Expected redirect');
        } catch (RedirectedResponse $redirected) {
            self::assertSame('/campaigns/createdhash', $redirected->url);
        }

        self::assertSame(['Created campaign The Flood'], $session->successes);
    }

    public function testCreatePostRendersValidationErrors(): void
    {
        $_POST = ['name' => '', 'description' => ''];

        $campaignFactory = $this->getMockBuilder(CampaignFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['validate', 'insert'])
            ->getMock();
        $campaignFactory->expects(self::once())
            ->method('validate')
            ->willReturn(['name']);
        $campaignFactory->expects(self::never())
            ->method('insert');

        $session = $this->mapSession();
        $this->mapRequest('POST');
        $this->mapRenderToException();

        try {
            $this->controller($campaignFactory)->create();
            self::fail('Expected render');
        } catch (RenderedResponse $rendered) {
            self::assertSame('campaigns/create.twig', $rendered->template);
            self::assertSame(['name'], $rendered->data['errors']);
        }

        self::assertSame(['Sorry! There was a problem creating that campaign'], $session->errors);
    }

    public function testCreatePostRendersInsertErrors(): void
    {
        $_POST = ['name' => 'The Flood', 'description' => ''];

        $campaignFactory = $this->getMockBuilder(CampaignFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['validate', 'insert'])
            ->getMock();
        $campaignFactory->method('validate')
            ->willReturn([]);
        $campaignFactory->expects(self::once())
            ->method('insert')
            ->willReturn(new Result(['database failed']));

        $session = $this->mapSession();
        $this->mapRequest('POST');
        $this->mapRenderToException();

        try {
            $this->controller($campaignFactory)->create();
            self::fail('Expected render');
        } catch (RenderedResponse $rendered) {
            self::assertSame(['database failed'], $rendered->data['errors']);
        }

        self::assertSame(['Sorry! There was a problem creating that campaign'], $session->errors);
    }

    public function testEditRedirectsWhenCampaignIsMissing(): void
    {
        $campaignFactory = $this->campaignLookup(null);
        $session = $this->mapSession();
        $this->mapRedirectToException();
        $this->mapUrls(['campaigns_index' => '/campaigns']);

        try {
            $this->controller($campaignFactory)->edit('missing');
            self::fail('Expected redirect');
        } catch (RedirectedResponse $redirected) {
            self::assertSame('/campaigns', $redirected->url);
        }

        self::assertSame(['Unable to find campaign'], $session->errors);
    }

    public function testEditRejectsUsersWhoCannotEdit(): void
    {
        $campaign = $this->campaign();

        $session = $this->mapCurrentUser(8);
        $this->mapRedirectToException();
        $this->mapUrls([
            'campaigns_index' => '/campaigns',
            'campaigns_view' => '/campaigns/{hash}',
        ]);

        try {
            $this->controller($this->campaignLookup($campaign))->edit($campaign->hash);
            self::fail('Expected redirect');
        } catch (RedirectedResponse $redirected) {
            self::assertSame('/campaigns/abc123', $redirected->url);
        }

        self::assertSame(['You do not have permission to edit that campaign'], $session->errors);
    }

    public function testEditGetRendersCampaignForOwner(): void
    {
        $campaign = $this->campaign();
        $this->mapCurrentUser(7);
        $this->mapRequest('GET');
        $this->mapRenderToException();

        try {
            $this->controller($this->campaignLookup($campaign))->edit($campaign->hash);
            self::fail('Expected render');
        } catch (RenderedResponse $rendered) {
            self::assertSame('campaigns/edit.twig', $rendered->template);
            self::assertSame('Edit The Flood', $rendered->data['page_title']);
            self::assertSame($campaign, $rendered->data['campaign']);
            self::assertSame([], $rendered->data['errors']);
        }
    }

    public function testEditPostRedirectsOnSuccess(): void
    {
        $_POST = [
            'name' => 'Updated',
            'description' => 'Better',
        ];

        $campaign = $this->campaign();
        $campaignFactory = $this->getMockBuilder(CampaignFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['forHash', 'validate', 'update'])
            ->getMock();
        $campaignFactory->method('forHash')
            ->willReturn($campaign);
        $campaignFactory->expects(self::once())
            ->method('validate')
            ->with(self::callback(fn (Entity $updated): bool => 'Updated' === $updated->name))
            ->willReturn([]);
        $campaignFactory->expects(self::once())
            ->method('update')
            ->willReturn(new Result());

        $session = $this->mapCurrentUser(7);
        $this->mapRequest('POST');
        $this->mapRedirectToException();
        $this->mapUrls(['campaigns_view' => '/campaigns/{hash}']);

        try {
            $this->controller($campaignFactory)->edit($campaign->hash);
            self::fail('Expected redirect');
        } catch (RedirectedResponse $redirected) {
            self::assertSame('/campaigns/abc123', $redirected->url);
        }

        self::assertSame(['Updated campaign Updated'], $session->successes);
    }

    public function testEditPostRendersUpdateErrors(): void
    {
        $_POST = ['name' => 'Updated', 'description' => 'Better'];
        $campaign = $this->campaign();
        $campaignFactory = $this->getMockBuilder(CampaignFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['forHash', 'validate', 'update'])
            ->getMock();
        $campaignFactory->method('forHash')
            ->willReturn($campaign);
        $campaignFactory->method('validate')
            ->willReturn([]);
        $campaignFactory->expects(self::once())
            ->method('update')
            ->willReturn(new Result(['database failed']));

        $session = $this->mapCurrentUser(7);
        $this->mapRequest('POST');
        $this->mapRenderToException();

        try {
            $this->controller($campaignFactory)->edit($campaign->hash);
            self::fail('Expected render');
        } catch (RenderedResponse $rendered) {
            self::assertSame(['database failed'], $rendered->data['errors']);
        }

        self::assertSame(['Sorry! There was a problem updating that campaign'], $session->errors);
    }

    public function testViewRedirectsWhenUnauthorized(): void
    {
        $campaign = $this->campaign();
        $memberFactory = $this->memberAccess(false);

        $session = $this->mapCurrentUser(8);
        $this->mapRedirectToException();
        $this->mapUrls(['campaigns_index' => '/campaigns']);

        try {
            $this->controller($this->campaignLookup($campaign), $memberFactory)->view($campaign->hash);
            self::fail('Expected redirect');
        } catch (RedirectedResponse $redirected) {
            self::assertSame('/campaigns', $redirected->url);
        }

        self::assertSame(['You do not have access to that campaign'], $session->errors);
    }

    public function testViewRendersCampaignForMember(): void
    {
        $campaign = $this->campaign();
        $member = new Entity(['user_id' => 8]);
        $memberUser = new Entity(['id' => 8, 'email' => 'player@example.com']);
        $rosterCharacter = new Entity(['name' => 'Roster']);
        $availableCharacter = new Entity(['name' => 'Available']);
        $currentCharacter = new Entity(['name' => 'Current']);

        $memberFactory = $this->getMockBuilder(Member::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isMember', 'forCampaign'])
            ->getMock();
        $memberFactory->expects(self::exactly(2))
            ->method('isMember')
            ->with($campaign, 7)
            ->willReturn(true);
        $memberFactory->expects(self::once())
            ->method('forCampaign')
            ->with($campaign)
            ->willReturn([$member]);

        $characterFactory = $this->getMockBuilder(Character::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['forCampaignAndUser', 'forUserWithoutCampaign'])
            ->getMock();
        $characterFactory->expects(self::exactly(2))
            ->method('forCampaignAndUser')
            ->willReturnMap([
                [$campaign, 7, [$currentCharacter]],
                [$campaign, 8, [$rosterCharacter]],
            ]);
        $characterFactory->expects(self::once())
            ->method('forUserWithoutCampaign')
            ->with(7)
            ->willReturn([$availableCharacter]);

        $userFactory = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['byId'])
            ->getMock();
        $userFactory->expects(self::exactly(2))
            ->method('byId')
            ->willReturnMap([
                [7, new Entity(['id' => 7, 'email' => 'owner@example.com'])],
                [8, $memberUser],
            ]);

        $this->mapCurrentUser(7);
        $this->mapRequest('GET', url: '/campaigns/abc123');
        $this->mapRenderToException();

        try {
            $this->controller($this->campaignLookup($campaign), $memberFactory, $characterFactory, $userFactory)->view($campaign->hash);
            self::fail('Expected render');
        } catch (RenderedResponse $rendered) {
            self::assertSame('campaigns/view.twig', $rendered->template);
            self::assertSame($campaign, $rendered->data['campaign']);
            self::assertSame('https://example.test/campaigns/join/abc123', $rendered->data['invite_url']);
            self::assertTrue($rendered->data['is_owner']);
            self::assertTrue($rendered->data['is_member']);
            self::assertFalse($rendered->data['is_superuser']);
            self::assertSame([$availableCharacter], $rendered->data['available_characters']);
            self::assertSame([$currentCharacter], $rendered->data['current_user_characters']);
            self::assertFalse($rendered->data['can_leave']);
            self::assertSame($rosterCharacter, $rendered->data['roster_characters'][0]['character']);
        }
    }

    public function testJoinPostRedirectsOnSuccess(): void
    {
        $campaign = $this->campaign();
        $memberFactory = $this->getMockBuilder(Member::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['ensureMember'])
            ->getMock();
        $memberFactory->expects(self::once())
            ->method('ensureMember')
            ->with($campaign, 7)
            ->willReturn(new Result());

        $session = $this->mapCurrentUser(7);
        $this->mapRequest('POST');
        $this->mapRedirectToException();
        $this->mapUrls([
            'campaigns_index' => '/campaigns',
            'campaigns_view' => '/campaigns/{hash}',
        ]);

        try {
            $this->controller($this->campaignLookup($campaign), $memberFactory)->join($campaign->hash);
            self::fail('Expected redirect');
        } catch (RedirectedResponse $redirected) {
            self::assertSame('/campaigns/abc123', $redirected->url);
        }

        self::assertSame(['Joined campaign The Flood'], $session->successes);
    }

    public function testJoinPostRendersFailure(): void
    {
        $campaign = $this->campaign();
        $memberFactory = $this->getMockBuilder(Member::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['ensureMember', 'isMember'])
            ->getMock();
        $memberFactory->expects(self::once())
            ->method('ensureMember')
            ->willReturn(new Result(['join failed']));
        $memberFactory->expects(self::once())
            ->method('isMember')
            ->willReturn(false);

        $session = $this->mapCurrentUser(7);
        $this->mapRequest('POST');
        $this->mapRenderToException();

        try {
            $this->controller($this->campaignLookup($campaign), $memberFactory)->join($campaign->hash);
            self::fail('Expected render');
        } catch (RenderedResponse $rendered) {
            self::assertSame('campaigns/join.twig', $rendered->template);
        }

        self::assertSame(['Sorry! There was a problem joining that campaign'], $session->errors);
    }

    public function testJoinGetRendersCampaignJoinState(): void
    {
        $campaign = $this->campaign();
        $memberFactory = $this->memberAccess(true);
        $userFactory = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['byId'])
            ->getMock();
        $userFactory->expects(self::once())
            ->method('byId')
            ->with(7)
            ->willReturn(new Entity(['id' => 7, 'email' => 'owner@example.com']));

        $this->mapCurrentUser(7);
        $this->mapRequest('GET');
        $this->mapRenderToException();

        try {
            $this->controller($this->campaignLookup($campaign), $memberFactory, userFactory: $userFactory)->join($campaign->hash);
            self::fail('Expected render');
        } catch (RenderedResponse $rendered) {
            self::assertSame('campaigns/join.twig', $rendered->template);
            self::assertSame('Join Campaign', $rendered->data['page_title']);
            self::assertTrue($rendered->data['is_member']);
        }
    }

    public function testResetClearsHashAndRedirectsToNewLink(): void
    {
        $campaign = $this->campaign();
        $campaignFactory = $this->getMockBuilder(CampaignFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['forHash', 'update'])
            ->getMock();
        $campaignFactory->method('forHash')
            ->willReturn($campaign);
        $campaignFactory->expects(self::once())
            ->method('update')
            ->with(self::callback(function (Entity $updated): bool {
                self::assertSame('', $updated->hash);
                $updated->hash = 'newhash';

                return true;
            }))
            ->willReturn(new Result());

        $session = $this->mapCurrentUser(7);
        $this->mapRedirectToException();
        $this->mapUrls([
            'campaigns_index' => '/campaigns',
            'campaigns_view' => '/campaigns/{hash}',
        ]);

        try {
            $this->controller($campaignFactory, $this->memberAccess(true))->reset('abc123');
            self::fail('Expected redirect');
        } catch (RedirectedResponse $redirected) {
            self::assertSame('/campaigns/newhash', $redirected->url);
        }

        self::assertSame(['Reset campaign link successfully'], $session->successes);
    }

    public function testResetFlashesUpdateFailure(): void
    {
        $campaign = $this->campaign();
        $campaignFactory = $this->getMockBuilder(CampaignFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['forHash', 'update'])
            ->getMock();
        $campaignFactory->expects(self::once())
            ->method('forHash')
            ->willReturn($campaign);
        $campaignFactory->expects(self::once())
            ->method('update')
            ->willReturn(new Result(['reset failed']));

        $session = $this->mapCurrentUser(7);
        $this->mapRedirectToException();
        $this->mapUrls([
            'campaigns_index' => '/campaigns',
            'campaigns_view' => '/campaigns/{hash}',
        ]);

        try {
            $this->controller($campaignFactory, $this->memberAccess(true))->reset($campaign->hash);
            self::fail('Expected redirect');
        } catch (RedirectedResponse $redirected) {
            self::assertSame('/campaigns/', $redirected->url);
        }

        self::assertSame(['reset failed'], $session->errors);
    }

    public function testAddCharacterRejectsNonMember(): void
    {
        $campaign = $this->campaign();
        $session = $this->mapCurrentUser(7);
        $this->mapRedirectToException();
        $this->mapUrls(['campaigns_index' => '/campaigns']);

        try {
            $this->controller($this->campaignLookup($campaign), $this->memberAccess(false))->addCharacter($campaign->hash);
            self::fail('Expected redirect');
        } catch (RedirectedResponse $redirected) {
            self::assertSame('/campaigns', $redirected->url);
        }

        self::assertSame(['You do not have access to that campaign'], $session->errors);
    }

    public function testAddCharacterRedirectsWhenCharacterIsMissing(): void
    {
        $_POST = ['character_hash' => 'bad'];
        $campaign = $this->campaign();
        $characterFactory = $this->getMockBuilder(Character::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['forUserHash', 'joinCampaign'])
            ->getMock();
        $characterFactory->expects(self::once())
            ->method('forUserHash')
            ->with(7, 'bad')
            ->willReturn(null);
        $characterFactory->expects(self::never())
            ->method('joinCampaign');

        $session = $this->mapCurrentUser(7);
        $this->mapRedirectToException();
        $this->mapUrls([
            'campaigns_index' => '/campaigns',
            'campaigns_view' => '/campaigns/{hash}',
        ]);

        try {
            $this->controller($this->campaignLookup($campaign), $this->memberAccess(true), $characterFactory)->addCharacter($campaign->hash);
            self::fail('Expected redirect');
        } catch (RedirectedResponse $redirected) {
            self::assertSame('/campaigns/abc123', $redirected->url);
        }

        self::assertSame(['Unable to find character'], $session->errors);
    }

    public function testAddCharacterFlashesJoinResult(): void
    {
        $_POST = ['character_hash' => 'charhash'];
        $campaign = $this->campaign();
        $character = new Entity(['name' => 'Mara', 'hash' => 'charhash']);

        $characterFactory = $this->getMockBuilder(Character::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['forUserHash', 'joinCampaign'])
            ->getMock();
        $characterFactory->method('forUserHash')
            ->willReturn($character);
        $characterFactory->expects(self::once())
            ->method('joinCampaign')
            ->with($campaign, $character)
            ->willReturn(new Result());

        $session = $this->mapCurrentUser(7);
        $this->mapRedirectToException();
        $this->mapUrls([
            'campaigns_index' => '/campaigns',
            'campaigns_view' => '/campaigns/{hash}',
        ]);

        try {
            $this->controller($this->campaignLookup($campaign), $this->memberAccess(true), $characterFactory)->addCharacter($campaign->hash);
            self::fail('Expected redirect');
        } catch (RedirectedResponse $redirected) {
            self::assertSame('/campaigns/abc123', $redirected->url);
        }

        self::assertSame(['Added Mara to the campaign'], $session->successes);
    }

    public function testAddCharacterFlashesJoinFailure(): void
    {
        $_POST = ['character_hash' => 'charhash'];
        $campaign = $this->campaign();
        $character = new Entity(['name' => 'Mara', 'hash' => 'charhash']);

        $characterFactory = $this->getMockBuilder(Character::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['forUserHash', 'joinCampaign'])
            ->getMock();
        $characterFactory->expects(self::once())
            ->method('forUserHash')
            ->willReturn($character);
        $characterFactory->expects(self::once())
            ->method('joinCampaign')
            ->with($campaign, $character)
            ->willReturn(new Result(['already in another campaign']));

        $session = $this->mapCurrentUser(7);
        $this->mapRedirectToException();
        $this->mapUrls([
            'campaigns_index' => '/campaigns',
            'campaigns_view' => '/campaigns/{hash}',
        ]);

        try {
            $this->controller($this->campaignLookup($campaign), $this->memberAccess(true), $characterFactory)->addCharacter($campaign->hash);
            self::fail('Expected redirect');
        } catch (RedirectedResponse $redirected) {
            self::assertSame('/campaigns/abc123', $redirected->url);
        }

        self::assertSame(['already in another campaign'], $session->errors);
    }

    public function testLeaveCharacterRejectsCharacterOutsideCampaign(): void
    {
        $campaign = $this->campaign();
        $character = new Entity(['name' => 'Mara', 'hash' => 'charhash', 'campaign' => 99]);
        $characterFactory = $this->getMockBuilder(Character::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['forUserHash', 'leaveCampaign'])
            ->getMock();
        $characterFactory->method('forUserHash')
            ->willReturn($character);
        $characterFactory->expects(self::never())
            ->method('leaveCampaign');

        $session = $this->mapCurrentUser(7);
        $this->mapRedirectToException();
        $this->mapUrls([
            'campaigns_index' => '/campaigns',
            'campaigns_view' => '/campaigns/{hash}',
        ]);

        try {
            $this->controller($this->campaignLookup($campaign), $this->memberAccess(true), $characterFactory)->leaveCharacter($campaign->hash, 'charhash');
            self::fail('Expected redirect');
        } catch (RedirectedResponse $redirected) {
            self::assertSame('/campaigns/abc123', $redirected->url);
        }

        self::assertSame(['Unable to find campaign character'], $session->errors);
    }

    public function testLeaveCharacterFlashesRemovalResult(): void
    {
        $campaign = $this->campaign();
        $character = new Entity(['name' => 'Mara', 'hash' => 'charhash', 'campaign' => 3]);
        $characterFactory = $this->getMockBuilder(Character::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['forUserHash', 'leaveCampaign'])
            ->getMock();
        $characterFactory->method('forUserHash')
            ->willReturn($character);
        $characterFactory->expects(self::once())
            ->method('leaveCampaign')
            ->with($character)
            ->willReturn(new Result());

        $session = $this->mapCurrentUser(7);
        $this->mapRedirectToException();
        $this->mapUrls([
            'campaigns_index' => '/campaigns',
            'campaigns_view' => '/campaigns/{hash}',
        ]);

        try {
            $this->controller($this->campaignLookup($campaign), $this->memberAccess(true), $characterFactory)->leaveCharacter($campaign->hash, 'charhash');
            self::fail('Expected redirect');
        } catch (RedirectedResponse $redirected) {
            self::assertSame('/campaigns/abc123', $redirected->url);
        }

        self::assertSame(['Removed Mara from the campaign'], $session->successes);
    }

    public function testLeaveCharacterFlashesRemovalFailure(): void
    {
        $campaign = $this->campaign();
        $character = new Entity(['name' => 'Mara', 'hash' => 'charhash', 'campaign' => 3]);
        $characterFactory = $this->getMockBuilder(Character::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['forUserHash', 'leaveCampaign'])
            ->getMock();
        $characterFactory->expects(self::once())
            ->method('forUserHash')
            ->willReturn($character);
        $characterFactory->expects(self::once())
            ->method('leaveCampaign')
            ->with($character)
            ->willReturn(new Result(['remove failed']));

        $session = $this->mapCurrentUser(7);
        $this->mapRedirectToException();
        $this->mapUrls([
            'campaigns_index' => '/campaigns',
            'campaigns_view' => '/campaigns/{hash}',
        ]);

        try {
            $this->controller($this->campaignLookup($campaign), $this->memberAccess(true), $characterFactory)->leaveCharacter($campaign->hash, 'charhash');
            self::fail('Expected redirect');
        } catch (RedirectedResponse $redirected) {
            self::assertSame('/campaigns/abc123', $redirected->url);
        }

        self::assertSame(['Sorry! There was a problem removing that character'], $session->errors);
    }

    public function testLeaveRedirectsToIndexOnSuccess(): void
    {
        $campaign = $this->campaign();
        $memberFactory = $this->getMockBuilder(Member::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isMember', 'leaveCampaign'])
            ->getMock();
        $memberFactory->method('isMember')
            ->willReturn(true);
        $memberFactory->expects(self::once())
            ->method('leaveCampaign')
            ->with($campaign, 7)
            ->willReturn(new Result());

        $session = $this->mapCurrentUser(7);
        $this->mapRedirectToException();
        $this->mapUrls([
            'campaigns_index' => '/campaigns',
            'campaigns_view' => '/campaigns/{hash}',
        ]);

        try {
            $this->controller($this->campaignLookup($campaign), $memberFactory)->leave($campaign->hash);
            self::fail('Expected redirect');
        } catch (RedirectedResponse $redirected) {
            self::assertSame('/campaigns', $redirected->url);
        }

        self::assertSame(['Left campaign The Flood'], $session->successes);
    }

    public function testLeaveRedirectsBackOnFailure(): void
    {
        $campaign = $this->campaign();
        $memberFactory = $this->getMockBuilder(Member::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isMember', 'leaveCampaign'])
            ->getMock();
        $memberFactory->expects(self::once())
            ->method('isMember')
            ->willReturn(true);
        $memberFactory->expects(self::once())
            ->method('leaveCampaign')
            ->willReturn(new Result(['Characters must leave first']));

        $session = $this->mapCurrentUser(7);
        $this->mapRedirectToException();
        $this->mapUrls([
            'campaigns_index' => '/campaigns',
            'campaigns_view' => '/campaigns/{hash}',
        ]);

        try {
            $this->controller($this->campaignLookup($campaign), $memberFactory)->leave($campaign->hash);
            self::fail('Expected redirect');
        } catch (RedirectedResponse $redirected) {
            self::assertSame('/campaigns/abc123', $redirected->url);
        }

        self::assertSame(['Characters must leave first'], $session->errors);
    }

    private function campaign(): Entity
    {
        return new Entity([
            'id' => 3,
            'hash' => 'abc123',
            'user' => 7,
            'name' => 'The Flood',
        ]);
    }

    private function campaignLookup(?Entity $campaign): CampaignFactory
    {
        $factory = $this->getMockBuilder(CampaignFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['forHash', 'invitePath'])
            ->getMock();
        $factory->expects(self::once())
            ->method('forHash')
            ->willReturn($campaign);
        $factory->method('invitePath')
            ->willReturn('/campaigns/join/abc123');

        return $factory;
    }

    private function memberAccess(bool $isMember): Member
    {
        $factory = $this->getMockBuilder(Member::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isMember'])
            ->getMock();
        $factory->expects(self::atLeastOnce())
            ->method('isMember')
            ->willReturn($isMember);

        return $factory;
    }

    private function mapCurrentUser(int $id, bool $superuser = false): object
    {
        $session = $this->mapSession();
        $session->user = (object) [
            'id' => $id,
            'superuser' => $superuser,
        ];

        return $session;
    }

    private function controller(
        ?CampaignFactory $campaignFactory = null,
        ?Member $memberFactory = null,
        ?Character $characterFactory = null,
        ?User $userFactory = null,
    ): Campaigns {
        return new Campaigns(
            $campaignFactory ?? $this->createStub(CampaignFactory::class),
            $memberFactory ?? $this->createStub(Member::class),
            $characterFactory ?? $this->createStub(Character::class),
            $userFactory ?? $this->createStub(User::class),
        );
    }
}
