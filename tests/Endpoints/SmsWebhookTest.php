<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\SmsWebhook;
use MailerSend\Helpers\Builder\SmsWebhookParams;
use MailerSend\Helpers\Builder\WebhookParams;
use MailerSend\Tests\TestCase;
use Psr\Http\Message\ResponseInterface;
use Tightenco\Collect\Support\Arr;

class SmsWebhookTest extends TestCase
{
    protected SmsWebhook $smsWebhook;
    protected ResponseInterface $defaultResponse;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = new Client();

        $this->smsWebhook = new SmsWebhook(new HttpLayer(self::OPTIONS, $this->client), self::OPTIONS);
        $this->defaultResponse = $this->createMock(ResponseInterface::class);
        $this->defaultResponse->method('getStatusCode')->willReturn(200);
    }

    public function test_get_webhooks_sms_number_id_is_required()
    {
        $this->expectExceptionMessage('SMS number id is required.');

        $this->smsWebhook->get('');
    }

    public function test_find_webhook_is_validated()
    {
        $this->expectExceptionMessage('SMS webhook id is required.');

        $this->smsWebhook->find('');
    }

    public function test_delete_webhook_is_validated()
    {
        $this->expectExceptionMessage('SMS webhook id is required.');

        $this->smsWebhook->delete('');
    }

    public function test_get_sms_webhooks()
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $this->client->addResponse($response);

        $response = $this->smsWebhook->get('hashed_sms_number_id');

        $request = $this->client->getLastRequest();
        $request_body = json_decode((string) $request->getBody(), true);

        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/v1/sms-webhooks', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);

        self::assertSame('hashed_sms_number_id', Arr::get($request_body, 'sms_number_id'));
    }

    public function test_find_webhook()
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $this->client->addResponse($response);

        $response = $this->smsWebhook->find('hashed_sms_webhook_id');

        $request = $this->client->getLastRequest();

        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/v1/sms-webhooks/hashed_sms_webhook_id', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);
    }

    public function test_delete_sms_webhook()
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $this->client->addResponse($response);

        $response = $this->smsWebhook->delete('hashed_sms_webhook_id');

        $request = $this->client->getLastRequest();

        self::assertEquals('DELETE', $request->getMethod());
        self::assertEquals('/v1/sms-webhooks/hashed_sms_webhook_id', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);
    }

    public function test_url_is_validated_when_creating_sms_webhooks()
    {
        $this->expectExceptionMessage('Invalid URL.');

        $this->smsWebhook->create(
            new SmsWebhookParams('invalid_url', 'SMS webhook name', SmsWebhookParams::ALL_ACTIVITIES, 'hashed_sms_number_id')
        );
    }

    public function test_name_is_required_when_creating_sms_webhooks()
    {
        $this->expectExceptionMessage('Webhook name is required.');

        $this->smsWebhook->create(
            new SmsWebhookParams('https://link.com/webhook', '', SmsWebhookParams::ALL_ACTIVITIES, 'hashed_sms_number_id')
        );
    }

    public function test_events_are_required_when_creating_sms_webhooks()
    {
        $this->expectExceptionMessage('Webhook events are required.');

        $this->smsWebhook->create(
            new SmsWebhookParams('https://link.com/webhook', 'webhook name', [], 'hashed_sms_number_id')
        );
    }

    public function test_events_are_validated_when_creating_sms_webhooks()
    {
        $this->expectExceptionMessage('One or multiple invalid events.');

        $this->smsWebhook->create(
            new SmsWebhookParams('https://link.com/webhook', 'webhook name', ['invalid event'], 'hashed_sms_number_id')
        );
    }

    public function test_domain_id_is_required_when_creating_sms_webhooks()
    {
        $this->expectExceptionMessage('SMS number id is required.');

        $this->smsWebhook->create(
            new SmsWebhookParams('https://link.com/webhook', 'webhook name', SmsWebhookParams::ALL_ACTIVITIES, '')
        );
    }

    public function test_create_sms_webhooks()
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $this->client->addResponse($response);

        $response = $this->smsWebhook->create(
            new SmsWebhookParams('https://link.com/webhook', 'webhook name', SmsWebhookParams::ALL_ACTIVITIES, 'hashed_sms_number_id')
        );

        $request = $this->client->getLastRequest();
        $request_body = json_decode((string) $request->getBody(), true);

        self::assertEquals('POST', $request->getMethod());
        self::assertEquals('/v1/sms-webhooks', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);

        self::assertSame('https://link.com/webhook', Arr::get($request_body, 'url'));
        self::assertSame('webhook name', Arr::get($request_body, 'name'));
        self::assertSame(SmsWebhookParams::ALL_ACTIVITIES, Arr::get($request_body, 'events'));
        self::assertSame('hashed_sms_number_id', Arr::get($request_body, 'sms_number_id'));
        self::assertNull(Arr::get($request_body, 'enabled'));
    }

    public function test_create_disabled_sms_webhooks()
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $this->client->addResponse($response);

        $response = $this->smsWebhook->create(
            new SmsWebhookParams('https://link.com/webhook', 'webhook name', SmsWebhookParams::ALL_ACTIVITIES, 'hashed_sms_number_id', false)
        );

        $request = $this->client->getLastRequest();
        $request_body = json_decode((string) $request->getBody(), true);

        self::assertEquals('POST', $request->getMethod());
        self::assertEquals('/v1/sms-webhooks', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);

        self::assertSame('https://link.com/webhook', Arr::get($request_body, 'url'));
        self::assertSame('webhook name', Arr::get($request_body, 'name'));
        self::assertSame(SmsWebhookParams::ALL_ACTIVITIES, Arr::get($request_body, 'events'));
        self::assertSame('hashed_sms_number_id', Arr::get($request_body, 'sms_number_id'));
        self::assertEquals(false, Arr::get($request_body, 'enabled'));
    }

    public function test_create_enabled_sms_webhooks()
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $this->client->addResponse($response);

        $response = $this->smsWebhook->create(
            new SmsWebhookParams('https://link.com/webhook', 'webhook name', SmsWebhookParams::ALL_ACTIVITIES, 'hashed_sms_number_id', true)
        );

        $request = $this->client->getLastRequest();
        $request_body = json_decode((string) $request->getBody(), true);

        self::assertEquals('POST', $request->getMethod());
        self::assertEquals('/v1/sms-webhooks', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);

        self::assertSame('https://link.com/webhook', Arr::get($request_body, 'url'));
        self::assertSame('webhook name', Arr::get($request_body, 'name'));
        self::assertSame(SmsWebhookParams::ALL_ACTIVITIES, Arr::get($request_body, 'events'));
        self::assertSame('hashed_sms_number_id', Arr::get($request_body, 'sms_number_id'));
        self::assertEquals(true, Arr::get($request_body, 'enabled'));
    }

    public function test_update_sms_webhook_requires_id()
    {
        $this->expectExceptionMessage('SMS webhook id is required.');

        $this->smsWebhook->update(
            '',
            new SmsWebhookParams(
                'https://link.com/webhook',
                'webhook name',
                SmsWebhookParams::ALL_ACTIVITIES
            )
        );
    }

    public function test_update_sms_webhooks()
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $this->client->addResponse($response);

        $response = $this->smsWebhook->update(
            'random_id',
            new SmsWebhookParams('https://link.com/webhook', 'Webhook name', [SmsWebhookParams::SMS_FAILED, SmsWebhookParams::SMS_SENT])
        );

        $request = $this->client->getLastRequest();
        $request_body = json_decode((string) $request->getBody(), true);

        self::assertEquals('PUT', $request->getMethod());
        self::assertEquals('/v1/sms-webhooks/random_id', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);

        self::assertSame('https://link.com/webhook', Arr::get($request_body, 'url'));
        self::assertSame('Webhook name', Arr::get($request_body, 'name'));
        self::assertSame([SmsWebhookParams::SMS_FAILED, SmsWebhookParams::SMS_SENT], Arr::get($request_body, 'events'));
        self::assertNull(Arr::get($request_body, 'enabled'));
    }

    public function test_enable_sms_webhooks()
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $this->client->addResponse($response);

        $smsWebhookParams = new SmsWebhookParams();
        $smsWebhookParams->setEnabled(true);

        $response = $this->smsWebhook->update('random_id', $smsWebhookParams);

        $request = $this->client->getLastRequest();
        $request_body = json_decode((string) $request->getBody(), true);

        self::assertEquals('PUT', $request->getMethod());
        self::assertEquals('/v1/sms-webhooks/random_id', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);

        self::assertEquals(true, Arr::get($request_body, 'enabled'));
    }

    public function test_disable_sms_webhooks()
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $this->client->addResponse($response);

        $smsWebhookParams = new SmsWebhookParams();
        $smsWebhookParams->setEnabled(false);

        $response = $this->smsWebhook->update('random_id', $smsWebhookParams);

        $request = $this->client->getLastRequest();
        $request_body = json_decode((string) $request->getBody(), true);

        self::assertEquals('PUT', $request->getMethod());
        self::assertEquals('/v1/sms-webhooks/random_id', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);

        self::assertEquals(false, Arr::get($request_body, 'enabled'));
    }
}
