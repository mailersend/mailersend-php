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

        $this->assertQueryParams(['limit' => '30'], $this->lastRequestQuery());
    }

    public function test_get_with_page(): void
    {
        $this->addSuccessResponse();

        $this->recipients->get(null, 25, 2);

        $this->assertQueryParams(['page' => '2'], $this->lastRequestQuery());
    }

    public function test_get_sends_default_limit(): void
    {
        $this->addSuccessResponse();

        $this->recipients->get(null);

        $this->assertQueryParams(['limit' => (string) Recipient::DEFAULT_LIMIT], $this->lastRequestQuery());
    }

    public function test_get_with_domain_id(): void
    {
        $this->addSuccessResponse();

        $this->recipients->get('domain_id');

        $this->assertQueryParams(['domain_id' => 'domain_id'], $this->lastRequestQuery());
    }

    public function test_get_sends_no_params_in_body(): void
    {
        $this->addSuccessResponse();

        $this->recipients->get('domain_id', 30, 2);

        self::assertSame([], $this->lastRequestBody());
    }

    public function test_get_excludes_domain_id_when_not_set(): void
    {
        $this->addSuccessResponse();

        $this->recipients->get(null, 25);

        $this->assertBodyExcludes(['domain_id'], $this->lastRequestQuery());
    }

    public function test_get_excludes_page_when_not_set(): void
    {
        $this->addSuccessResponse();

        $this->recipients->get(null, 25, null);

        $this->assertBodyExcludes(['page'], $this->lastRequestQuery());
    }

    /**
     * @dataProvider invalidGetParamsProvider
     * @param string $domainId
     * @param int $limit
     * @param string $exceptionMessage
     */
    #[DataProvider('invalidGetParamsProvider')]
    public function test_get_rejects_invalid_params(string $domainId, int $limit, string $exceptionMessage): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $this->recipients->get($domainId, $limit);
    }

    public static function invalidGetParamsProvider(): array
    {
        return [
            'limit below minimum' => ['domain_id', 9, 'Minimum limit is ' . Recipient::MIN_LIMIT . '.'],
            'limit above maximum' => ['domain_id', 101, 'Maximum limit is ' . Recipient::MAX_LIMIT . '.'],
            'empty domain id' => ['', Recipient::DEFAULT_LIMIT, 'Domain id cannot be empty.'],
        ];
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

    private function lastRequestQuery(): array
    {
        parse_str($this->client->getLastRequest()->getUri()->getQuery(), $query);

        return $query;
    }
}
