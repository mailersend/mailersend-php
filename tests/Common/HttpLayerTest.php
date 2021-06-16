<?php

namespace MailerSend\Tests\Common;

use Http\Mock\Client;
use MailerSend\Common\HttpLayer;
use MailerSend\Tests\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Tightenco\Collect\Support\Arr;

class HttpLayerTest extends TestCase
{
    protected HttpLayer $httpLayer;

    protected function setUp(): void
    {
        $this->client = new Client();

        $this->httpLayer = new HttpLayer([
            'api_key' => 'test'
        ], $this->client);
    }

    public function test_call_get(): void
    {
        $this->httpLayer->get('endpoint', ['test' => 'body']);

        $lastRequest = $this->client->getLastRequest();

        self::assertEquals('GET', $lastRequest->getMethod());
        self::assertEquals('endpoint', $lastRequest->getUri());
        self::assertEquals('{"test":"body"}', $lastRequest->getBody());
    }

    public function test_call_get_with_parameters(): void
    {
        $this->httpLayer->get('endpoint?key=value');

        $lastRequest = $this->client->getLastRequest();

        self::assertEquals('GET', $lastRequest->getMethod());
        self::assertEquals('endpoint?key=value', $lastRequest->getUri());
        parse_str($lastRequest->getUri()->getQuery(), $query);
        self::assertEquals('value', Arr::get($query, 'key'));
    }

    public function test_call_post(): void
    {
        $this->httpLayer->post('endpoint', ['test' => 'body']);

        $lastRequest = $this->client->getLastRequest();

        self::assertEquals('POST', $lastRequest->getMethod());
        self::assertEquals('endpoint', $lastRequest->getUri());
        self::assertEquals('{"test":"body"}', $lastRequest->getBody());
    }

    public function test_call_put(): void
    {
        $this->httpLayer->put('endpoint', ['test' => 'body']);

        $lastRequest = $this->client->getLastRequest();

        self::assertEquals('PUT', $lastRequest->getMethod());
        self::assertEquals('endpoint', $lastRequest->getUri());
        self::assertEquals('{"test":"body"}', $lastRequest->getBody());
    }

    public function test_call_delete(): void
    {
        $this->httpLayer->delete('endpoint', ['test' => 'body']);

        $lastRequest = $this->client->getLastRequest();

        self::assertEquals('DELETE', $lastRequest->getMethod());
        self::assertEquals('endpoint', $lastRequest->getUri());
        self::assertEquals('{"test":"body"}', $lastRequest->getBody());
    }

    public function test_call_request_without_body(): void
    {
        $this->httpLayer->request('GET', 'endpoint');

        $lastRequest = $this->client->getLastRequest();

        self::assertEquals('GET', $lastRequest->getMethod());
        self::assertEquals('endpoint', $lastRequest->getUri());
        self::assertEquals('', $lastRequest->getBody());
    }

    public function test_call_request_with_body(): void
    {
        $this->httpLayer->request('PATCH', 'endpoint', 'body');

        $lastRequest = $this->client->getLastRequest();

        self::assertEquals('PATCH', $lastRequest->getMethod());
        self::assertEquals('endpoint', $lastRequest->getUri());
        self::assertEquals('body', $lastRequest->getBody());
    }

    /**
     * @dataProvider body_provider
     */
    public function test_http_layer_builds_body($body, string $expected): void
    {
        /** @var StreamInterface $buildBodyResult */
        $buildBodyResult = $this->callMethod($this->httpLayer, 'buildBody', [$body]);

        self::assertEquals($expected, $buildBodyResult->getContents());
    }

    public function body_provider(): array
    {
        return [
            [
                ['builds' => 'json'],
                '{"builds":"json"}',
            ],
            [
                'builds_text',
                'builds_text'
            ]
        ];
    }

    public function test_http_layer_builds_response_json(): void
    {
        $responseBody = $this->createMock(StreamInterface::class);
        $responseBody->method('getContents')->willReturn('{"test":"array"}');

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('hasHeader')->with('Content-Type')->willReturn(true);
        $response->method('getHeader')->with('Content-Type')->willReturn(['application/json']);
        $response->method('getHeaders')->willReturn([]);
        $response->method('getBody')->willReturn($responseBody);

        /** @var array $buildResponseResult */
        $buildResponseResult = $this->callMethod($this->httpLayer, 'buildResponse', [$response]);

        self::assertEquals(200, Arr::get($buildResponseResult, 'status_code'));
        self::assertEquals([], Arr::get($buildResponseResult, 'headers'));
        self::assertEquals(['test' => 'array'], Arr::get($buildResponseResult, 'body'));
    }

    public function test_http_layer_builds_response_text(): void
    {
        $responseBody = $this->createMock(StreamInterface::class);
        $responseBody->method('getContents')->willReturn('test');

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('hasHeader')->with('Content-Type')->willReturn(true);
        $response->method('getHeader')->with('Content-Type')->willReturn(['text/plain']);
        $response->method('getHeaders')->willReturn([]);
        $response->method('getBody')->willReturn($responseBody);

        /** @var array $buildResponseResult */
        $buildResponseResult = $this->callMethod($this->httpLayer, 'buildResponse', [$response]);

        self::assertEquals(200, Arr::get($buildResponseResult, 'status_code'));
        self::assertEquals([], Arr::get($buildResponseResult, 'headers'));
        self::assertEquals('test', Arr::get($buildResponseResult, 'body'));
    }
}
