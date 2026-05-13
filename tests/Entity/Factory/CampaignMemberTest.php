<?php

declare(strict_types=1);

namespace Tests\Entity\Factory;

use App\Entity;
use App\Entity\Factory\Campaign\Member;
use App\Entity\Validator;
use flight\database\SimplePdo;
use flight\util\Collection;
use PHPUnit\Framework\TestCase;

class CampaignMemberTest extends TestCase
{
    public function testValidationAcceptsKnownRoles(): void
    {
        $errors = $this->factory()->validate(new Entity([
            'campaign_id' => 4,
            'user_id' => 7,
            'role' => Member::ROLE_OWNER,
        ]));

        self::assertSame([], $errors);
    }

    public function testEnsureMemberIsIdempotentWhenMembershipExists(): void
    {
        $campaign = new Entity(['id' => 4]);
        $pdo = $this->createMock(SimplePdo::class);
        $pdo->expects(self::once())
            ->method('fetchRow')
            ->with(
                'SELECT * FROM campaign_members WHERE campaign_member_campaign_id = ? AND campaign_member_user_id = ?',
                [4, 7],
            )
            ->willReturn(new Collection([
                'campaign_member_id' => 9,
                'campaign_member_campaign_id' => 4,
                'campaign_member_user_id' => 7,
                'campaign_member_role' => Member::ROLE_MEMBER,
            ]));
        $pdo->expects(self::never())
            ->method('insert');

        $result = $this->factory($pdo)->ensureMember($campaign, 7);

        self::assertTrue($result->isSuccess());
    }

    public function testLeaveCampaignIsBlockedWhenUserHasAssignedCharacters(): void
    {
        $pdo = $this->createMock(SimplePdo::class);
        $pdo->expects(self::once())
            ->method('fetchRow')
            ->with(
                'SELECT COUNT(*) AS assigned_count FROM characters WHERE character_campaign = ? AND character_user = ?',
                [4, 7],
            )
            ->willReturn(new Collection(['assigned_count' => 1]));
        $pdo->expects(self::never())
            ->method('delete');

        $result = $this->factory($pdo)->leaveCampaign(new Entity(['id' => 4]), 7);

        self::assertFalse($result->isSuccess());
        self::assertSame(['Remove your characters from the campaign before leaving'], $result->errors());
    }

    public function testLeaveCampaignDeletesMembershipWhenNoCharactersRemain(): void
    {
        $pdo = $this->createMock(SimplePdo::class);
        $pdo->expects(self::once())
            ->method('fetchRow')
            ->willReturn(new Collection(['assigned_count' => 0]));
        $pdo->expects(self::once())
            ->method('delete')
            ->with(
                'campaign_members',
                'campaign_member_campaign_id = ? AND campaign_member_user_id = ?',
                [4, 7],
            )
            ->willReturn(1);

        $result = $this->factory($pdo)->leaveCampaign(new Entity(['id' => 4]), 7);

        self::assertTrue($result->isSuccess());
    }

    private function factory(?SimplePdo $pdo = null): Member
    {
        return new Member(
            $pdo ?? $this->createStub(SimplePdo::class),
            new Validator(),
        );
    }
}
