<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity;
use App\Entity\Factory\Campaign;
use App\Entity\Factory\Campaign\Member;
use App\Entity\Factory\Character;
use App\Entity\Factory\User;
use Flight;

class Campaigns
{
    public function __construct(
        private Campaign $campaignFactory,
        private Member $memberFactory,
        private Character $characterFactory,
        private User $userFactory,
    ) {
    }

    public function index(): void
    {
        Flight::render('admin/campaigns/index.twig', [
            'page_title' => 'Manage Campaigns',
            'campaigns' => $this->campaignFactory->allWithSummary(),
        ]);
    }

    public function view(string $hash): void
    {
        $campaign = $this->campaignFactory->forHash($hash);
        if (!$campaign instanceof Entity) {
            Flight::session()->error('Unable to find campaign');
            Flight::redirect(Flight::getUrl('admin_campaigns_index'));
            return;
        }

        Flight::render('admin/campaigns/view.twig', [
            'page_title' => $campaign->name,
            'campaign' => $campaign,
            'owner' => $this->userFactory->byId((int) $campaign->user),
            'roster' => $this->roster($campaign),
        ]);
    }

    private function roster(Entity $campaign): array
    {
        $members = [];
        foreach ($this->memberFactory->forCampaign($campaign) as $member) {
            $user = $this->userFactory->byId((int) $member->user_id);
            $members[] = [
                'member' => $member,
                'user' => $user,
                'characters' => $user instanceof Entity
                    ? $this->characterFactory->forCampaignAndUser($campaign, (int) $user->id)
                    : [],
            ];
        }

        return $members;
    }
}
