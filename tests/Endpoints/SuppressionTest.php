<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use MailerSend\Common\Constants;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\Suppression;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Arr;
use MailerSend\Helpers\Builder\SuppressionParams;
use MailerSend\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Http\Message\ResponseInterface;

class SuppressionTest extends TestCase
{
    protected Suppression $suppression;
    protected ResponseInterface $defaultResponse;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = new Client();
        $this->suppression = new Suppression(new HttpLayer(self::OPTIONS, $this->client), self::OPTIONS, 'suppressions/hard-bounces');

        $this->defaultResponse = $this->createStub(ResponseInterface::class);
        $this->defaultResponse->method('getStatusCode')->willReturn(200);
    }

    /**
     * @dataProvider validGetAllDataProvider
     */
    #[DataProvider('validGetAllDataProvider')]
    public function test_get_all(array $params, array $expected): void
    {
        $this->addSuccessResponse();

        $this->suppression->getAll(
            $params['domain_id'] ?? null,
            $params['page'] ?? null,
            $params['limit'] ?? null,
        );

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);

        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/v1/suppressions/hard-bounces', $request->getUri()->getPath());

        self::assertEquals(Arr::get($expected, 'domain_id'), Arr::get($query, 'domain_id'));
        self::assertEquals(Arr::get($expected, 'page'), Arr::get($query, 'page'));
        self::assertEquals(Arr::get($expected, 'limit'), Arr::get($query, 'limit'));
    }

    /**
     * @dataProvider invalidGetAllDataProvider
     */
    #[DataProvider('invalidGetAllDataProvider')]
    public function test_get_all_with_errors(array $params, string $errorMessage): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($errorMessage);

        $this->suppression->getAll(
            $params['domain_id'] ?? null,
            $params['page'] ?? null,
            $params['limit'] ?? null,
        );
    }

    public static function validGetAllDataProvider(): array
    {
        return [
            'no params' => [
                [],
                [],
            ],
            'with domain id' => [
                ['domain_id' => 'domain_id'],
                ['domain_id' => 'domain_id'],
            ],
            'with page and limit' => [
                ['page' => 2, 'limit' => 20],
                ['page' => '2', 'limit' => '20'],
            ],
            'with all params' => [
                ['domain_id' => 'domain_id', 'page' => 3, 'limit' => 25],
                ['domain_id' => 'domain_id', 'page' => '3', 'limit' => '25'],
            ],
        ];
    }

    public static function invalidGetAllDataProvider(): array
    {
        return [
            'limit too low' => [
                ['limit' => 9],
                'Limit is supposed to be between ' . Constants::MIN_LIMIT . ' and ' . Constants::MAX_LIMIT . '.',
            ],
            'limit too high' => [
                ['limit' => 101],
                'Limit is supposed to be between ' . Constants::MIN_LIMIT . ' and ' . Constants::MAX_LIMIT . '.',
            ],
        ];
    }

    public function test_create(): void
    {
        $this->addSuccessResponse();

        $params = (new SuppressionParams())
            ->setDomainId('domain_id')
            ->setRecipients(['recipient@mailersend.com']);

        $this->suppression->create($params);

        $body = $this->assertRequest('POST', '/v1/suppressions/hard-bounces');

        self::assertEquals('domain_id', Arr::get($body, 'domain_id'));
        self::assertEquals(['recipient@mailersend.com'], Arr::get($body, 'recipients'));
    }

    public function test_create_requires_recipients(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Recipients is required.');

        $params = (new SuppressionParams())
            ->setDomainId('domain_id')
            ->setRecipients([]);

        $this->suppression->create($params);
    }

    public function test_create_requires_domain_id(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Domain id is required.');

        $params = (new SuppressionParams())
            ->setRecipients(['recipient@mailersend.com']);

        $this->suppression->create($params);
    }

    public function test_delete_by_ids(): void
    {
        $this->addSuccessResponse();

        $this->suppression->delete(['id_1', 'id_2']);

        $body = $this->assertRequest('DELETE', '/v1/suppressions/hard-bounces');

        self::assertEquals(['id_1', 'id_2'], Arr::get($body, 'ids'));
        self::assertFalse((bool) Arr::get($body, 'all'));
        $this->assertBodyExcludes(['domain_id'], $body);
    }

    public function test_delete_all(): void
    {
        $this->addSuccessResponse();

        $this->suppression->delete(null, true, 'domain_id');

        $body = $this->assertRequest('DELETE', '/v1/suppressions/hard-bounces');

        self::assertTrue(Arr::get($body, 'all'));
        self::assertEquals('domain_id', Arr::get($body, 'domain_id'));
        $this->assertBodyExcludes(['ids'], $body);
    }

    public function test_delete_requires_ids_or_all(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Either ids or all must be provided.');

        $this->suppression->delete(null, false);
    }

    public function test_delete_with_empty_ids_requires_all(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Either ids or all must be provided.');

        $this->suppression->delete([], false);
    }

    public function test_get_all_forwards_status_code(): void
    {
        $this->addSuccessResponse(200);
        $response = $this->suppression->getAll();
        self::assertEquals(200, $response['status_code']);
    }

    public function test_create_forwards_status_code(): void
    {
        $this->addSuccessResponse(200);
        $params = (new SuppressionParams())
            ->setDomainId('domain_id')
            ->setRecipients(['recipient@mailersend.com']);
        $response = $this->suppression->create($params);
        self::assertEquals(200, $response['status_code']);
    }

    public function test_delete_forwards_status_code(): void
    {
        $this->addSuccessResponse(200);
        $response = $this->suppression->delete(['id_1']);
        self::assertEquals(200, $response['status_code']);
    }

}
