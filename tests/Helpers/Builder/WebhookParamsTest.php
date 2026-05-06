<?php

namespace MailerSend\Tests\Helpers\Builder;

use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Builder\WebhookParams;
use MailerSend\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class WebhookParamsTest extends TestCase
{
    /**
     * @dataProvider invalidEventsProvider
     * @param array $events
     */
    #[DataProvider('invalidEventsProvider')]
    public function test_rejects_invalid_events(array $events): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('One or multiple invalid events.');

        new WebhookParams(
            'https://example.com/hook',
            'My Webhook',
            $events,
            'domain_abc'
        );
    }

    public static function invalidEventsProvider(): array
    {
        return [
            'single invalid event'          => [['invalid.event']],
            'multiple invalid events'       => [['bad.one', 'bad.two']],
            'mixed valid and invalid event' => [[WebhookParams::ACTIVITY_SENT, 'not.a.real.event']],
        ];
    }

    public function test_all_activities_constant_contains_all_event_constants(): void
    {
        $expected = [
            WebhookParams::ACTIVITY_SENT,
            WebhookParams::ACTIVITY_DELIVERED,
            WebhookParams::ACTIVITY_SOFT_BOUNCED,
            WebhookParams::ACTIVITY_HARD_BOUNCED,
            WebhookParams::ACTIVITY_DEFERRED,
            WebhookParams::ACTIVITY_OPENED,
            WebhookParams::ACTIVITY_OPENED_UNIQUE,
            WebhookParams::ACTIVITY_CLICKED,
            WebhookParams::ACTIVITY_CLICKED_UNIQUE,
            WebhookParams::ACTIVITY_UNSUBSCRIBED,
            WebhookParams::ACTIVITY_SPAM_COMPLAINT,
            WebhookParams::ACTIVITY_SURVEY_OPENED,
            WebhookParams::ACTIVITY_SURVEY_SUBMITTED,
            WebhookParams::ACTIVITY_IDENTITY_VERIFIED,
            WebhookParams::ACTIVITY_MAINTENANCE_START,
            WebhookParams::ACTIVITY_MAINTENANCE_END,
            WebhookParams::ACTIVITY_INBOUND_FORWARD_FAILED,
            WebhookParams::ACTIVITY_EMAIL_SINGLE_VERIFIED,
            WebhookParams::ACTIVITY_EMAIL_LIST_VERIFIED,
            WebhookParams::ACTIVITY_BULK_EMAIL_COMPLETED,
            WebhookParams::ACTIVITY_RECIPIENT_ON_HOLD_ADDED,
            WebhookParams::ACTIVITY_RECIPIENT_ON_HOLD_REMOVED,
        ];

        foreach ($expected as $event) {
            self::assertContains($event, WebhookParams::ALL_ACTIVITIES, "Missing event: $event");
        }
        self::assertCount(count($expected), WebhookParams::ALL_ACTIVITIES, 'ALL_ACTIVITIES has unexpected extra entries.');
    }
}
