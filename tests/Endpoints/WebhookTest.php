<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\Webhook;
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
}
