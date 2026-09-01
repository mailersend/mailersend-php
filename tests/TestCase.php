<?php

namespace MailerSend\Tests;

use Http\Mock\Client;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class TestCase extends \PHPUnit\Framework\TestCase
{
    protected Client $client;

    public const OPTIONS = [
        'host' => 'api.mailersend.com',
        'protocol' => 'https',
        'api_path' => 'v1',
        'api_key' => 'api-key'
    ];

    protected function addSuccessResponse(int $statusCode = 200): void
    {
        $response = $this->createStub(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn($statusCode);
        $this->client->addResponse($response);
    }

    protected function addErrorResponse(int $statusCode, string $message): void
    {
        // MailerSendValidationException constructor requires an `errors` key in the body.
        $payload = ['message' => $message, 'errors' => []];

        $body = $this->createStub(StreamInterface::class);
        $body->method('getContents')->willReturn(json_encode($payload));

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn($statusCode);
        $response->method('getBody')->willReturn($body);
        $response->method('getHeaders')->willReturn([]);
        $this->client->addResponse($response);
    }

    protected function lastRequestBody(): array
    {
        $request = $this->client->getLastRequest();
        return json_decode((string) $request->getBody(), true) ?? [];
    }

    /**
     * Asserts the last request used the given HTTP method and URI path, then
     * returns the decoded request body for further assertions.
     */
    protected function assertRequest(string $method, string $path): array
    {
        $request = $this->client->getLastRequest();
        self::assertEquals($method, $request->getMethod());
        self::assertEquals($path, $request->getUri()->getPath());
        return json_decode((string) $request->getBody(), true) ?? [];
    }

    /**
     * Asserts that each key in $expected exists in $query with the given value.
     * Pass the result of parse_str() on the URI query string.
     */
    protected function assertQueryParams(array $expected, array $query): void
    {
        foreach ($expected as $key => $value) {
            self::assertEquals($value, $query[$key] ?? null, "Query param '$key' mismatch.");
        }
    }

    /**
     * Asserts that each key/value pair in $expected is present in $actual.
     */
    protected function assertBodyContains(array $expected, array $actual): void
    {
        foreach ($expected as $key => $value) {
            self::assertEquals($value, $actual[$key] ?? null, "Body key '$key' mismatch.");
        }
    }

    /**
     * Asserts that none of the given keys are present in $actual.
     */
    protected function assertBodyExcludes(array $keys, array $actual): void
    {
        foreach ($keys as $key) {
            self::assertArrayNotHasKey($key, $actual, "Body must not contain key '$key'.");
        }
    }

    /**
     * @throws \RuntimeException
     * @throws \ReflectionException
     */
    protected function callMethod($object, string $method, array $parameters = [])
    {
        try {
            $className = get_class($object);
            $reflection = new \ReflectionClass($className);
        } catch (\ReflectionException $e) {
            throw new \RuntimeException($e->getMessage());
        }

        $method = $reflection->getMethod($method);
        if (PHP_VERSION_ID < 80100) {
            $method->setAccessible(true);
        }

        return $method->invokeArgs($object, $parameters);
    }
}
