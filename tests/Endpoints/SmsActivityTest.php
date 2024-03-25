<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use Illuminate\Support\Arr;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\SmsActivity;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Builder\SmsActivityParams;
use MailerSend\Tests\TestCase;
use Psr\Http\Message\ResponseInterface;

class SmsActivityTest extends TestCase
{
    protected SmsActivity $smsActivity;
    protected ResponseInterface $defaultResponse;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = new Client();

        $this->smsActivity = new SmsActivity(new HttpLayer(self::OPTIONS, $this->client), self::OPTIONS);

        $this->defaultResponse = $this->createMock(ResponseInterface::class);
        $this->defaultResponse->method('getStatusCode')->willReturn(200);
    }

    /**
     * @dataProvider validSmsActivityParamsProvider
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_get_all(SmsActivityParams $smsActivityParams): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $this->client->addResponse($response);

        $response = $this->smsActivity->getAll($smsActivityParams);

        $request = $this->client->getLastRequest();

        parse_str($request->getUri()->getQuery(), $query);

        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/v1/sms-activity', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);

        self::assertEquals($smsActivityParams->getSmsNumberId(), Arr::get($query, 'sms_number_id'));
        self::assertEquals($smsActivityParams->getPage(), Arr::get($query, 'page'));
        self::assertEquals($smsActivityParams->getLimit(), Arr::get($query, 'limit'));
        self::assertEquals($smsActivityParams->getDateFrom(), Arr::get($query, 'date_from'));
        self::assertEquals($smsActivityParams->getDateTo(), Arr::get($query, 'date_to'));
        self::assertEquals($smsActivityParams->getStatus(), Arr::get($query, 'status', []));
    }

    /**
     * @dataProvider invalidSmsActivityParamsProvider
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function test_get_all_with_errors(SmsActivityParams $smsActivityParams): void
    {
        $this->expectException(MailerSendAssertException::class);

        $httpLayer = $this->createMock(HttpLayer::class);
        $httpLayer->method('get')
            ->withAnyParameters()
            ->willReturn([]);

        (new SmsActivity($httpLayer, self::OPTIONS))->getAll($smsActivityParams);
    }

    public function validSmsActivityParamsProvider(): array
    {
        return [
            'no params' => [
                (new SmsActivityParams()),
            ],
            'with sms number id' => [
                (new SmsActivityParams())
                    ->setSmsNumberId('hashed_sms_number_id'),
            ],
            'with page' => [
                (new SmsActivityParams())
                    ->setPage(3),
            ],
            'with limit' => [
                (new SmsActivityParams())
                    ->setLimit(15),
            ],
            'with dates' => [
                (new SmsActivityParams())
                    ->setDateFrom(1623073576)
                    ->setDateTo(1623074976),
            ],
            'with events' => [
                (new SmsActivityParams())
                    ->setStatus(['queued', 'sent']),
            ],
            'with all' => [
                (new SmsActivityParams())
                    ->setSmsNumberId('hashed_sms_number_id')
                    ->setPage(3)
                    ->setLimit(15)
                    ->setDateFrom(1623073576)
                    ->setDateTo(1623074976)
                    ->setStatus(['queued', 'sent']),
            ]
        ];
    }

    public function invalidSmsActivityParamsProvider(): array
    {
        return [
            'limit under 10' => [
                (new SmsActivityParams())
                    ->setLimit(9),
            ],
            'limit over 100' => [
                (new SmsActivityParams())
                    ->setLimit(101),
            ],
            'date_from greater than date_to' => [
                (new SmsActivityParams())
                    ->setDateFrom(1623074976)
                    ->setDateTo(1623074975),
            ],
            'status is not a possible type' => [
                (new SmsActivityParams())
                    ->setStatus(['invalid_type', 'queued']),
            ],
        ];
    }
}
