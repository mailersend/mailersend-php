<?php

namespace MailerSend\Tests\Helpers\Builder;

use MailerSend\Common\Permissions;
use MailerSend\Common\Roles;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Builder\UserParams;
use MailerSend\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class UserParamsTest extends TestCase
{
    /**
     * @dataProvider invalidEmailProvider
     */
    #[DataProvider('invalidEmailProvider')]
    public function test_set_email_rejects_invalid_email(string $email, string $message): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($message);

        (new UserParams())->setEmail($email);
    }

    public static function invalidEmailProvider(): array
    {
        return [
            'invalid format' => [
                'not-an-email',
                'Value "not-an-email" was expected to be a valid e-mail address.',
            ],
            'exceeds 320 chars' => [
                str_repeat('a', 315) . '@b.com',
                'Email may not be greater than 320 characters.',
            ],
        ];
    }

    public function test_set_role_rejects_unknown_role(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Invalid role.');

        (new UserParams())->setRole('InvalidRole');
    }

    /**
     * @dataProvider documentedRolesProvider
     */
    #[DataProvider('documentedRolesProvider')]
    public function test_set_role_accepts_documented_role(string $role): void
    {
        self::assertSame($role, (new UserParams())->setRole($role)->getRole());
    }

    public static function documentedRolesProvider(): array
    {
        return [
            'admin' => ['Admin'],
            'manager' => ['Manager'],
            'designer' => ['Designer'],
            'accountant' => ['Accountant'],
            'custom user' => ['Custom User'],
        ];
    }

    public function test_to_array_returns_all_fields(): void
    {
        $params = (new UserParams('test@user.com', Roles::CUSTOM_USER))
            ->setPermissions([Permissions::READ_OWN_TEMPLATES])
            ->setTemplates(['template_id'])
            ->setDomains(['domain_id'])
            ->setRequiresPeriodicPasswordChange(true);

        self::assertSame([
            'email' => 'test@user.com',
            'role' => Roles::CUSTOM_USER,
            'permissions' => [Permissions::READ_OWN_TEMPLATES],
            'domains' => ['domain_id'],
            'templates' => ['template_id'],
            'requires_periodic_password_change' => true,
        ], $params->toArray());
    }

    public function test_to_array_excludes_unset_optional_fields(): void
    {
        $params = new UserParams('test@user.com', Roles::ADMIN);

        self::assertSame([
            'email' => 'test@user.com',
            'role' => Roles::ADMIN,
        ], $params->toArray());
    }

    public function test_to_update_array_returns_all_fields_except_email(): void
    {
        $params = (new UserParams('test@user.com', Roles::CUSTOM_USER))
            ->setPermissions([Permissions::READ_OWN_TEMPLATES])
            ->setTemplates(['template_id'])
            ->setDomains(['domain_id'])
            ->setRequiresPeriodicPasswordChange(true);

        self::assertSame([
            'role' => Roles::CUSTOM_USER,
            'permissions' => [Permissions::READ_OWN_TEMPLATES],
            'domains' => ['domain_id'],
            'templates' => ['template_id'],
            'requires_periodic_password_change' => true,
        ], $params->toUpdateArray());
    }

    public function test_to_update_array_excludes_unset_optional_fields(): void
    {
        $params = new UserParams(null, Roles::ADMIN);

        self::assertSame(['role' => Roles::ADMIN], $params->toUpdateArray());
    }
}
