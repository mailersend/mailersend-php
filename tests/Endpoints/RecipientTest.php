<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\Recipient;
use MailerSend\Tests\TestCase;
use Psr\Http\Message\ResponseInterface;
use Tightenco\Collect\Support\Arr;

class RecipientTest extends TestCase
{
    protected Recipient $recipients;
    protected ResponseInterface $defaultResponse;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = new Client();

        $this->recipients = new Recipient(new HttpLayer(self::OPTIONS, $this->client), self::OPTIONS);
        $this->defaultResponse = $this->createMock(ResponseInterface::class);
        $this->defaultResponse->method('getStatusCode')->willReturn(200);
    }

    public function test_get_recipients_min_limit_is_validated()
    {
        $this->expectExceptionMessage('Minimum limit is ' . Recipient::MIN_LIMIT . '.');

        $this->recipients->get('domain_id', 9);
    }

    public function test_get_recipients_max_limit_is_validated()
    {
        $this->expectExceptionMessage('Maximum limit is ' . Recipient::MAX_LIMIT . '.');

        $this->recipients->get('domain_id', 101);
    }

    public function test_get_recipients()
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $this->client->addResponse($response);

        $response = $this->recipients->get(null, 30, 2);

        $request = $this->client->getLastRequest();
        $request_body = json_decode((string) $request->getBody(), true);

        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/v1/recipients', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);

        self::assertSame(30, Arr::get($request_body, 'limit'));
        self::assertSame(2, Arr::get($request_body, 'page'));
        self::assertNull(Arr::get($request_body, 'domain_id'));
    }

    public function test_get_recipients_with_domain_filter()
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $this->client->addResponse($response);

        $response = $this->recipients->get('domain_id', 30, 2);

        $request = $this->client->getLastRequest();
        $request_body = json_decode((string) $request->getBody(), true);

        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/v1/recipients', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);

        self::assertSame(30, Arr::get($request_body, 'limit'));
        self::assertSame(2, Arr::get($request_body, 'page'));
        self::assertSame('domain_id', Arr::get($request_body, 'domain_id'));
    }

    public function test_delete_recipient()
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $this->client->addResponse($response);

        $response = $this->recipients->delete('random_id');

        $request = $this->client->getLastRequest();

        self::assertEquals('DELETE', $request->getMethod());
        self::assertEquals('/v1/recipients/random_id', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);
    }
}
