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

    public function test_get_messages_min_limit_is_validated()
    {
        $this->expectExceptionMessage('Minimum limit is ' . Recipient::MIN_LIMIT . '.');

        $this->recipients->get(9);
    }

    public function test_get_messages_max_limit_is_validated()
    {
        $this->expectExceptionMessage('Maximum limit is ' . Recipient::MAX_LIMIT . '.');

        $this->recipients->get(101);
    }

    public function test_get_messages()
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $this->client->addResponse($response);

        $response = $this->recipients->get(30, 2);

        $request = $this->client->getLastRequest();
        $request_body = json_decode((string) $request->getBody(), true);

        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/v1/recipients', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);

        self::assertSame(30, Arr::get($request_body, 'limit'));
        self::assertSame(2, Arr::get($request_body, 'page'));
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
