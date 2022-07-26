<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\SmsInbound;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Builder\SmsInbound as SmsInboundBuilder;
use MailerSend\Helpers\Builder\SmsInboundFilter;
use MailerSend\Tests\TestCase;
use Psr\Http\Message\ResponseInterface;
use Tightenco\Collect\Support\Arr;

class SmsInboundTest extends TestCase
{
    protected SmsInbound $smsInbound;
    protected ResponseInterface $defaultResponse;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = new Client();

        $this->smsInbound = new SmsInbound(new HttpLayer(self::OPTIONS, $this->client), self::OPTIONS);

        $this->defaultResponse = $this->createMock(ResponseInterface::class);
        $this->defaultResponse->method('getStatusCode')->willReturn(200);
    }

    /**
     * @dataProvider validSmsInboundListDataProvider
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_get_all(array $params, array $expected): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $this->client->addResponse($response);

        $response = $this->smsInbound->getAll(
            Arr::get($params, 'sms_number_id'),
            Arr::get($params, 'enabled'),
            Arr::get($params, 'page'),
            Arr::get($params, 'limit'),
        );

        $request = $this->client->getLastRequest();

        parse_str($request->getUri()->getQuery(), $query);

        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/v1/sms-inbounds', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);

        self::assertEquals(Arr::get($expected, 'sms_number_id'), Arr::get($query, 'sms_number_id'));
        self::assertEquals(Arr::get($expected, 'enabled'), Arr::get($query, 'enabled'));
        self::assertEquals(Arr::get($expected, 'page'), Arr::get($query, 'page'));
        self::assertEquals(Arr::get($expected, 'limit'), Arr::get($query, 'limit'));
    }

    /**
     * @dataProvider invalidSmsInboundListDataProvider
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_get_all_with_errors(array $params): void
    {
        $this->expectException(MailerSendAssertException::class);

        $this->smsInbound->getAll(
            Arr::get($params, 'domain_id'),
            Arr::get($params, 'enabled'),
            Arr::get($params, 'page'),
            Arr::get($params, 'limit'),
        );
    }

    /**
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_find_requires_sms_inbound_id(): void
    {
        $this->expectException(MailerSendAssertException::class);

        $this->smsInbound->find('');
    }

    /**
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_find_sms_inbound_id(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $this->client->addResponse($response);

        $response = $this->smsInbound->find('hashed_sms_inbound_id');

        $request = $this->client->getLastRequest();

        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/v1/sms-inbounds/hashed_sms_inbound_id', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);
    }

    /**
     * @dataProvider validSmsInboundCreateDataProvider
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_create_sms_inbound(SmsInboundBuilder $params, array $expected): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $this->client->addResponse($response);

        $response = $this->smsInbound->create($params);

        $request = $this->client->getLastRequest();
        $request_body = json_decode((string)$request->getBody(), true, 512, JSON_THROW_ON_ERROR);

        self::assertEquals('POST', $request->getMethod());
        self::assertEquals('/v1/sms-inbounds', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);
        self::assertSame(Arr::get($expected, 'sms_number_id'), Arr::get($request_body, 'sms_number_id'));
        self::assertSame(Arr::get($expected, 'name'), Arr::get($request_body, 'name'));
        self::assertSame(Arr::get($expected, 'filter'), Arr::get($request_body, 'filter'));
        self::assertSame(Arr::get($expected, 'forward_url'), Arr::get($request_body, 'forward_url'));
        self::assertSame(Arr::get($expected, 'enabled'), Arr::get($request_body, 'enabled'));
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function test_update_sms_inbound(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $this->client->addResponse($response);

        $params = (new SmsInboundBuilder())
        ->setSmsNumberId('hashed_sms_number_id')
        ->setName('Updated')
        ->setForwardUrl('https://mailersend.com/updated')
        ->setFilter(new SmsInboundFilter('starts-with', 'value'))
        ->setEnabled(false);

        $response = $this->smsInbound->update(
            'hashed_sms_inbound_id',
            $params,
        );

        $request = $this->client->getLastRequest();
        $request_body = json_decode((string)$request->getBody(), true, 512, JSON_THROW_ON_ERROR);

        self::assertEquals('PUT', $request->getMethod());
        self::assertEquals('/v1/sms-inbounds/hashed_sms_inbound_id', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);
        self::assertSame('hashed_sms_number_id', Arr::get($request_body, 'sms_number_id'));
        self::assertSame('Updated', Arr::get($request_body, 'name'));
        self::assertSame('https://mailersend.com/updated', Arr::get($request_body, 'forward_url'));
        self::assertSame('starts-with', Arr::get($request_body, 'filter.comparer'));
        self::assertSame('value', Arr::get($request_body, 'filter.value'));
        self::assertFalse(Arr::get($request_body, 'enabled'));
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws MailerSendAssertException
     * @throws \JsonException
     */
    public function test_delete_sms_inbound(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $this->client->addResponse($response);

        $response = $this->smsInbound->delete('hashedSmsInboundId');

        $request = $this->client->getLastRequest();

        self::assertEquals('DELETE', $request->getMethod());
        self::assertEquals('/v1/sms-inbounds/hashedSmsInboundId', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function test_delete_sms_inbound_required_sms_inbound_id(): void
    {
        $this->expectException(MailerSendAssertException::class);

        $this->smsInbound->delete('');
    }

    public function validSmsInboundListDataProvider(): array
    {
        return [
            'empty request' => [
                'params' => [],
                'expected' => [
                    'sms_number_id' => null,
                    'enabled' => null,
                    'page' => null,
                    'limit' => null,
                ],
            ],
            'with sms number id' => [
                'params' => [
                    'sms_number_id' => 'hashed_sms_number_id',
                ],
                'expected' => [
                    'sms_number_id' => 'hashed_sms_number_id',
                    'enabled' => null,
                    'page' => null,
                    'limit' => null,
                ],
            ],
            'with enabled' => [
                [
                    'enabled' => false,
                ],
                [
                    'sms_number_id' => null,
                    'enabled' => 0,
                    'page' => null,
                    'limit' => null,
                ],
            ],
            'with page' => [
                [
                    'page' => 1,
                ],
                [
                    'sms_number_id' => null,
                    'enabled' => null,
                    'page' => 1,
                    'limit' => null,
                ],
            ],
            'with limit' => [
                [
                    'limit' => 10,
                ],
                [
                    'sms_number_id' => null,
                    'enabled' => null,
                    'page' => null,
                    'limit' => 10,
                ],
            ],
            'complete request' => [
                [
                    'sms_number_id' => 'hashed_sms_number_id',
                    'enabled' => false,
                    'page' => 1,
                    'limit' => 10,
                ],
                [
                    'sms_number_id' => 'hashed_sms_number_id',
                    'enabled' => 0,
                    'page' => 1,
                    'limit' => 10,
                ],
            ],
        ];
    }

    public function invalidSmsInboundListDataProvider(): array
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

    public function validSmsInboundCreateDataProvider(): array
    {
        return [
            'enabled, with filter' => [
                'params' => (new SmsInboundBuilder())
                    ->setSmsNumberId('hashed_sms_number_id')
                    ->setName('Test name')
                    ->setForwardUrl('https://www.mailersend.com/inbound_webhook')
                    ->setFilter(new SmsInboundFilter('equals', 'value'))
                    ->setEnabled(true),
                'expected' => [
                    'sms_number_id' => 'hashed_sms_number_id',
                    'name' => 'Test name',
                    'forward_url' => 'https://www.mailersend.com/inbound_webhook',
                    'filter' => [
                        'comparer' => 'equals',
                        'value' => 'value',
                    ],
                    'enabled' => true,
                ],
            ],
            'disabled, w/o filter' => [
                'params' => (new SmsInboundBuilder())
                    ->setSmsNumberId('hashed_sms_number_id')
                    ->setName('Test name')
                    ->setForwardUrl('https://www.mailersend.com/inbound_webhook')
                    ->setEnabled(false),
                'expected' => [
                    'sms_number_id' => 'hashed_sms_number_id',
                    'name' => 'Test name',
                    'forward_url' => 'https://www.mailersend.com/inbound_webhook',
                    'filter' => null,
                    'enabled' => false,
                ],
            ]
        ];
    }
}
