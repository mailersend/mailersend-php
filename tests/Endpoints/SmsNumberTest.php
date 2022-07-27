<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\SmsNumber;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Tests\TestCase;
use Psr\Http\Message\ResponseInterface;
use Tightenco\Collect\Support\Arr;

class SmsNumberTest extends TestCase
{
    protected SmsNumber $smsNumber;
    protected ResponseInterface $defaultResponse;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = new Client();

        $this->smsNumber = new SmsNumber(new HttpLayer(self::OPTIONS, $this->client), self::OPTIONS);

        $this->defaultResponse = $this->createMock(ResponseInterface::class);
        $this->defaultResponse->method('getStatusCode')->willReturn(200);
    }

    /**
     * @dataProvider validSmsNumberListDataProvider
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_get_all(array $smsNumberParams, array $expected): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $this->client->addResponse($response);

        $response = $this->smsNumber->getAll(
            $smsNumberParams['page'] ?? null,
            $smsNumberParams['limit'] ?? null,
            $smsNumberParams['paused'] ?? null
        );

        $request = $this->client->getLastRequest();

        parse_str($request->getUri()->getQuery(), $query);

        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/v1/sms-numbers', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);

        self::assertEquals(Arr::get($expected, 'page'), Arr::get($query, 'page'));
        self::assertEquals(Arr::get($expected, 'limit'), Arr::get($query, 'limit'));
        self::assertEquals(Arr::get($expected, 'paused'), Arr::get($query, 'paused'));
    }

    /**
     * @dataProvider invalidSmsNumberListDataProvider
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_get_all_with_errors(array $smsNumberParams): void
    {
        $this->expectException(MailerSendAssertException::class);

        $this->smsNumber->getAll(
            $smsNumberParams['page'] ?? null,
            $smsNumberParams['limit'] ?? null,
            $smsNumberParams['paused'] ?? null
        );
    }

    public function test_find_requires_sms_number_id()
    {
        $this->expectException(MailerSendAssertException::class);

        $this->smsNumber->find('');
    }

    public function test_delete_requires_sms_number_id()
    {
        $this->expectException(MailerSendAssertException::class);

        $this->smsNumber->delete('');
    }

    public function test_update_sms_number_requires_id()
    {
        $this->expectExceptionMessage('SMS number id is required.');

        $this->smsNumber->update('', true);
    }

    public function test_update_sms_number()
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $this->client->addResponse($response);

        $response = $this->smsNumber->update('random_id', true);

        $request = $this->client->getLastRequest();
        $request_body = json_decode((string) $request->getBody(), true);

        self::assertEquals('PUT', $request->getMethod());
        self::assertEquals('/v1/sms-numbers/random_id', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);

        self::assertSame(true, Arr::get($request_body, 'paused'));
    }

    public function validSmsNumberListDataProvider(): array
    {
        return [
            'empty request' => [
                [],
                [
                    'page' => null,
                    'limit' => null,
                    'paused' => null,
                ],
            ],
            'with page' => [
                [
                    'page' => 1,
                ],
                [
                    'page' => 1,
                    'limit' => null,
                    'paused' => null,
                ],
            ],
            'with limit' => [
                [
                    'limit' => 10,
                ],
                [
                    'page' => null,
                    'limit' => 10,
                    'paused' => null,
                ],
            ],
            'with paused true' => [
                [
                    'paused' => true,
                ],
                [
                    'page' => null,
                    'limit' => null,
                    'paused' => true,
                ],
            ],
            'with paused false' => [
                [
                    'paused' => false,
                ],
                [
                    'page' => null,
                    'limit' => null,
                    'paused' => 0,
                ],
            ],
            'complete request' => [
                [
                    'page' => 1,
                    'limit' => 10,
                    'paused' => true,
                ],
                [
                    'page' => 1,
                    'limit' => 10,
                    'paused' => true,
                ],
            ],
        ];
    }

    public function invalidSmsNumberListDataProvider(): array
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
