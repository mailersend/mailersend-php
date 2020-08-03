<?php

namespace MailerSend\Tests;

use Http\Mock\Client;

class TestCase extends \PHPUnit\Framework\TestCase
{
    protected Client $client;

    public const OPTIONS = [
        'host' => 'api.mailersend.com',
        'protocol' => 'https',
        'version' => 'v1',
        'port' => 443,
        'api_key' => 'api-key'
    ];

    protected function lastRequestBody(): array
    {
        $request = $this->client->getLastRequest();
        return json_decode((string) $request->getBody(), true);
    }
}