<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use MailerSend\Helpers\Arr;
use MailerSend\Common\Constants;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\ScheduleMessages;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Http\Message\ResponseInterface;

class ScheduleMessagesTest extends TestCase
{
    protected ScheduleMessages $scheduleMessages;
    protected ResponseInterface $defaultResponse;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = new Client();

        $this->scheduleMessages = new ScheduleMessages(new HttpLayer(self::OPTIONS, $this->client), self::OPTIONS);

        $this->defaultResponse = $this->createStub(ResponseInterface::class);
        $this->defaultResponse->method('getStatusCode')->willReturn(200);
    }

    /**
     * @dataProvider validGetAllDataProvider
     * @param array $params
     * @param array $expected
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     */
    #[DataProvider('validGetAllDataProvider')]
    public function test_get_all(array $params, array $expected): void
    {
        $this->addSuccessResponse();

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

    public function test_get_all_excludes_optional_params_when_not_set(): void
    {
        $this->addSuccessResponse();

        $this->scheduleMessages->getAll();

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);

        $this->assertArrayNotHasKey('domain_id', $query, "Query must not contain 'domain_id' when not set.");
        $this->assertArrayNotHasKey('status', $query, "Query must not contain 'status' when not set.");
        $this->assertArrayNotHasKey('page', $query, "Query must not contain 'page' when not set.");
    }

    /**
     * @dataProvider invalidGetAllDataProvider
     * @param array $params
     * @param string $exceptionMessage
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    #[DataProvider('invalidGetAllDataProvider')]
    public function test_get_all_rejects_invalid_params(array $params, string $exceptionMessage): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($exceptionMessage);

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
     * @throws MailerSendAssertException
     */
    public function test_find_sends_correct_method_and_path(): void
    {
        $this->addSuccessResponse();

        $response = $this->scheduleMessages->find('message-id-123');

        $this->assertRequest('GET', '/v1/message-schedules/message-id-123');
        self::assertEquals(200, $response['status_code']);
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function test_find_requires_message_id(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Message id is required.');

        $this->scheduleMessages->find('');
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     * @throws MailerSendAssertException
     */
    public function test_delete_sends_correct_method_and_path(): void
    {
        $this->addSuccessResponse();

        $response = $this->scheduleMessages->delete('message-id-123');

        $this->assertRequest('DELETE', '/v1/message-schedules/message-id-123');
        self::assertEquals(200, $response['status_code']);
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function test_delete_requires_message_id(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Message id is required.');

        $this->scheduleMessages->delete('');
    }

    public static function validGetAllDataProvider(): array
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
            'with status scheduled' => [
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
            'with status sent' => [
                [
                    'status' => Constants::STATUS_SENT,
                ],
                [
                    'domain_id' => null,
                    'status' => Constants::STATUS_SENT,
                    'page' => null,
                    'limit' => null,
                ],
            ],
            'with status error' => [
                [
                    'status' => Constants::STATUS_ERROR,
                ],
                [
                    'domain_id' => null,
                    'status' => Constants::STATUS_ERROR,
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

    public static function invalidGetAllDataProvider(): array
    {
        $limitMessage = 'Limit is supposed to be between ' . Constants::MIN_LIMIT . ' and ' . Constants::MAX_LIMIT . '.';

        return [
            'limit below minimum' => [
                ['limit' => 9],
                $limitMessage,
            ],
            'limit above maximum' => [
                ['limit' => 101],
                $limitMessage,
            ],
            'invalid status' => [
                ['status' => 'invalid'],
                'The status provided is invalid.',
            ],
        ];
    }
}
