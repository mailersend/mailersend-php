<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\SmsRecipient;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Builder\SmsRecipientParams;
use MailerSend\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Http\Message\ResponseInterface;
use MailerSend\Helpers\Arr;

class SmsRecipientTest extends TestCase
{
    protected SmsRecipient $smsRecipient;
    protected ResponseInterface $defaultResponse;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = new Client();

        $this->smsRecipient = new SmsRecipient(new HttpLayer(self::OPTIONS, $this->client), self::OPTIONS);

        $this->defaultResponse = $this->createStub(ResponseInterface::class);
        $this->defaultResponse->method('getStatusCode')->willReturn(200);
    }

    public function test_get_all_uses_correct_method_and_path(): void
    {
        $this->addSuccessResponse();

        $this->smsRecipient->getAll(new SmsRecipientParams());

        $request = $this->client->getLastRequest();

        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/v1/sms-recipients', $request->getUri()->getPath());
    }

    public function test_get_all_forwards_status_code(): void
    {
        $this->addSuccessResponse();

        $response = $this->smsRecipient->getAll(new SmsRecipientParams());

        self::assertEquals(200, $response['status_code']);
    }

    public function test_get_all_sends_sms_number_id_param(): void
    {
        $this->addSuccessResponse();

        $this->smsRecipient->getAll((new SmsRecipientParams())->setSmsNumberId('hashed_sms_number_id'));

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);

        self::assertEquals('hashed_sms_number_id', $query['sms_number_id']);
    }

    public function test_get_all_sends_page_param(): void
    {
        $this->addSuccessResponse();

        $this->smsRecipient->getAll((new SmsRecipientParams())->setPage(3));

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);

        self::assertEquals(3, $query['page']);
    }

    public function test_get_all_sends_limit_param(): void
    {
        $this->addSuccessResponse();

        $this->smsRecipient->getAll((new SmsRecipientParams())->setLimit(15));

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);

        self::assertEquals(15, $query['limit']);
    }

    public function test_get_all_sends_status_param(): void
    {
        $this->addSuccessResponse();

        $this->smsRecipient->getAll((new SmsRecipientParams())->setStatus('opt_out'));

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);

        self::assertEquals('opt_out', $query['status']);
    }

    public function test_get_all_excludes_optional_params_when_not_set(): void
    {
        $this->addSuccessResponse();

        $this->smsRecipient->getAll(new SmsRecipientParams());

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);

        self::assertArrayNotHasKey('sms_number_id', $query);
        self::assertArrayNotHasKey('page', $query);
        self::assertArrayNotHasKey('limit', $query);
        self::assertArrayNotHasKey('status', $query);
    }

    /**
     * @dataProvider invalidSmsRecipientListDataProvider
     * @param SmsRecipientParams $smsRecipientParams
     * @param string $expectedMessage
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    #[DataProvider('invalidSmsRecipientListDataProvider')]
    public function test_get_all_rejects_invalid_params(SmsRecipientParams $smsRecipientParams, string $expectedMessage): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($expectedMessage);

        $httpLayer = $this->createStub(HttpLayer::class);
        $httpLayer->method('get')
            ->withAnyParameters()
            ->willReturn([]);

        (new SmsRecipient($httpLayer, self::OPTIONS))->getAll($smsRecipientParams);
    }

    public function test_find_requires_sms_recipient_id(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('SMS recipient id is required.');

        $this->smsRecipient->find('');
    }

    public function test_find_sms_recipient(): void
    {
        $this->addSuccessResponse();

        $response = $this->smsRecipient->find('hashed_recipient_id');

        $this->assertRequest('GET', '/v1/sms-recipients/hashed_recipient_id');

        self::assertEquals(200, $response['status_code']);
    }

    public function test_update_requires_sms_recipient_id(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('SMS number id cannot be empty.');

        $this->smsRecipient->update('', 'active');
    }

    public function test_update_rejects_invalid_status(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Value "invalid_status" is not an element of the valid values: active, opt_out');

        $this->smsRecipient->update('sms_number_id', 'invalid_status');
    }

    public function test_update_sms_recipient_with_active_status(): void
    {
        $this->addSuccessResponse();

        $response = $this->smsRecipient->update('random_id', 'active');

        $body = $this->assertRequest('PUT', '/v1/sms-recipients/random_id');

        self::assertEquals(200, $response['status_code']);
        self::assertSame('active', Arr::get($body, 'status'));
    }

    public function test_update_sms_recipient_with_opt_out_status(): void
    {
        $this->addSuccessResponse();

        $response = $this->smsRecipient->update('random_id', 'opt_out');

        $body = $this->assertRequest('PUT', '/v1/sms-recipients/random_id');

        self::assertEquals(200, $response['status_code']);
        self::assertSame('opt_out', Arr::get($body, 'status'));
    }

    public static function invalidSmsRecipientListDataProvider(): array
    {
        return [
            'limit below minimum' => [
                (new SmsRecipientParams())
                    ->setLimit(9),
                'Minimum limit is 10.',
            ],
            'limit above maximum' => [
                (new SmsRecipientParams())
                    ->setLimit(101),
                'Maximum limit is 100.',
            ],
            'status contains invalid value' => [
                (new SmsRecipientParams())
                    ->setStatus('test'),
                'Value "test" is not an element of the valid values: active, opt_out',
            ],
        ];
    }
}
