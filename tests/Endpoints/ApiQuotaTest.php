<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\ApiQuota;
use MailerSend\Tests\TestCase;
use Psr\Http\Message\ResponseInterface;

class ApiQuotaTest extends TestCase
{
    protected ApiQuota $apiQuota;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = new Client();
        $this->apiQuota = new ApiQuota(new HttpLayer(self::OPTIONS, $this->client), self::OPTIONS);
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function test_get(): void
    {
        $response = $this->createStub(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $this->client->addResponse($response);

        $response = $this->apiQuota->get();

        $request = $this->client->getLastRequest();

        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/v1/api-quota', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);
    }
}
