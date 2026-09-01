<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\SpamComplaint;
use MailerSend\Helpers\Builder\SuppressionParams;
use MailerSend\Tests\TestCase;
use Psr\Http\Message\ResponseInterface;

class SpamComplaintTest extends TestCase
{
    protected SpamComplaint $spamComplaint;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = new Client();

        $this->spamComplaint = new SpamComplaint(new HttpLayer(self::OPTIONS, $this->client), self::OPTIONS);
    }

    public function test_get_all_uses_spam_complaints_path(): void
    {
        $this->addSuccessResponse();
        $this->spamComplaint->getAll();
        $request = $this->client->getLastRequest();
        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/v1/suppressions/spam-complaints', $request->getUri()->getPath());
    }

    public function test_create_uses_spam_complaints_path(): void
    {
        $this->addSuccessResponse();
        $params = (new SuppressionParams())
            ->setDomainId('domain_id')
            ->setRecipients(['recipient']);
        $this->spamComplaint->create($params);
        $request = $this->client->getLastRequest();
        self::assertEquals('POST', $request->getMethod());
        self::assertEquals('/v1/suppressions/spam-complaints', $request->getUri()->getPath());
    }

    public function test_delete_uses_spam_complaints_path(): void
    {
        $this->addSuccessResponse();
        $this->spamComplaint->delete(['id']);
        $request = $this->client->getLastRequest();
        self::assertEquals('DELETE', $request->getMethod());
        self::assertEquals('/v1/suppressions/spam-complaints', $request->getUri()->getPath());
    }
}
