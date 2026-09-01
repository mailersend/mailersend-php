<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\Recipient;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Http\Message\ResponseInterface;

class RecipientTest extends TestCase
{
    protected Recipient $recipients;
    protected ResponseInterface $defaultResponse;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = new Client();

        $this->recipients = new Recipient(new HttpLayer(self::OPTIONS, $this->client), self::OPTIONS);
        $this->defaultResponse = $this->createStub(ResponseInterface::class);
        $this->defaultResponse->method('getStatusCode')->willReturn(200);
    }

    public function test_get_sends_correct_method_and_path(): void
    {
        $this->addSuccessResponse();

        $response = $this->recipients->get(null);

        $this->assertRequest('GET', '/v1/recipients');
        self::assertEquals(200, $response['status_code']);
    }

    public function test_get_with_limit(): void
    {
        $this->addSuccessResponse();

        $this->recipients->get(null, 30);

        $body = $this->assertRequest('GET', '/v1/recipients');
        $this->assertBodyContains(['limit' => 30], $body);
    }

    public function test_get_with_page(): void
    {
        $this->addSuccessResponse();

        $this->recipients->get(null, 25, 2);

        $body = $this->assertRequest('GET', '/v1/recipients');
        $this->assertBodyContains(['page' => 2], $body);
    }

    public function test_get_sends_default_limit(): void
    {
        $this->addSuccessResponse();

        $this->recipients->get(null);

        $body = $this->assertRequest('GET', '/v1/recipients');
        $this->assertBodyContains(['limit' => Recipient::DEFAULT_LIMIT], $body);
    }

    public function test_get_with_domain_id(): void
    {
        $this->addSuccessResponse();

        $this->recipients->get('domain_id');

        $body = $this->assertRequest('GET', '/v1/recipients');
        $this->assertBodyContains(['domain_id' => 'domain_id'], $body);
    }

    public function test_get_excludes_domain_id_when_not_set(): void
    {
        $this->addSuccessResponse();

        $this->recipients->get(null, 25);

        $body = $this->assertRequest('GET', '/v1/recipients');
        $this->assertBodyExcludes(['domain_id'], $body);
    }

    public function test_get_excludes_page_when_not_set(): void
    {
        $this->addSuccessResponse();

        $this->recipients->get(null, 25, null);

        $body = $this->assertRequest('GET', '/v1/recipients');
        $this->assertBodyExcludes(['page'], $body);
    }

    /**
     * @dataProvider invalidGetLimitProvider
     * @param int $limit
     * @param string $exceptionMessage
     */
    #[DataProvider('invalidGetLimitProvider')]
    public function test_get_rejects_invalid_limit(int $limit, string $exceptionMessage): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $this->recipients->get('domain_id', $limit);
    }

    public static function invalidGetLimitProvider(): array
    {
        return [
            'limit below minimum' => [9, 'Minimum limit is ' . Recipient::MIN_LIMIT . '.'],
            'limit above maximum' => [101, 'Maximum limit is ' . Recipient::MAX_LIMIT . '.'],
        ];
    }

    public function test_get_rejects_empty_domain_id(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Domain id cannot be empty.');
        $this->recipients->get('');
    }

    public function test_find_sends_correct_method_and_path(): void
    {
        $this->addSuccessResponse();

        $response = $this->recipients->find('random_id');

        $this->assertRequest('GET', '/v1/recipients/random_id');
        self::assertEquals(200, $response['status_code']);
    }

    public function test_delete_sends_correct_method_and_path(): void
    {
        $this->addSuccessResponse();

        $response = $this->recipients->delete('random_id');

        $this->assertRequest('DELETE', '/v1/recipients/random_id');
        self::assertEquals(200, $response['status_code']);
    }
}
