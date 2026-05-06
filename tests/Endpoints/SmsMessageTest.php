<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\SmsMessage;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Http\Message\ResponseInterface;

class SmsMessageTest extends TestCase
{
    protected SmsMessage $smsMessage;
    protected ResponseInterface $defaultResponse;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = new Client();

        $this->smsMessage = new SmsMessage(new HttpLayer(self::OPTIONS, $this->client), self::OPTIONS);

        $this->defaultResponse = $this->createStub(ResponseInterface::class);
        $this->defaultResponse->method('getStatusCode')->willReturn(200);
    }

    /**
     * @dataProvider validSmsMessageListDataProvider
     * @param array $smsMessageParams
     * @param array $expected
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    #[DataProvider('validSmsMessageListDataProvider')]
    public function test_get_all(array $smsMessageParams, array $expected): void
    {
        $this->addSuccessResponse();

        $response = $this->smsMessage->getAll(
            $smsMessageParams['page'] ?? null,
            $smsMessageParams['limit'] ?? null
        );

        $request = $this->client->getLastRequest();

        parse_str($request->getUri()->getQuery(), $query);

        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/v1/sms-messages', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);

        if ($expected['page'] !== null) {
            self::assertEquals($expected['page'], $query['page'] ?? null);
        } else {
            self::assertArrayNotHasKey('page', $query);
        }

        if ($expected['limit'] !== null) {
            self::assertEquals($expected['limit'], $query['limit'] ?? null);
        } else {
            self::assertArrayNotHasKey('limit', $query);
        }
    }

    public function test_get_all_uses_correct_method_and_path(): void
    {
        $this->addSuccessResponse();

        $this->smsMessage->getAll();

        $this->assertRequest('GET', '/v1/sms-messages');
    }

    public function test_get_all_forwards_status_code(): void
    {
        $this->addSuccessResponse(200);

        $response = $this->smsMessage->getAll();

        self::assertEquals(200, $response['status_code']);
    }

    public function test_get_all_sends_page_param(): void
    {
        $this->addSuccessResponse();

        $this->smsMessage->getAll(3);

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);

        $this->assertQueryParams(['page' => '3'], $query);
    }

    public function test_get_all_sends_limit_param(): void
    {
        $this->addSuccessResponse();

        $this->smsMessage->getAll(null, 25);

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);

        $this->assertQueryParams(['limit' => '25'], $query);
    }

    public function test_get_all_excludes_optional_params_when_not_set(): void
    {
        $this->addSuccessResponse();

        $this->smsMessage->getAll(null, null);

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);

        self::assertArrayNotHasKey('page', $query);
        self::assertArrayNotHasKey('limit', $query);
    }

    /**
     * @dataProvider invalidSmsMessageListDataProvider
     * @param int $limit
     * @param string $expectedMessage
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    #[DataProvider('invalidSmsMessageListDataProvider')]
    public function test_get_all_rejects_invalid_limit(int $limit, string $expectedMessage): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($expectedMessage);

        $this->smsMessage->getAll(null, $limit);
    }

    public function test_find_uses_correct_method_and_path(): void
    {
        $this->addSuccessResponse();

        $this->smsMessage->find('sms-message-id');

        $this->assertRequest('GET', '/v1/sms-messages/sms-message-id');
    }

    public function test_find_forwards_status_code(): void
    {
        $this->addSuccessResponse(200);

        $response = $this->smsMessage->find('sms-message-id');

        self::assertEquals(200, $response['status_code']);
    }

    public function test_find(): void
    {
        $this->addSuccessResponse();

        $response = $this->smsMessage->find('sms-message-id');

        $this->assertRequest('GET', '/v1/sms-messages/sms-message-id');
        self::assertEquals(200, $response['status_code']);
    }

    public function test_find_requires_sms_message_id(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('SMS message id is required.');

        $this->smsMessage->find('');
    }

    public static function validSmsMessageListDataProvider(): array
    {
        return [
            'empty request' => [
                [],
                [
                    'page' => null,
                    'limit' => null,
                ],
            ],
            'with page' => [
                [
                    'page' => 1,
                ],
                [
                    'page' => 1,
                    'limit' => null,
                ],
            ],
            'with limit' => [
                [
                    'limit' => 10,
                ],
                [
                    'page' => null,
                    'limit' => 10,
                ],
            ],
            'with page and limit' => [
                [
                    'page' => 1,
                    'limit' => 10,
                ],
                [
                    'page' => 1,
                    'limit' => 10,
                ],
            ],
        ];
    }

    public static function invalidSmsMessageListDataProvider(): array
    {
        return [
            'limit below minimum' => [
                9,
                'Limit is supposed to be between 10 and 100.',
            ],
            'limit above maximum' => [
                101,
                'Limit is supposed to be between 10 and 100.',
            ],
        ];
    }
}
