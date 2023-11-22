<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\Sms;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Exceptions\MailerSendValidationException;
use MailerSend\Helpers\Builder\SmsParams;
use MailerSend\Helpers\Builder\SmsPersonalization;
use MailerSend\Tests\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Tightenco\Collect\Support\Arr;

class SmsTest extends TestCase
{
    protected Sms $sms;
    protected ResponseInterface $defaultResponse;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = new Client();

        $this->sms = new Sms(new HttpLayer(self::OPTIONS, $this->client), self::OPTIONS);

        $this->defaultResponse = $this->createMock(ResponseInterface::class);
        $this->defaultResponse->method('getStatusCode')->willReturn(200);
    }

    /**
     * @dataProvider validSmsParamsProvider
     * @param SmsParams $smsParams
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_send_sms(SmsParams $smsParams): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $this->client->addResponse($response);

        $response = $this->sms->send($smsParams);

        $request = $this->client->getLastRequest();
        $request_body = json_decode((string) $request->getBody(), true);

        self::assertEquals('POST', $request->getMethod());
        self::assertEquals('/v1/sms', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);

        self::assertEquals($smsParams->getFrom(), Arr::get($request_body, 'from'));
        self::assertEquals($smsParams->getTo(), Arr::get($request_body, 'to'));
        self::assertEquals($smsParams->getText(), Arr::get($request_body, 'text'));
        self::assertCount(count($smsParams->getPersonalization()), Arr::get($request_body, 'personalization') ?? []);

        foreach ($smsParams->getPersonalization() as $key => $personalization) {
            $personalization = !is_array($personalization) ? $personalization->toArray() : $personalization;
            self::assertEquals($personalization['phone_number'], Arr::get($request_body, "personalization.$key.phone_number"));
            foreach ($personalization['data'] as $variableKey => $variableValue) {
                self::assertEquals($personalization['data'][$variableKey], Arr::get($request_body, "personalization.$key.data.$variableKey"));
            }
        }
    }

    /**
     * @dataProvider invalidSmsParamsProvider
     * @param SmsParams $smsParams
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_send_email_with_errors(SmsParams $smsParams)
    {
        $this->expectException(MailerSendAssertException::class);

        $httpLayer = $this->createMock(HttpLayer::class);
        $httpLayer->method('post')
            ->withAnyParameters()
            ->willReturn([]);

        (new Sms($httpLayer, self::OPTIONS))->send($smsParams);
    }

    public function validSmsParamsProvider(): array
    {
        return [
            'simple request' => [
                (new SmsParams())
                    ->setFrom('+1111111111')
                    ->setTo([
                        '+2222222222',
                        '+3333333333',
                    ])
                    ->setText('Text')
            ],
            'using recipients helper' => [
                (new SmsParams())
                    ->setFrom('+1111111111')
                    ->addRecipient('+2222222222')
                    ->setText('TEXT'),
            ],
            'using personalization helper' => [
                (new SmsParams())
                    ->setFrom('+1111111111')
                    ->addRecipient('+2222222222')
                    ->setText('TEXT')
                    ->setPersonalization([
                        new SmsPersonalization('+2222222222', [
                            'var' => 'variable',
                            'number' => 123,
                            'object' => [
                                'key' => 'object-value'
                            ],
                            'objectCollection' => [
                                [
                                    'name' => 'John'
                                ],
                                [
                                    'name' => 'Patrick'
                                ]
                            ],
                        ])
                    ]),
            ],
        ];
    }

    public function invalidSmsParamsProvider(): array
    {
        return [
            'from is required' => [
                (new SmsParams())
                    ->setTo(['+222222222'])
                    ->setText('TEXT')
            ],
            'at least one recipients' => [
                (new SmsParams())
                    ->setFrom('+1111111111')
                    ->setTo([])
                    ->setText('TEXT')
            ],
            'text is required' => [
                (new SmsParams())
                    ->setFrom('+1111111111')
                    ->setTo(['+2222222222'])
                    ->setText('')
            ],
        ];
    }
}
