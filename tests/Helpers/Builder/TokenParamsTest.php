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

        new TokenParams('My Token', 'domain_abc', ['invalid_scope']);
    }

    public function test_set_scopes_rejects_invalid_scopes(): void
    {
        $this->expectException(MailerSendAssertException::class);

        $params = new TokenParams('My Token', 'domain_abc', [TokenParams::EMAIL_FULL]);
        $params->setScopes(['not_a_real_scope']);
    }

    public function test_all_scopes_constant_contains_known_scopes(): void
    {
        self::assertContains(TokenParams::EMAIL_FULL, TokenParams::ALL_SCOPES);
        self::assertContains(TokenParams::DOMAINS_READ, TokenParams::ALL_SCOPES);
        self::assertContains(TokenParams::DOMAINS_FULL, TokenParams::ALL_SCOPES);
        self::assertContains(TokenParams::ACTIVITY_READ, TokenParams::ALL_SCOPES);
        self::assertContains(TokenParams::ACTIVITY_FULL, TokenParams::ALL_SCOPES);
        self::assertContains(TokenParams::ANALYTICS_READ, TokenParams::ALL_SCOPES);
        self::assertContains(TokenParams::ANALYTICS_FULL, TokenParams::ALL_SCOPES);
        self::assertContains(TokenParams::TOKENS_FULL, TokenParams::ALL_SCOPES);
        self::assertContains(TokenParams::WEBHOOKS_FULL, TokenParams::ALL_SCOPES);
        self::assertContains(TokenParams::TEMPLATES_FULL, TokenParams::ALL_SCOPES);
    }
}
