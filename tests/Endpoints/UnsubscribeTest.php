<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\Unsubscribe;
use MailerSend\Helpers\Builder\SuppressionParams;
use MailerSend\Tests\TestCase;

class UnsubscribeTest extends TestCase
{
    protected Unsubscribe $unsubscribe;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = new Client();

        $this->unsubscribe = new Unsubscribe(new HttpLayer(self::OPTIONS, $this->client), self::OPTIONS);
    }

    public function test_get_all_uses_unsubscribes_path(): void
    {
        $this->addSuccessResponse();
        $this->unsubscribe->getAll();
        $request = $this->client->getLastRequest();
        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/v1/suppressions/unsubscribes', $request->getUri()->getPath());
    }

    public function test_create_uses_unsubscribes_path(): void
    {
        $this->addSuccessResponse();
        $params = (new SuppressionParams())
            ->setDomainId('domain_id')
            ->setRecipients(['recipient']);
        $this->unsubscribe->create($params);
        $request = $this->client->getLastRequest();
        self::assertEquals('POST', $request->getMethod());
        self::assertEquals('/v1/suppressions/unsubscribes', $request->getUri()->getPath());
    }

    public function test_delete_uses_unsubscribes_path(): void
    {
        $this->addSuccessResponse();
        $this->unsubscribe->delete(['id']);
        $request = $this->client->getLastRequest();
        self::assertEquals('DELETE', $request->getMethod());
        self::assertEquals('/v1/suppressions/unsubscribes', $request->getUri()->getPath());
    }
}
