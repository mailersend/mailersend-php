<?php

namespace MailerSend\Tests\Endpoints;

use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\Strategy\MockClientStrategy;
use Http\Mock\Client;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\Email;
use MailerSend\Tests\TestCase;

class EmailTest extends TestCase
{
    protected Email $email;
    protected Client $client;

    public function setUp(): void
    {
        parent::setUp();

        HttpClientDiscovery::prependStrategy(MockClientStrategy::class);

        $options = [
            'host' => 'api.mailersend.com',
            'protocol' => 'https',
            'version' => 'v1',
            'port' => 443,
            'api_key' => 'api-key'
        ];

        $this->client = new Client();

        $this->email = new Email(new HttpLayer($options), $options);
    }

    public function testSend()
    {
        $response = $this->email->send('test', 'test', ['test' => 'test'], 'test');

        $this->assertEquals($response['status_code'], 200);
    }
}
