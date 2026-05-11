<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\ApiQuota;
use MailerSend\Tests\TestCase;

class ApiQuotaTest extends TestCase
{
    protected ApiQuota $apiQuota;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = new Client();
        $this->apiQuota = new ApiQuota(new HttpLayer(self::OPTIONS, $this->client), self::OPTIONS);
    }

    public function test_get_uses_correct_method_and_path(): void
    {
        $this->addSuccessResponse();
        $this->apiQuota->get();
        $this->assertRequest('GET', '/v1/api-quota');
    }

    public function test_get_forwards_status_code(): void
    {
        $this->addSuccessResponse(200);
        $response = $this->apiQuota->get();
        self::assertEquals(200, $response['status_code']);
    }

    public function test_get_sends_empty_body(): void
    {
        $this->addSuccessResponse();
        $this->apiQuota->get();
        $body = $this->assertRequest('GET', '/v1/api-quota');
        self::assertEmpty($body);
    }
}
