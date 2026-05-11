<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\HardBounce;
use MailerSend\Helpers\Builder\SuppressionParams;
use MailerSend\Tests\TestCase;

class HardBounceTest extends TestCase
{
    protected HardBounce $hardBounce;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = new Client();

        $this->hardBounce = new HardBounce(new HttpLayer(self::OPTIONS, $this->client), self::OPTIONS);
    }

    public function test_get_all_uses_hard_bounces_path(): void
    {
        $this->addSuccessResponse();
        $this->hardBounce->getAll();
        $request = $this->client->getLastRequest();
        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/v1/suppressions/hard-bounces', $request->getUri()->getPath());
    }

    public function test_create_uses_hard_bounces_path(): void
    {
        $this->addSuccessResponse();
        $params = (new SuppressionParams())
            ->setDomainId('domain_id')
            ->setRecipients(['recipient@mailersend.com']);
        $this->hardBounce->create($params);
        $request = $this->client->getLastRequest();
        self::assertEquals('POST', $request->getMethod());
        self::assertEquals('/v1/suppressions/hard-bounces', $request->getUri()->getPath());
    }

    public function test_delete_uses_hard_bounces_path(): void
    {
        $this->addSuccessResponse();
        $this->hardBounce->delete(['id_1']);
        $request = $this->client->getLastRequest();
        self::assertEquals('DELETE', $request->getMethod());
        self::assertEquals('/v1/suppressions/hard-bounces', $request->getUri()->getPath());
    }
}
