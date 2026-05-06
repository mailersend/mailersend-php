<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use MailerSend\Helpers\Arr;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\SmsInbound;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Builder\SmsInbound as SmsInboundBuilder;
use MailerSend\Helpers\Builder\SmsInboundFilter;
use MailerSend\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Http\Message\ResponseInterface;

class SmsInboundTest extends TestCase
{
    protected SmsInbound $smsInbound;
    protected ResponseInterface $defaultResponse;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = new Client();

        $this->smsInbound = new SmsInbound(new HttpLayer(self::OPTIONS, $this->client), self::OPTIONS);

        $this->defaultResponse = $this->createStub(ResponseInterface::class);
        $this->defaultResponse->method('getStatusCode')->willReturn(200);
    }

    /**
     * @dataProvider validSmsInboundListDataProvider
     * @param array $params
     * @param array $expected
     * @param array $excluded
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    #[DataProvider('validSmsInboundListDataProvider')]
    public function test_get_all(array $params, array $expected, array $excluded): void
    {
        $this->addSuccessResponse();

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

        $this->assertQueryParams($expected, $query);

        foreach ($excluded as $key) {
            self::assertArrayNotHasKey($key, $query);
        }
    }

    public function test_get_all_excludes_optional_params_when_not_set(): void
    {
        $this->addSuccessResponse();

        $this->smsInbound->getAll(null, null, null, null);

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);

        self::assertArrayNotHasKey('sms_number_id', $query);
        self::assertArrayNotHasKey('enabled', $query);
        self::assertArrayNotHasKey('page', $query);
        self::assertArrayNotHasKey('limit', $query);
    }

    /**
     * @dataProvider invalidSmsInboundListDataProvider
     * @param int $limit
     * @param string $message
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    #[DataProvider('invalidSmsInboundListDataProvider')]
    public function test_get_all_rejects_invalid_limit(int $limit, string $message): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($message);

        $this->smsInbound->getAll(null, null, null, $limit);
    }

    /**
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_find_requires_sms_inbound_id(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('SMS inbound id is required.');

        $this->smsInbound->find('');
    }

    /**
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_find_sms_inbound_id(): void
    {
        $this->addSuccessResponse();

        $response = $this->smsInbound->find('hashed_sms_inbound_id');

        $this->assertRequest('GET', '/v1/sms-inbounds/hashed_sms_inbound_id');
        self::assertEquals(200, $response['status_code']);
    }

    /**
     * @dataProvider validSmsInboundCreateDataProvider
     * @param SmsInboundBuilder $params
     * @param array $expected
     * @param array $excluded
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    #[DataProvider('validSmsInboundCreateDataProvider')]
    public function test_create_sms_inbound(SmsInboundBuilder $params, array $expected, array $excluded): void
    {
        $this->addSuccessResponse();

        $response = $this->smsInbound->create($params);

        $body = $this->assertRequest('POST', '/v1/sms-inbounds');

        self::assertEquals(200, $response['status_code']);
        $this->assertBodyContains($expected, $body);
        $this->assertBodyExcludes($excluded, $body);

        if ($params->getFilter() !== null) {
            self::assertSame($params->getFilter()->toArray()['comparer'], Arr::get($body, 'filter.comparer'));
            self::assertSame($params->getFilter()->toArray()['value'], Arr::get($body, 'filter.value'));
        }
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function test_update_sms_inbound(): void
    {
        $this->addSuccessResponse();

        $params = (new SmsInboundBuilder())
            ->setSmsNumberId('hashed_sms_number_id')
            ->setName('Updated')
            ->setForwardUrl('https://mailersend.com/updated')
            ->setFilter(new SmsInboundFilter('starts-with', 'value'))
            ->setEnabled(false);

        $response = $this->smsInbound->update('hashed_sms_inbound_id', $params);

        $body = $this->assertRequest('PUT', '/v1/sms-inbounds/hashed_sms_inbound_id');

        self::assertEquals(200, $response['status_code']);
        $this->assertBodyContains([
            'sms_number_id' => 'hashed_sms_number_id',
            'name' => 'Updated',
            'forward_url' => 'https://mailersend.com/updated',
        ], $body);
        self::assertSame('starts-with', Arr::get($body, 'filter.comparer'));
        self::assertSame('value', Arr::get($body, 'filter.value'));
        self::assertFalse(Arr::get($body, 'enabled'));
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws MailerSendAssertException
     * @throws \JsonException
     */
    public function test_update_requires_sms_inbound_id(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('SMS inbound id is required.');

        $this->smsInbound->update('', new SmsInboundBuilder());
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws MailerSendAssertException
     * @throws \JsonException
     */
    public function test_delete_sms_inbound(): void
    {
        $this->addSuccessResponse();

        $response = $this->smsInbound->delete('hashedSmsInboundId');

        $this->assertRequest('DELETE', '/v1/sms-inbounds/hashedSmsInboundId');
        self::assertEquals(200, $response['status_code']);
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function test_delete_requires_sms_inbound_id(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('SMS inbound id is required.');

        $this->smsInbound->delete('');
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function test_create_requires_sms_number_id(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('SMS number id is required.');

        $params = (new SmsInboundBuilder())
            ->setName('Test name')
            ->setForwardUrl('https://mailersend.com/inbound');

        $this->smsInbound->create($params);
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function test_create_requires_name(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('SMS inbound name is required');

        $params = (new SmsInboundBuilder())
            ->setSmsNumberId('hashed_sms_number_id')
            ->setForwardUrl('https://mailersend.com/inbound');

        $this->smsInbound->create($params);
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function test_create_requires_valid_forward_url(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Invalid URL.');

        $params = (new SmsInboundBuilder())
            ->setSmsNumberId('hashed_sms_number_id')
            ->setName('Test name')
            ->setForwardUrl('not-a-valid-url');

        $this->smsInbound->create($params);
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function test_create_rejects_invalid_filter_comparer(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Invalid filter comparer.');

        $params = (new SmsInboundBuilder())
            ->setSmsNumberId('hashed_sms_number_id')
            ->setName('Test name')
            ->setForwardUrl('https://mailersend.com/inbound')
            ->setFilter(new SmsInboundFilter('invalid-comparer', 'value'));

        $this->smsInbound->create($params);
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function test_update_without_filter(): void
    {
        $this->addSuccessResponse();

        $params = (new SmsInboundBuilder())
            ->setSmsNumberId('hashed_sms_number_id')
            ->setName('Updated')
            ->setForwardUrl('https://mailersend.com/updated')
            ->setEnabled(true);

        $response = $this->smsInbound->update('hashed_sms_inbound_id', $params);

        $body = $this->assertRequest('PUT', '/v1/sms-inbounds/hashed_sms_inbound_id');

        self::assertEquals(200, $response['status_code']);
        $this->assertBodyExcludes(['filter'], $body);
        $this->assertBodyContains([
            'sms_number_id' => 'hashed_sms_number_id',
            'name' => 'Updated',
        ], $body);
        self::assertTrue($body['enabled']);
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function test_update_rejects_invalid_filter_comparer(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Invalid filter comparer.');

        $params = (new SmsInboundBuilder())
            ->setSmsNumberId('hashed_sms_number_id')
            ->setName('Updated')
            ->setForwardUrl('https://mailersend.com/updated')
            ->setFilter(new SmsInboundFilter('bad-comparer', 'value'));

        $this->smsInbound->update('hashed_sms_inbound_id', $params);
    }

    public static function validSmsInboundListDataProvider(): array
    {
        return [
            'empty request' => [
                'params' => [],
                'expected' => [],
                'excluded' => ['sms_number_id', 'enabled', 'page', 'limit'],
            ],
            'with sms number id' => [
                'params' => [
                    'sms_number_id' => 'hashed_sms_number_id',
                ],
                'expected' => [
                    'sms_number_id' => 'hashed_sms_number_id',
                ],
                'excluded' => ['enabled', 'page', 'limit'],
            ],
            'with enabled false' => [
                'params' => [
                    'enabled' => false,
                ],
                'expected' => [
                    'enabled' => '0',
                ],
                'excluded' => ['sms_number_id', 'page', 'limit'],
            ],
            'with enabled true' => [
                'params' => [
                    'enabled' => true,
                ],
                'expected' => [
                    'enabled' => '1',
                ],
                'excluded' => ['sms_number_id', 'page', 'limit'],
            ],
            'with page' => [
                'params' => [
                    'page' => 1,
                ],
                'expected' => [
                    'page' => '1',
                ],
                'excluded' => ['sms_number_id', 'enabled', 'limit'],
            ],
            'with limit' => [
                'params' => [
                    'limit' => 10,
                ],
                'expected' => [
                    'limit' => '10',
                ],
                'excluded' => ['sms_number_id', 'enabled', 'page'],
            ],
            'complete request' => [
                'params' => [
                    'sms_number_id' => 'hashed_sms_number_id',
                    'enabled' => false,
                    'page' => 1,
                    'limit' => 10,
                ],
                'expected' => [
                    'sms_number_id' => 'hashed_sms_number_id',
                    'enabled' => '0',
                    'page' => '1',
                    'limit' => '10',
                ],
                'excluded' => [],
            ],
        ];
    }

    public static function invalidSmsInboundListDataProvider(): array
    {
        return [
            'limit below minimum' => [9, 'Limit is supposed to be between 10 and 100.'],
            'limit above maximum' => [101, 'Limit is supposed to be between 10 and 100.'],
        ];
    }

    public static function validSmsInboundCreateDataProvider(): array
    {
        return [
            'with filter enabled' => [
                'params' => (new SmsInboundBuilder())
                    ->setSmsNumberId('hashed_sms_number_id')
                    ->setName('Test name')
                    ->setForwardUrl('https://www.mailersend.com/inbound_webhook')
                    ->setFilter(new SmsInboundFilter('equal', 'value'))
                    ->setEnabled(true),
                'expected' => [
                    'sms_number_id' => 'hashed_sms_number_id',
                    'name' => 'Test name',
                    'forward_url' => 'https://www.mailersend.com/inbound_webhook',
                    'enabled' => true,
                ],
                'excluded' => [],
            ],
            'without filter disabled' => [
                'params' => (new SmsInboundBuilder())
                    ->setSmsNumberId('hashed_sms_number_id')
                    ->setName('Test name')
                    ->setForwardUrl('https://www.mailersend.com/inbound_webhook')
                    ->setEnabled(false),
                'expected' => [
                    'sms_number_id' => 'hashed_sms_number_id',
                    'name' => 'Test name',
                    'forward_url' => 'https://www.mailersend.com/inbound_webhook',
                    'enabled' => false,
                ],
                'excluded' => ['filter'],
            ],
            'without filter enabled' => [
                'params' => (new SmsInboundBuilder())
                    ->setSmsNumberId('hashed_sms_number_id')
                    ->setName('Test name')
                    ->setForwardUrl('https://www.mailersend.com/inbound_webhook')
                    ->setEnabled(true),
                'expected' => [
                    'sms_number_id' => 'hashed_sms_number_id',
                    'name' => 'Test name',
                    'forward_url' => 'https://www.mailersend.com/inbound_webhook',
                    'enabled' => true,
                ],
                'excluded' => ['filter'],
            ],
        ];
    }
}
