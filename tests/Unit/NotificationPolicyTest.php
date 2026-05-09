<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\AccountStatus;
use App\Policies\NotificationPolicy;
use PHPUnit\Framework\TestCase;

final class NotificationPolicyTest extends TestCase
{
    public function testActiveUserCanViewOwnNotification(): void
    {
        $policy = new NotificationPolicy();

        $this->assertTrue($policy->view(
            ['user_id' => 10, 'status' => AccountStatus::ACTIVE->value],
            ['user_id' => 10]
        ));
    }

    public function testUserCannotViewAnotherUsersNotification(): void
    {
        $policy = new NotificationPolicy();

        $this->assertFalse($policy->view(
            ['user_id' => 10, 'status' => AccountStatus::ACTIVE->value],
            ['user_id' => 11]
        ));
    }

    public function testInactiveUserCannotViewOwnNotification(): void
    {
        $policy = new NotificationPolicy();

        $this->assertFalse($policy->view(
            ['user_id' => 10, 'status' => AccountStatus::INACTIVE->value],
            ['user_id' => 10]
        ));
    }

    public function testMarkReadUsesSameRuleAsView(): void
    {
        $policy = new NotificationPolicy();

        $this->assertTrue($policy->markRead(
            ['user_id' => 10, 'status' => AccountStatus::ACTIVE->value],
            ['user_id' => 10]
        ));
    }
}
