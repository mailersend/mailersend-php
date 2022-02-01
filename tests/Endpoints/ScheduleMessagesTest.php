<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use MailerSend\Common\Constants;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\ScheduleMessages;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Tests\TestCase;
use Psr\Http\Message\ResponseInterface;
use Tightenco\Collect\Support\Arr;

class ScheduleMessagesTest extends TestCase
{
    protected ScheduleMessages $scheduleMessages;
    protected ResponseInterface $defaultResponse;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = new Client();

        $this->scheduleMessages = new ScheduleMessages(new HttpLayer(self::OPTIONS, $this->client), self::OPTIONS);

        $this->defaultResponse = $this->createMock(ResponseInterface::class);
        $this->defaultResponse->method('getStatusCode')->willReturn(200);
    }

    /**
     * @dataProvider validGetAllDataProvider
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     */
    public function test_get_all(array $params, array $expected): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $this->client->addResponse($response);

        $response = $this->scheduleMessages->getAll(
            Arr::get($params, 'domain_id'),
            Arr::get($params, 'status'),
            Arr::get($params, 'page'),
            Arr::get($params, 'limit'),
        );

        $request = $this->client->getLastRequest();

        parse_str($request->getUri()->getQuery(), $query);

        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/v1/message-schedules', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);

        self::assertEquals(Arr::get($expected, 'domain_id'), Arr::get($query, 'domain_id'));
        self::assertEquals(Arr::get($expected, 'status'), Arr::get($query, 'status'));
        self::assertEquals(Arr::get($expected, 'page'), Arr::get($query, 'page'));
        self::assertEquals(Arr::get($expected, 'limit'), Arr::get($query, 'limit'));
    }

    /**
     * @dataProvider invalidGetAllDataProvider
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_get_all_with_errors(array $params): void
    {
        $this->expectException(MailerSendAssertException::class);

        $this->scheduleMessages->getAll(
            Arr::get($params, 'domain_id'),
            Arr::get($params, 'status'),
            Arr::get($params, 'page'),
            Arr::get($params, 'limit'),
        );
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function test_find_requires_domain_id(): void
    {
        $this->expectException(MailerSendAssertException::class);

        $this->scheduleMessages->find('');
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function test_delete_requires_domain_id(): void
    {
        $this->expectException(MailerSendAssertException::class);

        $this->scheduleMessages->delete('');
    }

    public function validGetAllDataProvider(): array
    {
        return [
            'empty request' => [
                [],
                [
                    'domain_id' => null,
                    'status' => null,
                    'page' => null,
                    'limit' => null,
                ],
            ],
            'with page' => [
                [
                    'page' => 1,
                ],
                [
                    'domain_id' => null,
                    'status' => null,
                    'page' => 1,
                    'limit' => null,
                ],
            ],
            'with limit' => [
                [
                    'limit' => 10,
                ],
                [
                    'domain_id' => null,
                    'status' => null,
                    'page' => null,
                    'limit' => 10,
                ],
            ],
            'with domain id' => [
                [
                    'domain_id' => 'test_id',
                ],
                [
                    'domain_id' => 'test_id',
                    'status' => null,
                    'page' => null,
                    'limit' => null,
                ],
            ],
            'with status' => [
                [
                    'status' => Constants::STATUS_SCHEDULED,
                ],
                [
                    'domain_id' => null,
                    'status' => Constants::STATUS_SCHEDULED,
                    'page' => null,
                    'limit' => null,
                ],
            ],
            'complete request' => [
                [
                    'domain_id' => 'test_id',
                    'status' => Constants::STATUS_SCHEDULED,
                    'page' => 1,
                    'limit' => 10,
                ],
                [
                    'domain_id' => 'test_id',
                    'status' => Constants::STATUS_SCHEDULED,
                    'page' => 1,
                    'limit' => 10,
                ],
            ],
        ];
    }

    public function invalidGetAllDataProvider(): array
    {
        return [
            'with limit under 10' => [
                [
                    'limit' => 9,
                ],
            ],
            'with limit over 100' => [
                [
                    'limit' => 101,
                ],
            ],
            'with invalid status' => [
                [
                    'status' => 'invalid',
                ],
            ],
        ];
    }
}
