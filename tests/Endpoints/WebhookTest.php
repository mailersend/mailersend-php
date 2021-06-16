<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\Webhook;
use MailerSend\Helpers\Builder\WebhookParams;
use MailerSend\Tests\TestCase;
use Psr\Http\Message\ResponseInterface;
use Tightenco\Collect\Support\Arr;

class WebhookTest extends TestCase
{
    protected Webhook $webhooks;
    protected ResponseInterface $defaultResponse;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = new Client();

        $this->webhooks = new Webhook(new HttpLayer(self::OPTIONS, $this->client), self::OPTIONS);
        $this->defaultResponse = $this->createMock(ResponseInterface::class);
        $this->defaultResponse->method('getStatusCode')->willReturn(200);
    }

    public function test_get_webhooks_domain_id_is_required()
    {
        $this->expectExceptionMessage('Domain id is required.');

        $this->webhooks->get('');
    }

    public function test_find_webhook_is_validated()
    {
        $this->expectExceptionMessage('Webhook id is required.');

        $this->webhooks->find('');
    }

    public function test_delete_webhook_is_validated()
    {
        $this->expectExceptionMessage('Webhook id is required.');

        $this->webhooks->delete('');
    }

    public function test_get_webhooks()
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $this->client->addResponse($response);

        $response = $this->webhooks->get('domain_id');

        $request = $this->client->getLastRequest();
        $request_body = json_decode((string) $request->getBody(), true);

        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/v1/webhooks', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);

        self::assertSame('domain_id', Arr::get($request_body, 'domain_id'));
    }

    public function test_find_webhook()
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $this->client->addResponse($response);

        $response = $this->webhooks->find('webhook_id');

        $request = $this->client->getLastRequest();

        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/v1/webhooks/webhook_id', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);
    }

    public function test_delete_webhook()
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $this->client->addResponse($response);

        $response = $this->webhooks->delete('webhook_id');

        $request = $this->client->getLastRequest();

        self::assertEquals('DELETE', $request->getMethod());
        self::assertEquals('/v1/webhooks/webhook_id', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);
    }

    public function test_url_is_validated_when_creating_webhooks()
    {
        $this->expectExceptionMessage('Invalid URL.');

        $this->webhooks->create(
            new WebhookParams('invalid_url', 'Webhook name', WebhookParams::ALL_ACTIVITIES, 'domain_id')
        );
    }

    public function test_name_is_required_when_creating_webhooks()
    {
        $this->expectExceptionMessage('Webhook name is required.');

        $this->webhooks->create(
            new WebhookParams('https://link.com/webhook', '', WebhookParams::ALL_ACTIVITIES, 'domain_id')
        );
    }

    public function test_events_are_required_when_creating_webhooks()
    {
        $this->expectExceptionMessage('Webhook events are required.');

        $this->webhooks->create(
            new WebhookParams('https://link.com/webhook', 'webhook name', [], 'domain_id')
        );
    }

    public function test_events_are_validated_when_creating_webhooks()
    {
        $this->expectExceptionMessage('One or multiple invalid events.');

        $this->webhooks->create(
            new WebhookParams('https://link.com/webhook', 'webhook name', ['invalid event'], 'domain_id')
        );
    }

    public function test_domain_id_is_required_when_creating_webhooks()
    {
        $this->expectExceptionMessage('Webhook domain id is required.');

        $this->webhooks->create(
            new WebhookParams('https://link.com/webhook', 'webhook name', WebhookParams::ALL_ACTIVITIES, '')
        );
    }

    public function test_create_webhooks()
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $this->client->addResponse($response);

        $response = $this->webhooks->create(
            new WebhookParams('https://link.com/webhook', 'webhook name', WebhookParams::ALL_ACTIVITIES, 'domain_id')
        );

        $request = $this->client->getLastRequest();
        $request_body = json_decode((string) $request->getBody(), true);

        self::assertEquals('POST', $request->getMethod());
        self::assertEquals('/v1/webhooks', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);

        self::assertSame('https://link.com/webhook', Arr::get($request_body, 'url'));
        self::assertSame('webhook name', Arr::get($request_body, 'name'));
        self::assertSame(WebhookParams::ALL_ACTIVITIES, Arr::get($request_body, 'events'));
        self::assertSame('domain_id', Arr::get($request_body, 'domain_id'));
        self::assertNull(Arr::get($request_body, 'enabled'));
    }

    public function test_create_disabled_webhooks()
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $this->client->addResponse($response);

        $response = $this->webhooks->create(
            new WebhookParams('https://link.com/webhook', 'webhook name', WebhookParams::ALL_ACTIVITIES, 'domain_id', false)
        );

        $request = $this->client->getLastRequest();
        $request_body = json_decode((string) $request->getBody(), true);

        self::assertEquals('POST', $request->getMethod());
        self::assertEquals('/v1/webhooks', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);

        self::assertSame('https://link.com/webhook', Arr::get($request_body, 'url'));
        self::assertSame('webhook name', Arr::get($request_body, 'name'));
        self::assertSame(WebhookParams::ALL_ACTIVITIES, Arr::get($request_body, 'events'));
        self::assertSame('domain_id', Arr::get($request_body, 'domain_id'));
        self::assertEquals(false, Arr::get($request_body, 'enabled'));
    }

    public function test_create_enabled_webhooks()
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $this->client->addResponse($response);

        $response = $this->webhooks->create(
            new WebhookParams('https://link.com/webhook', 'webhook name', WebhookParams::ALL_ACTIVITIES, 'domain_id', true)
        );

        $request = $this->client->getLastRequest();
        $request_body = json_decode((string) $request->getBody(), true);

        self::assertEquals('POST', $request->getMethod());
        self::assertEquals('/v1/webhooks', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);

        self::assertSame('https://link.com/webhook', Arr::get($request_body, 'url'));
        self::assertSame('webhook name', Arr::get($request_body, 'name'));
        self::assertSame(WebhookParams::ALL_ACTIVITIES, Arr::get($request_body, 'events'));
        self::assertSame('domain_id', Arr::get($request_body, 'domain_id'));
        self::assertEquals(true, Arr::get($request_body, 'enabled'));
    }

    public function test_update_webhook_requires_id()
    {
        $this->expectExceptionMessage('Webhook id is required.');

        $this->webhooks->update('', 'https://link.com/webhook', 'Webhook name', WebhookParams::ALL_ACTIVITIES);
    }

    public function test_update_webhook_requires_url()
    {
        $this->expectExceptionMessage('Invalid URL.');

        $this->webhooks->update('random_id', '', 'Webhook name', WebhookParams::ALL_ACTIVITIES);
    }

    public function test_update_webhook_requires_name()
    {
        $this->expectExceptionMessage('Webhook name is required.');

        $this->webhooks->update('random_id', 'https://link.com/webhook', '', WebhookParams::ALL_ACTIVITIES);
    }

    public function test_update_webhook_requires_events()
    {
        $this->expectExceptionMessage('Webhook events are required.');

        $this->webhooks->update('random_id', 'https://link.com/webhook', 'Webhook name', []);
    }

    public function test_update_webhook_events_are_validated()
    {
        $this->expectExceptionMessage('One or multiple invalid events.');

        $this->webhooks->update('random_id', 'https://link.com/webhook', 'Webhook name', ['invalid_activity_1', 'invalid_activity_2']);
    }

    public function test_update_webhooks()
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $this->client->addResponse($response);

        $response = $this->webhooks->update('random_id', 'https://link.com/webhook', 'Webhook name', [WebhookParams::ACTIVITY_OPENED, WebhookParams::ACTIVITY_CLICKED]);

        $request = $this->client->getLastRequest();
        $request_body = json_decode((string) $request->getBody(), true);

        self::assertEquals('PUT', $request->getMethod());
        self::assertEquals('/v1/webhooks/random_id', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);

        self::assertSame('https://link.com/webhook', Arr::get($request_body, 'url'));
        self::assertSame('Webhook name', Arr::get($request_body, 'name'));
        self::assertSame([WebhookParams::ACTIVITY_OPENED, WebhookParams::ACTIVITY_CLICKED], Arr::get($request_body, 'events'));
        self::assertNull(Arr::get($request_body, 'enabled'));
    }

    public function test_enable_webhooks()
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $this->client->addResponse($response);

        $response = $this->webhooks->update('random_id', 'https://link.com/webhook', 'Webhook name', [WebhookParams::ACTIVITY_OPENED, WebhookParams::ACTIVITY_CLICKED], true);

        $request = $this->client->getLastRequest();
        $request_body = json_decode((string) $request->getBody(), true);

        self::assertEquals('PUT', $request->getMethod());
        self::assertEquals('/v1/webhooks/random_id', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);

        self::assertSame('https://link.com/webhook', Arr::get($request_body, 'url'));
        self::assertSame('Webhook name', Arr::get($request_body, 'name'));
        self::assertSame([WebhookParams::ACTIVITY_OPENED, WebhookParams::ACTIVITY_CLICKED], Arr::get($request_body, 'events'));
        self::assertEquals(true, Arr::get($request_body, 'enabled'));
    }

    public function test_disable_webhooks()
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $this->client->addResponse($response);

        $response = $this->webhooks->update('random_id', 'https://link.com/webhook', 'Webhook name', [WebhookParams::ACTIVITY_OPENED, WebhookParams::ACTIVITY_CLICKED], false);

        $request = $this->client->getLastRequest();
        $request_body = json_decode((string) $request->getBody(), true);

        self::assertEquals('PUT', $request->getMethod());
        self::assertEquals('/v1/webhooks/random_id', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);

        self::assertSame('https://link.com/webhook', Arr::get($request_body, 'url'));
        self::assertSame('Webhook name', Arr::get($request_body, 'name'));
        self::assertSame([WebhookParams::ACTIVITY_OPENED, WebhookParams::ACTIVITY_CLICKED], Arr::get($request_body, 'events'));
        self::assertEquals(false, Arr::get($request_body, 'enabled'));
    }
}
