<?php

namespace MailerSend\Tests;

use Http\Mock\Client;

class TestCase extends \PHPUnit\Framework\TestCase
{
    protected Client $client;

    public const OPTIONS = [
        'host' => 'api.mailersend.com',
        'protocol' => 'https',
        'api_path' => 'v1',
        'api_key' => 'api-key'
    ];

    protected function lastRequestBody(): array
    {
        $request = $this->client->getLastRequest();
        return json_decode((string) $request->getBody(), true);
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
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
