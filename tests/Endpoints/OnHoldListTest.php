<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use MailerSend\Helpers\Arr;
use MailerSend\Common\Constants;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\OnHoldList;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Http\Message\ResponseInterface;

class OnHoldListTest extends TestCase
{
    protected OnHoldList $onHoldList;
    protected ResponseInterface $defaultResponse;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = new Client();

        $this->onHoldList = new OnHoldList(new HttpLayer(self::OPTIONS, $this->client), self::OPTIONS);
        $this->defaultResponse = $this->createStub(ResponseInterface::class);
        $this->defaultResponse->method('getStatusCode')->willReturn(200);
    }

    /**
     * @dataProvider validGetAllDataProvider
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    #[DataProvider('validGetAllDataProvider')]
    public function test_get_all(array $params): void
    {
        $response = $this->createStub(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $this->client->addResponse($response);

        $response = $this->onHoldList->getAll(
            Arr::get($params, 'domain_id'),
            Arr::get($params, 'page'),
            Arr::get($params, 'limit'),
        );

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);

        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/v1/suppressions/on-hold-list', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);
        self::assertEquals(Arr::get($params, 'page'), Arr::get($query, 'page'));
        self::assertEquals(Arr::get($params, 'limit'), Arr::get($query, 'limit'));
        self::assertEquals(Arr::get($params, 'domain_id'), Arr::get($query, 'domain_id'));
    }

    /**
     * @dataProvider invalidGetAllDataProvider
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    #[DataProvider('invalidGetAllDataProvider')]
    public function test_get_all_with_errors(array $params, string $errorMessage): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($errorMessage);

        $this->onHoldList->getAll(
            Arr::get($params, 'domain_id'),
            Arr::get($params, 'page'),
            Arr::get($params, 'limit'),
        );
    }

    /**
     * @dataProvider validDeleteDataProvider
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     * @throws MailerSendAssertException
     */
    #[DataProvider('validDeleteDataProvider')]
    public function test_delete(array $params): void
    {
        $response = $this->createStub(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $this->client->addResponse($response);

        $response = $this->onHoldList->delete(
            Arr::get($params, 'ids'),
            Arr::get($params, 'all', false),
            Arr::get($params, 'domain_id'),
        );

        $request = $this->client->getLastRequest();
        $request_body = json_decode((string)$request->getBody(), true, 512, JSON_THROW_ON_ERROR);

        self::assertEquals('DELETE', $request->getMethod());
        self::assertEquals('/v1/suppressions/on-hold-list', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);
        self::assertSame(Arr::get($params, 'ids'), Arr::get($request_body, 'ids'));
        self::assertSame(Arr::get($params, 'all', false), Arr::get($request_body, 'all'));
        self::assertSame(Arr::get($params, 'domain_id'), Arr::get($request_body, 'domain_id'));
    }

    public function test_delete_excludes_ids_when_deleting_all(): void
    {
        $this->addSuccessResponse();

        $this->onHoldList->delete(null, true);

        $body = $this->assertRequest('DELETE', '/v1/suppressions/on-hold-list');

        self::assertTrue($body['all']);
        $this->assertBodyExcludes(['ids'], $body);
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function test_delete_requires_either_ids_or_all(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Either ids or all must be provided.');

        $this->onHoldList->delete();
    }

    public function test_get_all_forwards_domain_id(): void
    {
        $this->addSuccessResponse();

        $this->onHoldList->getAll('some-domain-id', 1, 10);

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);

        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/v1/suppressions/on-hold-list', $request->getUri()->getPath());
        self::assertEquals('some-domain-id', $query['domain_id']);
        self::assertEquals('1', $query['page']);
        self::assertEquals('10', $query['limit']);
    }

    public function test_delete_forwards_domain_id(): void
    {
        $this->addSuccessResponse();

        $this->onHoldList->delete(['id_1'], false, 'some-domain-id');

        $body = $this->assertRequest('DELETE', '/v1/suppressions/on-hold-list');

        self::assertEquals(['id_1'], $body['ids']);
        self::assertEquals('some-domain-id', $body['domain_id']);
    }

    public static function validGetAllDataProvider(): array
    {
        return [
            'empty request' => [
                'params' => [],
            ],
            'with limit' => [
                'params' => [
                    'limit' => 10,
                ],
            ],
            'with page' => [
                'params' => [
                    'page' => 1,
                ],
            ],
            'with domain_id' => [
                'params' => [
                    'domain_id' => 'domain_id',
                ],
            ],
            'complete request' => [
                'params' => [
                    'domain_id' => 'domain_id',
                    'page' => 1,
                    'limit' => 10,
                ],
            ]
        ];
    }

    public static function invalidGetAllDataProvider(): array
    {
        return [
            'with limit under 10' => [
                'params' => [
                    'domain_id' => 'domain_id',
                    'limit' => 9,
                ],
                'errorMessage' => 'Limit is supposed to be between ' . Constants::MIN_LIMIT . ' and ' . Constants::MAX_LIMIT .  '.',
            ],
            'with limit over 100' => [
                'params' => [
                    'domain_id' => 'domain_id',
                    'limit' => 101,
                ],
                'errorMessage' => 'Limit is supposed to be between ' . Constants::MIN_LIMIT . ' and ' . Constants::MAX_LIMIT .  '.',
            ],
        ];
    }

    public static function validDeleteDataProvider(): array
    {
        return [
            'with ids' => [
                'params' => [
                    'ids' => ['id'],
                ],
            ],
            'with ids and domain_id' => [
                'params' => [
                    'ids' => ['id'],
                    'domain_id' => 'domain_id',
                ],
            ],
            'all' => [
                'params' => [
                    'all' => true,
                ],
            ],
        ];
    }
}
