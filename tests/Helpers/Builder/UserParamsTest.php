<?php

namespace MailerSend\Tests\Helpers\Builder;

use MailerSend\Helpers\Builder\UserParams;
use PHPUnit\Framework\TestCase;

class UserParamsTest extends TestCase
{
    public function test_empty_optional_fields_absent_from_to_array(): void
    {
        $params = new UserParams('user@example.com', 'manager');

        $array = $params->toArray();

        self::assertArrayHasKey('email', $array);
        self::assertArrayHasKey('role', $array);
        self::assertArrayNotHasKey('permissions', $array);
        self::assertArrayNotHasKey('domains', $array);
        self::assertArrayNotHasKey('templates', $array);
        self::assertArrayNotHasKey('requires_periodic_password_change', $array);
    }

    public function test_requires_periodic_password_change_false_is_absent(): void
    {
        $params = new UserParams();
        $params->setRequiresPeriodicPasswordChange(false);

        $array = $params->toArray();

        self::assertArrayNotHasKey('requires_periodic_password_change', $array);
    }

}
