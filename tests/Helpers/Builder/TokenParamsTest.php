<?php

namespace MailerSend\Tests\Helpers\Builder;

use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Builder\TokenParams;
use PHPUnit\Framework\TestCase;

class TokenParamsTest extends TestCase
{
    public function test_rejects_invalid_scopes(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Some scopes are not valid.');

        new TokenParams('My Token', 'domain_abc', ['invalid_scope']);
    }

    public function test_set_scopes_rejects_invalid_scopes(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Some scopes are not valid.');

        $params = new TokenParams('My Token', 'domain_abc', [TokenParams::EMAIL_FULL]);
        $params->setScopes(['not_a_real_scope']);
    }

    public function test_accepts_every_documented_scope(): void
    {
        $documentedScopes = [
            'email_full',
            'domains_read',
            'domains_full',
            'activity_read',
            'activity_full',
            'analytics_read',
            'analytics_full',
            'tokens_full',
            'webhooks_full',
            'templates_full',
            'suppressions_read',
            'suppressions_full',
            'sms_full',
            'sms_read',
            'email_verification_read',
            'email_verification_full',
            'inbounds_full',
            'recipients_read',
            'recipients_full',
            'sender_identity_read',
            'sender_identity_full',
            'users_read',
            'users_full',
            'smtp_users_read',
            'smtp_users_full',
            'dmarc_monitoring_read',
            'dmarc_monitoring_full',
            'blocklist_monitoring_read',
            'blocklist_monitoring_full',
            'whatsapp_full',
        ];

        $params = new TokenParams('My Token', null, $documentedScopes);

        self::assertSame($documentedScopes, $params->getScopes());
    }

    public function test_to_array_returns_all_fields(): void
    {
        $params = new TokenParams('My Token', 'domain_abc', [TokenParams::EMAIL_FULL, TokenParams::DOMAINS_READ]);

        self::assertSame([
            'name' => 'My Token',
            'domain_id' => 'domain_abc',
            'scopes' => [TokenParams::EMAIL_FULL, TokenParams::DOMAINS_READ],
        ], $params->toArray());
    }

    public function test_to_array_excludes_null_domain_id(): void
    {
        $params = new TokenParams('My Token', null, [TokenParams::EMAIL_FULL]);

        self::assertArrayNotHasKey('domain_id', $params->toArray());
    }
}
