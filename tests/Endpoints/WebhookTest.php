<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\Webhook;
use MailerSend\Helpers\Builder\WebhookParams;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Http\Message\ResponseInterface;
use MailerSend\Helpers\Arr;

class WebhookTest extends TestCase
{
    protected Webhook $webhooks;
    protected ResponseInterface $defaultResponse;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = new Client();

        $this->webhooks = new Webhook(new HttpLayer(self::OPTIONS, $this->client), self::OPTIONS);
        $this->defaultResponse = $this->createStub(ResponseInterface::class);
        $this->defaultResponse->method('getStatusCode')->willReturn(200);
    }

    public function test_get_webhooks_domain_id_is_required(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Domain id is required.');

        $this->webhooks->get('');
    }

    public function test_find_webhook_is_validated(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Webhook id is required.');

        $this->webhooks->find('');
    }

    public function test_delete_webhook_is_validated(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Webhook id is required.');

        $this->webhooks->delete('');
    }

    public function test_get_webhooks(): void
    {
        $this->addSuccessResponse();
        $response = $this->webhooks->get('domain_id');
        $request = $this->client->getLastRequest();
        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/v1/webhooks', $request->getUri()->getPath());
        parse_str($request->getUri()->getQuery(), $query);
        $this->assertQueryParams(['domain_id' => 'domain_id'], $query);
        self::assertEquals(200, $response['status_code']);
    }

    public function test_find_webhook(): void
    {
        $this->addSuccessResponse();

        $response = $this->webhooks->find('webhook_id');

        $this->assertRequest('GET', '/v1/webhooks/webhook_id');

        self::assertEquals(200, $response['status_code']);
    }

    public function test_delete_webhook(): void
    {
        $this->addSuccessResponse();

        $response = $this->webhooks->delete('webhook_id');

        $this->assertRequest('DELETE', '/v1/webhooks/webhook_id');

        self::assertEquals(200, $response['status_code']);
    }

    /**
     * @dataProvider invalidCreateParamsProvider
     */
    #[DataProvider('invalidCreateParamsProvider')]
    public function test_create_rejects_invalid_params(WebhookParams $params, string $exceptionMessage): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $this->webhooks->create($params);
    }

    public static function invalidCreateParamsProvider(): array
    {
        return [
            'invalid url' => [
                new WebhookParams('invalid_url', 'Webhook name', WebhookParams::ALL_ACTIVITIES, 'domain_id'),
                'Invalid URL.',
            ],
            'missing name' => [
                new WebhookParams('https://link.com/webhook', '', WebhookParams::ALL_ACTIVITIES, 'domain_id'),
                'Webhook name is required.',
            ],
            'name too long' => [
                new WebhookParams('https://link.com/webhook', str_repeat('a', 192), WebhookParams::ALL_ACTIVITIES, 'domain_id'),
                'Webhook name cannot be longer than 191 character.',
            ],
            'missing domain id' => [
                new WebhookParams('https://link.com/webhook', 'webhook name', WebhookParams::ALL_ACTIVITIES, ''),
                'Webhook domain id is required.',
            ],
        ];
    }

    public function test_create_rejects_missing_events(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Webhook events are required.');

        $this->webhooks->create(
            new WebhookParams('https://link.com/webhook', 'webhook name', [], 'domain_id')
        );
    }

    public function test_create_rejects_invalid_events(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('One or multiple invalid events.');

        $this->webhooks->create(
            new WebhookParams('https://link.com/webhook', 'webhook name', ['invalid event'], 'domain_id')
        );
    }

    public function test_create_webhooks(): void
    {
        $this->addSuccessResponse();

        $response = $this->webhooks->create(
            new WebhookParams('https://link.com/webhook', 'webhook name', WebhookParams::ALL_ACTIVITIES, 'domain_id')
        );

        $body = $this->assertRequest('POST', '/v1/webhooks');

        self::assertEquals(200, $response['status_code']);
        $this->assertBodyContains([
            'url' => 'https://link.com/webhook',
            'name' => 'webhook name',
            'domain_id' => 'domain_id',
        ], $body);
        self::assertSame(WebhookParams::ALL_ACTIVITIES, Arr::get($body, 'events'));
        $this->assertBodyExcludes(['enabled'], $body);
    }

    public function test_create_disabled_webhooks(): void
    {
        $this->addSuccessResponse();

        $response = $this->webhooks->create(
            new WebhookParams('https://link.com/webhook', 'webhook name', WebhookParams::ALL_ACTIVITIES, 'domain_id', false)
        );

        $body = $this->assertRequest('POST', '/v1/webhooks');

        self::assertEquals(200, $response['status_code']);
        self::assertFalse(Arr::get($body, 'enabled'));
    }

    public function test_create_enabled_webhooks(): void
    {
        $this->addSuccessResponse();

        $response = $this->webhooks->create(
            new WebhookParams('https://link.com/webhook', 'webhook name', WebhookParams::ALL_ACTIVITIES, 'domain_id', true)
        );

        $body = $this->assertRequest('POST', '/v1/webhooks');

        self::assertEquals(200, $response['status_code']);
        self::assertTrue(Arr::get($body, 'enabled'));
    }

    /**
     * @dataProvider invalidUpdateParamsProvider
     */
    #[DataProvider('invalidUpdateParamsProvider')]
    public function test_update_rejects_invalid_params(string $id, string $url, string $name, array $events, string $exceptionMessage): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $this->webhooks->update($id, $url, $name, $events);
    }

    public static function invalidUpdateParamsProvider(): array
    {
        return [
            'missing id' => [
                '', 'https://link.com/webhook', 'Webhook name', WebhookParams::ALL_ACTIVITIES,
                'Webhook id is required.',
            ],
            'invalid url' => [
                'random_id', '', 'Webhook name', WebhookParams::ALL_ACTIVITIES,
                'Invalid URL.',
            ],
            'missing name' => [
                'random_id', 'https://link.com/webhook', '', WebhookParams::ALL_ACTIVITIES,
                'Webhook name is required.',
            ],
            'missing events' => [
                'random_id', 'https://link.com/webhook', 'Webhook name', [],
                'Webhook events are required.',
            ],
            'invalid events' => [
                'random_id', 'https://link.com/webhook', 'Webhook name', ['invalid_activity_1', 'invalid_activity_2'],
                'One or multiple invalid events.',
            ],
        ];
    }

    public function test_update_webhooks(): void
    {
        $this->addSuccessResponse();

        $response = $this->webhooks->update('random_id', 'https://link.com/webhook', 'Webhook name', [WebhookParams::ACTIVITY_OPENED, WebhookParams::ACTIVITY_CLICKED]);

        $body = $this->assertRequest('PUT', '/v1/webhooks/random_id');

        self::assertEquals(200, $response['status_code']);
        $this->assertBodyContains([
            'url' => 'https://link.com/webhook',
            'name' => 'Webhook name',
        ], $body);
        self::assertSame([WebhookParams::ACTIVITY_OPENED, WebhookParams::ACTIVITY_CLICKED], Arr::get($body, 'events'));
        $this->assertBodyExcludes(['enabled'], $body);
    }

    public function test_enable_webhooks(): void
    {
        $this->addSuccessResponse();

        $response = $this->webhooks->update('random_id', 'https://link.com/webhook', 'Webhook name', [WebhookParams::ACTIVITY_OPENED, WebhookParams::ACTIVITY_CLICKED], true);

        $body = $this->assertRequest('PUT', '/v1/webhooks/random_id');

        self::assertEquals(200, $response['status_code']);
        $this->assertBodyContains([
            'url' => 'https://link.com/webhook',
            'name' => 'Webhook name',
        ], $body);
        self::assertSame([WebhookParams::ACTIVITY_OPENED, WebhookParams::ACTIVITY_CLICKED], Arr::get($body, 'events'));
        self::assertTrue(Arr::get($body, 'enabled'));
    }

    public function test_disable_webhooks(): void
    {
        $this->addSuccessResponse();

        $response = $this->webhooks->update('random_id', 'https://link.com/webhook', 'Webhook name', [WebhookParams::ACTIVITY_OPENED, WebhookParams::ACTIVITY_CLICKED], false);

        $body = $this->assertRequest('PUT', '/v1/webhooks/random_id');

        self::assertEquals(200, $response['status_code']);
        $this->assertBodyContains([
            'url' => 'https://link.com/webhook',
            'name' => 'Webhook name',
        ], $body);
        self::assertSame([WebhookParams::ACTIVITY_OPENED, WebhookParams::ACTIVITY_CLICKED], Arr::get($body, 'events'));
        self::assertFalse(Arr::get($body, 'enabled'));
    }

    public function test_create_webhook_with_email_verification_events(): void
    {
        $this->addSuccessResponse();

        $response = $this->webhooks->create(
            new WebhookParams('https://link.com/webhook', 'webhook name', [WebhookParams::ACTIVITY_EMAIL_SINGLE_VERIFIED, WebhookParams::ACTIVITY_EMAIL_LIST_VERIFIED], 'domain_id')
        );

        $body = $this->assertRequest('POST', '/v1/webhooks');

        self::assertEquals(200, $response['status_code']);
        self::assertSame([WebhookParams::ACTIVITY_EMAIL_SINGLE_VERIFIED, WebhookParams::ACTIVITY_EMAIL_LIST_VERIFIED], Arr::get($body, 'events'));
    }

    public function test_create_webhook_with_bulk_email_completed_event(): void
    {
        $this->addSuccessResponse();

        $response = $this->webhooks->create(
            new WebhookParams('https://link.com/webhook', 'webhook name', [WebhookParams::ACTIVITY_BULK_EMAIL_COMPLETED], 'domain_id')
        );

        $body = $this->assertRequest('POST', '/v1/webhooks');

        self::assertEquals(200, $response['status_code']);
        self::assertSame([WebhookParams::ACTIVITY_BULK_EMAIL_COMPLETED], Arr::get($body, 'events'));
    }

    public function test_create_webhook_with_on_hold_events(): void
    {
        $this->addSuccessResponse();

        $response = $this->webhooks->create(
            new WebhookParams('https://link.com/webhook', 'webhook name', [WebhookParams::ACTIVITY_RECIPIENT_ON_HOLD_ADDED, WebhookParams::ACTIVITY_RECIPIENT_ON_HOLD_REMOVED], 'domain_id')
        );

        $body = $this->assertRequest('POST', '/v1/webhooks');

        self::assertEquals(200, $response['status_code']);
        self::assertSame([WebhookParams::ACTIVITY_RECIPIENT_ON_HOLD_ADDED, WebhookParams::ACTIVITY_RECIPIENT_ON_HOLD_REMOVED], Arr::get($body, 'events'));
    }

    public function test_create_webhook_with_version_and_editable()
    {
        $this->addSuccessResponse();

        $response = $this->webhooks->create(
            new WebhookParams('https://link.com/webhook', 'webhook name', [WebhookParams::ACTIVITY_SENT], 'domain_id', null, 2, true)
        );

        $body = $this->assertRequest('POST', '/v1/webhooks');

        self::assertEquals(200, $response['status_code']);
        self::assertSame(2, Arr::get($body, 'version'));
        self::assertTrue(Arr::get($body, 'editable'));
    }

    public function test_create_webhook_excludes_null_optional_fields()
    {
        $this->addSuccessResponse();

        $this->webhooks->create(
            new WebhookParams('https://link.com/webhook', 'webhook name', [WebhookParams::ACTIVITY_SENT], 'domain_id')
        );

        $body = $this->assertRequest('POST', '/v1/webhooks');

        $this->assertBodyExcludes(['enabled', 'version', 'editable'], $body);
    }

    public function test_get_webhooks_with_limit_and_page(): void
    {
        $this->addSuccessResponse();
        $response = $this->webhooks->get('domain_id', 25, 3);
        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);
        $this->assertQueryParams(['domain_id' => 'domain_id', 'limit' => '25', 'page' => '3'], $query);
        self::assertEquals(200, $response['status_code']);
    }

    public function test_get_webhooks_excludes_null_limit_and_page()
    {
        $this->addSuccessResponse();
        $this->webhooks->get('domain_id');
        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);
        self::assertArrayNotHasKey('limit', $query);
        self::assertArrayNotHasKey('page', $query);
    }

    public function test_update_webhook_with_version()
    {
        $this->addSuccessResponse();

        $response = $this->webhooks->update('random_id', 'https://link.com/webhook', 'Webhook name', [WebhookParams::ACTIVITY_SENT], null, 2);

        $body = $this->assertRequest('PUT', '/v1/webhooks/random_id');

        self::assertEquals(200, $response['status_code']);
        self::assertSame(2, Arr::get($body, 'version'));
    }

    public function test_update_webhook_with_only_id_excludes_optional_fields()
    {
        $this->addSuccessResponse();

        $response = $this->webhooks->update('random_id');

        $body = $this->assertRequest('PUT', '/v1/webhooks/random_id');

        self::assertEquals(200, $response['status_code']);
        $this->assertBodyExcludes(['url', 'name', 'events', 'enabled', 'version'], $body);
    }
}
