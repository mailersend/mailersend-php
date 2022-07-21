<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\SmsMessage;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Tests\TestCase;
use Psr\Http\Message\ResponseInterface;
use Tightenco\Collect\Support\Arr;

class SmsMessageTest extends TestCase
{
    protected SmsMessage $smsMessage;
    protected ResponseInterface $defaultResponse;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = new Client();

        $this->smsMessage = new SmsMessage(new HttpLayer(self::OPTIONS, $this->client), self::OPTIONS);

        $this->defaultResponse = $this->createMock(ResponseInterface::class);
        $this->defaultResponse->method('getStatusCode')->willReturn(200);
    }

    /**
     * @dataProvider validSmsMessageListDataProvider
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_get_all(array $smsMessageParams, array $expected): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $this->client->addResponse($response);

        $response = $this->smsMessage->getAll(
            $smsMessageParams['page'] ?? null,
            $smsMessageParams['limit'] ?? null
        );

        $request = $this->client->getLastRequest();

        parse_str($request->getUri()->getQuery(), $query);

        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/v1/sms-messages', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);

        self::assertEquals(Arr::get($expected, 'page'), Arr::get($query, 'page'));
        self::assertEquals(Arr::get($expected, 'limit'), Arr::get($query, 'limit'));
    }

    /**
     * @dataProvider invalidSmsMessageListDataProvider
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_get_all_with_errors(array $smsMessageParams): void
    {
        $this->expectException(MailerSendAssertException::class);

        $this->smsMessage->getAll(
            $smsMessageParams['page'] ?? null,
            $smsMessageParams['limit'] ?? null
        );
    }

    public function test_find_requires_sms_message_id()
    {
        $this->expectException(MailerSendAssertException::class);

        $this->smsMessage->find('');
    }

    public function validSmsMessageListDataProvider(): array
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
            'complete request' => [
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

    public function invalidSmsMessageListDataProvider(): array
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
            ]
        ];
    }
}
