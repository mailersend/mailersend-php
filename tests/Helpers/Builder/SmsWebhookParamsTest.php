<?php

namespace MailerSend\Tests\Helpers\Builder;

use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Builder\SmsWebhookParams;
use PHPUnit\Framework\TestCase;

class SmsWebhookParamsTest extends TestCase
{
    public function test_set_events_rejects_invalid_event(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('One or multiple invalid events.');

        new SmsWebhookParams(
            'https://example.com/sms-hook',
            'My SMS Webhook',
            ['invalid.event']
        );
    }

    public function test_set_events_via_setter_rejects_invalid_event(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('One or multiple invalid events.');

        $params = new SmsWebhookParams();
        $params->setEvents(['completely.wrong']);
    }

    public function test_all_activities_constant_contains_known_events(): void
    {
        self::assertContains(SmsWebhookParams::SMS_SENT, SmsWebhookParams::ALL_ACTIVITIES);
        self::assertContains(SmsWebhookParams::SMS_DELIVERED, SmsWebhookParams::ALL_ACTIVITIES);
        self::assertContains(SmsWebhookParams::SMS_FAILED, SmsWebhookParams::ALL_ACTIVITIES);
    }

}
