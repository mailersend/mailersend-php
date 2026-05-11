<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\Message;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Http\Message\ResponseInterface;

class MessageTest extends TestCase
{
    protected Message $messages;
    protected ResponseInterface $defaultResponse;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = new Client();

        $this->messages = new Message(new HttpLayer(self::OPTIONS, $this->client), self::OPTIONS);
        $this->defaultResponse = $this->createStub(ResponseInterface::class);
        $this->defaultResponse->method('getStatusCode')->willReturn(200);
    }

    public function test_get_sends_correct_method_and_path(): void
    {
        $this->addSuccessResponse();

        $response = $this->messages->get();

        $this->assertRequest('GET', '/v1/messages');
        self::assertEquals(200, $response['status_code']);
    }

    public function test_get_with_limit_and_page(): void
    {
        $this->addSuccessResponse();

        $this->messages->get(30, 2);

        $body = $this->assertRequest('GET', '/v1/messages');
        $this->assertBodyContains(['limit' => 30, 'page' => 2], $body);
    }

    public function test_get_with_domain_id(): void
    {
        $this->addSuccessResponse();

        $this->messages->get(25, null, 'domain-id');

        $body = $this->assertRequest('GET', '/v1/messages');
        $this->assertBodyContains(['domain_id' => 'domain-id'], $body);
    }

    public function test_get_excludes_domain_id_when_not_set(): void
    {
        $this->addSuccessResponse();

        $this->messages->get(25, 1);

        $body = $this->assertRequest('GET', '/v1/messages');
        $this->assertBodyExcludes(['domain_id'], $body);
    }

    public function test_get_excludes_page_when_not_set(): void
    {
        $this->addSuccessResponse();

        $this->messages->get(25, null);

        $body = $this->assertRequest('GET', '/v1/messages');
        $this->assertBodyExcludes(['page'], $body);
    }

    /**
     * @dataProvider invalidLimitProvider
     */
    #[DataProvider('invalidLimitProvider')]
    public function test_get_rejects_invalid_limit(int $limit, string $message): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($message);

        $this->messages->get($limit);
    }

    public static function invalidLimitProvider(): array
    {
        return [
            'limit below minimum' => [9, 'Minimum limit is ' . Message::MIN_LIMIT . '.'],
            'limit above maximum' => [101, 'Maximum limit is ' . Message::MAX_LIMIT . '.'],
        ];
    }

    public function test_find_sends_correct_method_and_path(): void
    {
        $this->addSuccessResponse();

        $response = $this->messages->find('random_id');

        $this->assertRequest('GET', '/v1/messages/random_id');
        self::assertEquals(200, $response['status_code']);
    }

    public function test_find_forwards_status_code(): void
    {
        $this->addSuccessResponse(200);
        $response = $this->messages->find('message-id');
        self::assertEquals(200, $response['status_code']);
    }
}
