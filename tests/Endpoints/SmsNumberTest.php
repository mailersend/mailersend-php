<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use MailerSend\Helpers\Arr;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\SmsNumber;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Http\Message\ResponseInterface;

class SmsNumberTest extends TestCase
{
    protected SmsNumber $smsNumber;
    protected ResponseInterface $defaultResponse;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = new Client();

        $this->smsNumber = new SmsNumber(new HttpLayer(self::OPTIONS, $this->client), self::OPTIONS);

        $this->defaultResponse = $this->createStub(ResponseInterface::class);
        $this->defaultResponse->method('getStatusCode')->willReturn(200);
    }

    /**
     * @dataProvider validSmsNumberListDataProvider
     * @param array $smsNumberParams
     * @param array $expected
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    #[DataProvider('validSmsNumberListDataProvider')]
    public function test_get_all(array $smsNumberParams, array $expected): void
    {
        $this->addSuccessResponse();

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

    public function test_get_all_excludes_optional_params_when_not_set(): void
    {
        $this->addSuccessResponse();

        $this->smsNumber->getAll(null, null, null);

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);

        self::assertArrayNotHasKey('page', $query);
        self::assertArrayNotHasKey('limit', $query);
        self::assertArrayNotHasKey('paused', $query);
    }

    /**
     * @dataProvider invalidSmsNumberListDataProvider
     * @param int $limit
     * @param string $expectedMessage
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    #[DataProvider('invalidSmsNumberListDataProvider')]
    public function test_get_all_rejects_invalid_limit(int $limit, string $expectedMessage): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($expectedMessage);

        $this->smsNumber->getAll(null, $limit);
    }

    public function test_find_requires_sms_number_id(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('SMS number id is required.');

        $this->smsNumber->find('');
    }

    public function test_find_sms_number(): void
    {
        $this->addSuccessResponse();

        $response = $this->smsNumber->find('hashed_sms_number_id');

        $this->assertRequest('GET', '/v1/sms-numbers/hashed_sms_number_id');
        self::assertEquals(200, $response['status_code']);
    }

    public function test_delete_requires_sms_number_id(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Sms number id is required.');

        $this->smsNumber->delete('');
    }

    public function test_delete_sms_number(): void
    {
        $this->addSuccessResponse();

        $response = $this->smsNumber->delete('hashed_sms_number_id');

        $this->assertRequest('DELETE', '/v1/sms-numbers/hashed_sms_number_id');
        self::assertEquals(200, $response['status_code']);
    }

    public function test_update_requires_sms_number_id(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('SMS number id is required.');

        $this->smsNumber->update('', true);
    }

    public function test_update_with_paused_true(): void
    {
        $this->addSuccessResponse();

        $response = $this->smsNumber->update('random_id', true);

        $body = $this->assertRequest('PUT', '/v1/sms-numbers/random_id');

        self::assertEquals(200, $response['status_code']);
        self::assertSame(true, Arr::get($body, 'paused'));
    }

    public function test_update_with_paused_false(): void
    {
        $this->addSuccessResponse();

        $response = $this->smsNumber->update('random_id', false);

        $body = $this->assertRequest('PUT', '/v1/sms-numbers/random_id');

        self::assertEquals(200, $response['status_code']);
        self::assertFalse($body['paused']);
    }

    public static function validSmsNumberListDataProvider(): array
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
            'with all params' => [
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

    public static function invalidSmsNumberListDataProvider(): array
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
