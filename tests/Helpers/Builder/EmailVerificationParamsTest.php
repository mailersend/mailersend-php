<?php

namespace MailerSend\Tests\Helpers\Builder;

use MailerSend\Helpers\Builder\EmailVerificationParams;
use MailerSend\Tests\TestCase;

class EmailVerificationParamsTest extends TestCase
{
    public function test_to_array_returns_all_fields(): void
    {
        $params = (new EmailVerificationParams('file.csv'))
            ->setListId('list-abc-123')
            ->setVerify(true)
            ->setEmailAddresses(['test@mail.com']);

        self::assertSame([
            'name' => 'file.csv',
            'list_id' => 'list-abc-123',
            'verify' => true,
            'emails' => ['test@mail.com'],
        ], $params->toArray());
    }

    public function test_to_array_excludes_empty_name(): void
    {
        $params = (new EmailVerificationParams())
            ->setListId('list-abc-123')
            ->setEmailAddresses(['test@mail.com']);

        self::assertArrayNotHasKey('name', $params->toArray());
    }

    public function test_to_array_excludes_unset_list_id_and_verify(): void
    {
        $params = (new EmailVerificationParams('file.csv'))
            ->setEmailAddresses(['test@mail.com']);

        self::assertSame([
            'name' => 'file.csv',
            'emails' => ['test@mail.com'],
        ], $params->toArray());
    }

    public function test_to_array_keeps_false_verify(): void
    {
        $params = (new EmailVerificationParams('file.csv'))
            ->setVerify(false)
            ->setEmailAddresses(['test@mail.com']);

        self::assertFalse($params->toArray()['verify']);
    }
}
