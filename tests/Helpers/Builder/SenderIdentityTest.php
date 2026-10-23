<?php

namespace MailerSend\Tests\Helpers\Builder;

use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Builder\SenderIdentity;
use MailerSend\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class SenderIdentityTest extends TestCase
{
    /**
     * @dataProvider invalidConstructorParamsProvider
     */
    #[DataProvider('invalidConstructorParamsProvider')]
    public function test_constructor_rejects_invalid_params(string $name, string $email, string $message): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($message);

        new SenderIdentity('domainId', $name, $email);
    }

    public static function invalidConstructorParamsProvider(): array
    {
        return [
            'name over 191 chars' => [
                str_repeat('a', 192),
                'test@test.com',
                'Name may not be greater than 191 characters.',
            ],
            'email over 320 chars' => [
                'Test',
                str_repeat('a', 312) . '@test.com',
                'Email may not be greater than 320 characters.',
            ],
        ];
    }

    public function test_set_name_rejects_name_over_191_chars(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Name may not be greater than 191 characters.');

        (new SenderIdentity('domainId', 'Test', 'test@test.com'))->setName(str_repeat('a', 192));
    }

    public function test_set_email_rejects_email_over_320_chars(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Email may not be greater than 320 characters.');

        (new SenderIdentity('domainId', 'Test', 'test@test.com'))->setEmail(str_repeat('a', 312) . '@test.com');
    }

    public function test_set_reply_to_email_rejects_value_over_320_chars(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Reply to email may not be greater than 320 characters.');

        (new SenderIdentity('domainId', 'Test', 'test@test.com'))->setReplyToEmail(str_repeat('a', 312) . '@test.com');
    }

    public function test_set_reply_to_name_rejects_value_over_191_chars(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Reply to name may not be greater than 191 characters.');

        (new SenderIdentity('domainId', 'Test', 'test@test.com'))->setReplyToName(str_repeat('a', 192));
    }

    public function test_set_personal_note_rejects_value_over_250_chars(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Personal note may not be greater than 250 characters.');

        (new SenderIdentity('domainId', 'Test', 'test@test.com'))->setPersonalNote(str_repeat('a', 251));
    }

    public function test_to_update_array_returns_only_update_fields(): void
    {
        $params = (new SenderIdentity('domainId', 'Test User', 'test@test.com'))
            ->setReplyToEmail('reply@test.com')
            ->setReplyToName('Reply User')
            ->setAddNote(true)
            ->setPersonalNote('some note');

        self::assertSame([
            'name' => 'Test User',
            'reply_to_email' => 'reply@test.com',
            'reply_to_name' => 'Reply User',
        ], $params->toUpdateArray());
    }

    public function test_to_update_array_excludes_unset_reply_to_fields(): void
    {
        $params = new SenderIdentity('domainId', 'Test User', 'test@test.com');

        self::assertSame(['name' => 'Test User'], $params->toUpdateArray());
    }
}
