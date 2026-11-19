<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\Account;
use MailerSend\Tests\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class AccountTest extends TestCase
{
    protected Account $account;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = new Client();
        $this->account = new Account(new HttpLayer(self::OPTIONS, $this->client), self::OPTIONS);
    }

    public function test_get_uses_correct_method_and_path(): void
    {
        $this->addSuccessResponse();
        $this->account->get();
        $this->assertRequest('GET', '/v1/account');
    }

    public function test_get_sends_empty_body(): void
    {
        $this->addSuccessResponse();
        $this->account->get();
        $body = $this->assertRequest('GET', '/v1/account');
        self::assertEmpty($body);
    }

    public function test_get_forwards_status_code(): void
    {
        $this->addSuccessResponse(200);
        $response = $this->account->get();
        self::assertEquals(200, $response['status_code']);
    }

    public function test_get_parses_account_response(): void
    {
        $this->addJsonResponse(self::accountResponse());

        $data = $this->account->get()['body']['data'];

        self::assertEquals('Acme Inc', $data['name']);
        self::assertEquals('approved', $data['status']);
        self::assertEquals('starter', $data['plan_type']);
        self::assertSame(7, $data['data_retention_days']);
        self::assertSame(['sent' => 12000, 'received' => 430, 'limit' => -1], $data['emails']);
        self::assertSame(
            ['general' => 60, 'email' => 120, 'bulk_email' => 10, 'activity' => 10],
            $data['rate_limits']
        );
        self::assertSame(['used' => 3, 'limit' => 10], $data['resources']['domains']);
        self::assertSame(['used' => 2, 'limit' => 5], $data['resources']['users']);
        self::assertSame(['used' => 0, 'limit' => 0], $data['resources']['free_accountant_seat']);
        self::assertSame(['used' => 41, 'limit' => 250], $data['resources']['templates']);
        self::assertSame(['used' => 1, 'limit' => 50], $data['resources']['smtp_users']);
        self::assertSame(['used' => 4, 'limit' => 50], $data['resources']['api_tokens']);
        self::assertSame(['used' => 2, 'limit' => 50], $data['resources']['webhooks']);
        self::assertSame(['sent' => 120, 'received' => 30, 'limit' => -1], $data['addons']['sms']);
        self::assertSame(['sent' => 800, 'limit' => 1000], $data['addons']['whatsapp']);
        self::assertSame(1, $data['addons']['phone_numbers']);
        self::assertSame(2, $data['addons']['domain_spaces']);
        self::assertSame(30, $data['addons']['data_retention_days']);
        self::assertSame(0, $data['addons']['dedicated_ips']);
    }

    public function test_get_parses_free_accountant_seat_and_nullable_fields(): void
    {
        $payload = self::accountResponse();
        $payload['data']['resources']['free_accountant_seat'] = ['used' => 1, 'limit' => 1];
        $payload['data']['resources']['webhooks']['limit'] = null;
        $payload['data']['addons']['whatsapp'] = null;
        $this->addJsonResponse($payload);

        $data = $this->account->get()['body']['data'];

        self::assertSame(['used' => 1, 'limit' => 1], $data['resources']['free_accountant_seat']);
        self::assertArrayHasKey('limit', $data['resources']['webhooks']);
        self::assertNull($data['resources']['webhooks']['limit']);
        self::assertArrayHasKey('whatsapp', $data['addons']);
        self::assertNull($data['addons']['whatsapp']);
    }

    protected static function accountResponse(): array
    {
        return [
            'data' => [
                'name' => 'Acme Inc',
                'status' => 'approved',
                'plan_type' => 'starter',
                'data_retention_days' => 7,
                'emails' => ['sent' => 12000, 'received' => 430, 'limit' => -1],
                'rate_limits' => ['general' => 60, 'email' => 120, 'bulk_email' => 10, 'activity' => 10],
                'resources' => [
                    'domains' => ['used' => 3, 'limit' => 10],
                    'users' => ['used' => 2, 'limit' => 5],
                    'free_accountant_seat' => ['used' => 0, 'limit' => 0],
                    'templates' => ['used' => 41, 'limit' => 250],
                    'smtp_users' => ['used' => 1, 'limit' => 50],
                    'api_tokens' => ['used' => 4, 'limit' => 50],
                    'webhooks' => ['used' => 2, 'limit' => 50],
                ],
                'addons' => [
                    'sms' => ['sent' => 120, 'received' => 30, 'limit' => -1],
                    'whatsapp' => ['sent' => 800, 'limit' => 1000],
                    'phone_numbers' => 1,
                    'domain_spaces' => 2,
                    'data_retention_days' => 30,
                    'dedicated_ips' => 0,
                ],
            ],
        ];
    }

    /**
     * @throws \JsonException
     */
    protected function addJsonResponse(array $payload, int $statusCode = 200): void
    {
        $body = $this->createStub(StreamInterface::class);
        $body->method('getContents')->willReturn(json_encode($payload, JSON_THROW_ON_ERROR));

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn($statusCode);
        $response->method('hasHeader')->willReturn(true);
        $response->method('getHeader')->willReturn(['application/json']);
        $response->method('getHeaders')->willReturn(['Content-Type' => ['application/json']]);
        $response->method('getBody')->willReturn($body);

        $this->client->addResponse($response);
    }
}
