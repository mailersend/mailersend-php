<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\SmsWebhook;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Builder\SmsWebhookParams;
use MailerSend\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Http\Message\ResponseInterface;
use MailerSend\Helpers\Arr;

class SmsWebhookTest extends TestCase
{
    protected SmsWebhook $smsWebhook;
    protected ResponseInterface $defaultResponse;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = new Client();

        $this->smsWebhook = new SmsWebhook(new HttpLayer(self::OPTIONS, $this->client), self::OPTIONS);
        $this->defaultResponse = $this->createStub(ResponseInterface::class);
        $this->defaultResponse->method('getStatusCode')->willReturn(200);
    }

    public function test_get_webhooks_sms_number_id_is_required()
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('SMS number id is required.');

        $this->smsWebhook->get('');
    }

    public function test_find_webhook_is_validated()
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('SMS webhook id is required.');

        $this->smsWebhook->find('');
    }

    public function test_delete_webhook_is_validated()
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('SMS webhook id is required.');

        $this->smsWebhook->delete('');
    }

    public function test_get_sms_webhooks()
    {
        $this->addSuccessResponse();

        $response = $this->smsWebhook->get('hashed_sms_number_id');

        $body = $this->assertRequest('GET', '/v1/sms-webhooks');

        self::assertEquals(200, $response['status_code']);
        self::assertSame('hashed_sms_number_id', Arr::get($body, 'sms_number_id'));
    }

    public function test_get_sms_webhooks_with_limit()
    {
        $this->addSuccessResponse();

        $response = $this->smsWebhook->get('hashed_sms_number_id', 10);

        $body = $this->assertRequest('GET', '/v1/sms-webhooks');

        self::assertEquals(200, $response['status_code']);
        self::assertSame('hashed_sms_number_id', Arr::get($body, 'sms_number_id'));
        self::assertSame(10, Arr::get($body, 'limit'));
    }

    public function test_get_sms_webhooks_with_page()
    {
        $this->addSuccessResponse();

        $response = $this->smsWebhook->get('hashed_sms_number_id', null, 2);

        $body = $this->assertRequest('GET', '/v1/sms-webhooks');

        self::assertEquals(200, $response['status_code']);
        self::assertSame('hashed_sms_number_id', Arr::get($body, 'sms_number_id'));
        self::assertSame(2, Arr::get($body, 'page'));
    }

    public function test_get_sms_webhooks_excludes_null_limit_and_page(): void
    {
        $this->addSuccessResponse();

        $this->smsWebhook->get('hashed_sms_number_id');

        $body = $this->assertRequest('GET', '/v1/sms-webhooks');

        $this->assertBodyExcludes(['limit', 'page'], $body);
    }

    public function test_find_webhook(): void
    {
        $this->addSuccessResponse();

        $response = $this->smsWebhook->find('hashed_sms_webhook_id');

        $this->assertRequest('GET', '/v1/sms-webhooks/hashed_sms_webhook_id');

        self::assertEquals(200, $response['status_code']);
    }

    public function test_delete_sms_webhook(): void
    {
        $this->addSuccessResponse();

        $response = $this->smsWebhook->delete('hashed_sms_webhook_id');

        $this->assertRequest('DELETE', '/v1/sms-webhooks/hashed_sms_webhook_id');

        self::assertEquals(200, $response['status_code']);
    }

    /**
     * @dataProvider invalidCreateParamsProvider
     */
    #[DataProvider('invalidCreateParamsProvider')]
    public function test_create_rejects_invalid_params(SmsWebhookParams $params, string $exceptionMessage): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $this->smsWebhook->create($params);
    }

    public static function invalidCreateParamsProvider(): array
    {
        return [
            'invalid url' => [
                new SmsWebhookParams('invalid_url', 'SMS webhook name', SmsWebhookParams::ALL_ACTIVITIES, 'hashed_sms_number_id'),
                'Invalid URL.',
            ],
            'missing name' => [
                new SmsWebhookParams('https://link.com/webhook', '', SmsWebhookParams::ALL_ACTIVITIES, 'hashed_sms_number_id'),
                'Webhook name is required.',
            ],
            'name too long' => [
                new SmsWebhookParams('https://link.com/webhook', str_repeat('a', 192), SmsWebhookParams::ALL_ACTIVITIES, 'hashed_sms_number_id'),
                'Webhook name cannot be longer than 191 character.',
            ],
            'missing events' => [
                new SmsWebhookParams('https://link.com/webhook', 'webhook name', [], 'hashed_sms_number_id'),
                'Webhook events are required.',
            ],
            'missing sms number id' => [
                new SmsWebhookParams('https://link.com/webhook', 'webhook name', SmsWebhookParams::ALL_ACTIVITIES, ''),
                'SMS number id is required.',
            ],
        ];
    }

    public function test_create_rejects_invalid_events(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('One or multiple invalid events.');

        $this->smsWebhook->create(
            new SmsWebhookParams('https://link.com/webhook', 'webhook name', ['invalid event'], 'hashed_sms_number_id')
        );
    }

    public function test_create_sms_webhooks(): void
    {
        $this->addSuccessResponse();

        $response = $this->smsWebhook->create(
            new SmsWebhookParams('https://link.com/webhook', 'webhook name', SmsWebhookParams::ALL_ACTIVITIES, 'hashed_sms_number_id')
        );

        $body = $this->assertRequest('POST', '/v1/sms-webhooks');

        self::assertEquals(200, $response['status_code']);
        $this->assertBodyContains([
            'url' => 'https://link.com/webhook',
            'name' => 'webhook name',
            'sms_number_id' => 'hashed_sms_number_id',
        ], $body);
        self::assertSame(SmsWebhookParams::ALL_ACTIVITIES, Arr::get($body, 'events'));
        $this->assertBodyExcludes(['enabled'], $body);
    }

    public function test_create_disabled_sms_webhooks(): void
    {
        $this->addSuccessResponse();

        $response = $this->smsWebhook->create(
            new SmsWebhookParams('https://link.com/webhook', 'webhook name', SmsWebhookParams::ALL_ACTIVITIES, 'hashed_sms_number_id', false)
        );

        $body = $this->assertRequest('POST', '/v1/sms-webhooks');

        self::assertEquals(200, $response['status_code']);
        self::assertFalse(Arr::get($body, 'enabled'));
    }

    public function test_create_enabled_sms_webhooks(): void
    {
        $this->addSuccessResponse();

        $response = $this->smsWebhook->create(
            new SmsWebhookParams('https://link.com/webhook', 'webhook name', SmsWebhookParams::ALL_ACTIVITIES, 'hashed_sms_number_id', true)
        );

        $body = $this->assertRequest('POST', '/v1/sms-webhooks');

        self::assertEquals(200, $response['status_code']);
        self::assertTrue(Arr::get($body, 'enabled'));
    }

    public function test_update_sms_webhook_requires_id()
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('SMS webhook id is required.');

        $this->smsWebhook->update(
            '',
            new SmsWebhookParams(
                'https://link.com/webhook',
                'webhook name',
                SmsWebhookParams::ALL_ACTIVITIES
            )
        );
    }

    public function test_update_sms_webhooks(): void
    {
        $this->addSuccessResponse();

        $response = $this->smsWebhook->update(
            'random_id',
            new SmsWebhookParams('https://link.com/webhook', 'Webhook name', [SmsWebhookParams::SMS_FAILED, SmsWebhookParams::SMS_SENT])
        );

        $body = $this->assertRequest('PUT', '/v1/sms-webhooks/random_id');

        self::assertEquals(200, $response['status_code']);
        $this->assertBodyContains([
            'url' => 'https://link.com/webhook',
            'name' => 'Webhook name',
        ], $body);
        self::assertSame([SmsWebhookParams::SMS_FAILED, SmsWebhookParams::SMS_SENT], Arr::get($body, 'events'));
        $this->assertBodyExcludes(['enabled'], $body);
    }

    public function test_update_sms_webhooks_excludes_null_optional_fields(): void
    {
        $this->addSuccessResponse();

        $this->smsWebhook->update(
            'random_id',
            new SmsWebhookParams()
        );

        $body = $this->assertRequest('PUT', '/v1/sms-webhooks/random_id');

        $this->assertBodyExcludes(['url', 'name', 'events', 'enabled', 'sms_number_id'], $body);
    }

    public function test_enable_sms_webhooks(): void
    {
        $this->addSuccessResponse();

        $smsWebhookParams = new SmsWebhookParams();
        $smsWebhookParams->setEnabled(true);

        $response = $this->smsWebhook->update('random_id', $smsWebhookParams);

        $body = $this->assertRequest('PUT', '/v1/sms-webhooks/random_id');

        self::assertEquals(200, $response['status_code']);
        self::assertTrue(Arr::get($body, 'enabled'));
        $this->assertBodyExcludes(['url', 'name', 'events', 'sms_number_id'], $body);
    }

    public function test_disable_sms_webhooks(): void
    {
        $this->addSuccessResponse();

        $smsWebhookParams = new SmsWebhookParams();
        $smsWebhookParams->setEnabled(false);

        $response = $this->smsWebhook->update('random_id', $smsWebhookParams);

        $body = $this->assertRequest('PUT', '/v1/sms-webhooks/random_id');

        self::assertEquals(200, $response['status_code']);
        self::assertFalse(Arr::get($body, 'enabled'));
        $this->assertBodyExcludes(['url', 'name', 'events', 'sms_number_id'], $body);
    }
}
