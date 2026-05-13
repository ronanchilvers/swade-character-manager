<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity;
use App\Entity\Factory\Campaign;
use App\Entity\Factory\Campaign\Member;
use App\Entity\Factory\Character;
use App\Entity\Factory\User;
use App\Filter;
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
        Flight::render('campaigns/index.twig', [
            'page_title' => 'Campaigns',
            'campaigns' => $this->campaignFactory->forMemberUser($this->currentUserId()),
        ]);
    }

    public function create(): void
    {
        $campaign = new Entity();
        $errors = [];

        if ('POST' === Flight::request()->getMethod()) {
            $campaign->name = trim(Filter::noTags($_POST['name'] ?? ''));
            $campaign->description = trim(Filter::noTags($_POST['description'] ?? ''));
            $errors = $this->campaignFactory->validate($campaign);
            if (!$errors) {
                $result = $this->campaignFactory->insert($campaign);
                $errors = $result->errors();
            }
            if (!$errors) {
                Flight::session()->success(sprintf('Created campaign %s', $campaign->name));
                Flight::redirect(Flight::getUrl('campaigns_view', ['hash' => $campaign->hash]));
                return;
            }
            Flight::session()->error('Sorry! There was a problem creating that campaign');
        }

        Flight::render('campaigns/create.twig', [
            'page_title' => 'Create Campaign',
            'entity' => $campaign,
            'errors' => $errors,
        ]);
    }

    public function view(string $hash): void
    {
        $campaign = $this->campaignForHash($hash);
        if (!$campaign instanceof Entity) {
            return;
        }
        if (!$this->canView($campaign)) {
            Flight::session()->error('You do not have access to that campaign');
            Flight::redirect(Flight::getUrl('campaigns_index'));
            return;
        }
        $isMember = $this->memberFactory->isMember($campaign, $this->currentUserId());

        Flight::render('campaigns/view.twig', [
            'page_title' => $campaign->name,
            'campaign' => $campaign,
            'owner' => $this->userFactory->byId((int) $campaign->user),
            'invite_url' => $this->inviteUrl($campaign),
            'is_owner' => (int) $campaign->user === $this->currentUserId(),
            'is_member' => $isMember,
            'is_superuser' => $this->isSuperuser(),
            'roster' => $this->roster($campaign),
            'available_characters' => $isMember
                ? $this->characterFactory->forUserWithoutCampaign($this->currentUserId())
                : [],
            'current_user_characters' => $this->characterFactory->forCampaignAndUser($campaign, $this->currentUserId()),
            'can_leave' => $isMember
                && [] === $this->characterFactory->forCampaignAndUser($campaign, $this->currentUserId()),
        ]);
    }

    public function join(string $hash): void
    {
        $campaign = $this->campaignForHash($hash);
        if (!$campaign instanceof Entity) {
            return;
        }

        if ('POST' === Flight::request()->getMethod()) {
            $result = $this->memberFactory->ensureMember($campaign, $this->currentUserId());
            if ($result->isSuccess()) {
                Flight::session()->success(sprintf('Joined campaign %s', $campaign->name));
                Flight::redirect(Flight::getUrl('campaigns_view', ['hash' => $campaign->hash]));
                return;
            }
            Flight::session()->error('Sorry! There was a problem joining that campaign');
        }

        Flight::render('campaigns/join.twig', [
            'page_title' => 'Join Campaign',
            'campaign' => $campaign,
            'owner' => $this->userFactory->byId((int) $campaign->user),
            'is_member' => $this->memberFactory->isMember($campaign, $this->currentUserId()),
        ]);
    }

    public function addCharacter(string $hash): void
    {
        $campaign = $this->requireMemberCampaign($hash);
        if (!$campaign instanceof Entity) {
            return;
        }

        $characterHash = trim(Filter::noTags($_POST['character_hash'] ?? ''));
        $character = $this->characterFactory->forUserHash($this->currentUserId(), $characterHash);
        if (!$character instanceof Entity) {
            Flight::session()->error('Unable to find character');
            Flight::redirect(Flight::getUrl('campaigns_view', ['hash' => $campaign->hash]));
            return;
        }

        $result = $this->characterFactory->joinCampaign($campaign, $character);
        if ($result->isSuccess()) {
            Flight::session()->success(sprintf('Added %s to the campaign', $character->name));
        } else {
            Flight::session()->error($result->errors()[0] ?? 'Sorry! There was a problem adding that character');
        }

        Flight::redirect(Flight::getUrl('campaigns_view', ['hash' => $campaign->hash]));
    }

    public function leaveCharacter(string $hash, string $characterHash): void
    {
        $campaign = $this->requireMemberCampaign($hash);
        if (!$campaign instanceof Entity) {
            return;
        }

        $character = $this->characterFactory->forUserHash($this->currentUserId(), $characterHash);
        if (!$character instanceof Entity || (int) $character->campaign !== (int) $campaign->id) {
            Flight::session()->error('Unable to find campaign character');
            Flight::redirect(Flight::getUrl('campaigns_view', ['hash' => $campaign->hash]));
            return;
        }

        $result = $this->characterFactory->leaveCampaign($character);
        if ($result->isSuccess()) {
            Flight::session()->success(sprintf('Removed %s from the campaign', $character->name));
        } else {
            Flight::session()->error('Sorry! There was a problem removing that character');
        }

        Flight::redirect(Flight::getUrl('campaigns_view', ['hash' => $campaign->hash]));
    }

    public function leave(string $hash): void
    {
        $campaign = $this->requireMemberCampaign($hash);
        if (!$campaign instanceof Entity) {
            return;
        }

        $result = $this->memberFactory->leaveCampaign($campaign, $this->currentUserId());
        if ($result->isSuccess()) {
            Flight::session()->success(sprintf('Left campaign %s', $campaign->name));
            Flight::redirect(Flight::getUrl('campaigns_index'));
            return;
        }

        Flight::session()->error($result->errors()[0] ?? 'Sorry! There was a problem leaving that campaign');
        Flight::redirect(Flight::getUrl('campaigns_view', ['hash' => $campaign->hash]));
    }

    private function campaignForHash(string $hash): ?Entity
    {
        $campaign = $this->campaignFactory->forHash($hash);
        if (!$campaign instanceof Entity) {
            Flight::session()->error('Unable to find campaign');
            Flight::redirect(Flight::getUrl('campaigns_index'));
            return null;
        }

        return $campaign;
    }

    private function requireMemberCampaign(string $hash): ?Entity
    {
        $campaign = $this->campaignForHash($hash);
        if (!$campaign instanceof Entity) {
            return null;
        }
        if (!$this->memberFactory->isMember($campaign, $this->currentUserId())) {
            Flight::session()->error('You do not have access to that campaign');
            Flight::redirect(Flight::getUrl('campaigns_index'));
            return null;
        }

        return $campaign;
    }

    private function canView(Entity $campaign): bool
    {
        return $this->isSuperuser()
            || $this->memberFactory->isMember($campaign, $this->currentUserId());
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

    private function inviteUrl(Entity $campaign): string
    {
        $request = Flight::request();
        $path = $this->campaignFactory->invitePath($campaign);
        if (!isset($request->host, $request->scheme)) {
            return $path;
        }

        return sprintf('%s://%s%s', $request->scheme, $request->host, $path);
    }

    private function currentUserId(): int
    {
        return (int) Flight::session()->user->id;
    }

    private function isSuperuser(): bool
    {
        return isset(Flight::session()->user) && (bool) Flight::session()->user->superuser;
    }
}
