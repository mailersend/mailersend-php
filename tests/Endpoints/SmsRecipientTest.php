<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use Illuminate\Support\Arr;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\SmsRecipient;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Builder\SmsRecipientParams;
use MailerSend\Tests\TestCase;
use Psr\Http\Message\ResponseInterface;

class SmsRecipientTest extends TestCase
{
    protected SmsRecipient $smsRecipient;
    protected ResponseInterface $defaultResponse;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = new Client();

        $this->smsRecipient = new SmsRecipient(new HttpLayer(self::OPTIONS, $this->client), self::OPTIONS);

        $this->defaultResponse = $this->createMock(ResponseInterface::class);
        $this->defaultResponse->method('getStatusCode')->willReturn(200);
    }

    /**
     * @dataProvider validSmsRecipientListDataProvider
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_get_all(SmsRecipientParams $smsRecipientParams): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $this->client->addResponse($response);

        $response = $this->smsRecipient->getAll($smsRecipientParams);

        $request = $this->client->getLastRequest();

        parse_str($request->getUri()->getQuery(), $query);

        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/v1/sms-recipients', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);

        self::assertEquals($smsRecipientParams->getSmsNumberId(), Arr::get($query, 'sms_number_id'));
        self::assertEquals($smsRecipientParams->getPage(), Arr::get($query, 'page'));
        self::assertEquals($smsRecipientParams->getLimit(), Arr::get($query, 'limit'));
        self::assertEquals($smsRecipientParams->getStatus(), Arr::get($query, 'status'));
    }

    /**
     * @dataProvider invalidSmsRecipientListDataProvider
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_get_all_with_errors(SmsRecipientParams $smsRecipientParams): void
    {
        $this->expectException(MailerSendAssertException::class);

        $httpLayer = $this->createMock(HttpLayer::class);
        $httpLayer->method('get')
            ->withAnyParameters()
            ->willReturn([]);

        (new SmsRecipient($httpLayer, self::OPTIONS))->getAll($smsRecipientParams);
    }

    public function test_find_requires_sms_recipient_id()
    {
        $this->expectException(MailerSendAssertException::class);

        $this->smsRecipient->find('');
    }

    public function test_update_requires_sms_recipient_id()
    {
        $this->expectException(MailerSendAssertException::class);

        $this->smsRecipient->update('', 'active');
    }

    public function test_update_requires_status()
    {
        $this->expectException(MailerSendAssertException::class);

        $this->smsRecipient->update('sms_number_id', 'test');
    }

    public function test_update_sms_recipient()
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $this->client->addResponse($response);

        $response = $this->smsRecipient->update('random_id', 'active');

        $request = $this->client->getLastRequest();
        $request_body = json_decode((string) $request->getBody(), true);

        self::assertEquals('PUT', $request->getMethod());
        self::assertEquals('/v1/sms-recipients/random_id', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);

        self::assertSame('active', Arr::get($request_body, 'status'));
    }

    public function validSmsRecipientListDataProvider(): array
    {
        return [
            'no params' => [
                (new SmsRecipientParams()),
            ],
            'with sms number id' => [
                (new SmsRecipientParams())
                    ->setSmsNumberId('hashed_sms_number_id'),
            ],
            'with page' => [
                (new SmsRecipientParams())
                    ->setPage(3),
            ],
            'with limit' => [
                (new SmsRecipientParams())
                    ->setLimit(15),
            ],
            'with status' => [
                (new SmsRecipientParams())
                    ->setStatus('opt_out'),
            ],
            'with all' => [
                (new SmsRecipientParams())
                    ->setSmsNumberId('hashed_sms_number_id')
                    ->setPage(3)
                    ->setLimit(15)
                    ->setStatus('opt_out'),
            ]
        ];
    }

    public function invalidSmsRecipientListDataProvider(): array
    {
        return [
            'limit under 10' => [
                (new SmsRecipientParams())
                    ->setLimit(9),
            ],
            'limit over 100' => [
                (new SmsRecipientParams())
                    ->setLimit(101),
            ],
            'status is not a possible type' => [
                (new SmsRecipientParams())
                    ->setStatus('test'),
            ],
        ];
    }
}
