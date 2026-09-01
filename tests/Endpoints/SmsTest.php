<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\Sms;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Builder\SmsParams;
use MailerSend\Helpers\Builder\SmsPersonalization;
use MailerSend\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class SmsTest extends TestCase
{
    protected Sms $sms;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = new Client();

        $this->sms = new Sms(new HttpLayer(self::OPTIONS, $this->client), self::OPTIONS);
    }

    /**
     * @dataProvider validSmsParamsProvider
     * @param SmsParams $smsParams
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    #[DataProvider('validSmsParamsProvider')]
    public function test_send(SmsParams $smsParams): void
    {
        $this->addSuccessResponse();

        $response = $this->sms->send($smsParams);

        $body = $this->assertRequest('POST', '/v1/sms');

        self::assertEquals(200, $response['status_code']);
        self::assertEquals($smsParams->getFrom(), $body['from'] ?? null);
        self::assertEquals($smsParams->getTo(), $body['to'] ?? null);
        self::assertEquals($smsParams->getText(), $body['text'] ?? null);

        if (!empty($smsParams->getPersonalization())) {
            self::assertCount(count($smsParams->getPersonalization()), $body['personalization'] ?? []);
        }
    }

    public function test_send_includes_personalization_when_set(): void
    {
        $this->addSuccessResponse();

        $params = (new SmsParams())
            ->setFrom('+1111111111')
            ->addRecipient('+2222222222')
            ->setText('TEXT')
            ->setPersonalization([
                new SmsPersonalization('+2222222222', [
                    'var' => 'variable',
                    'number' => 123,
                ]),
            ]);

        $this->sms->send($params);

        $body = $this->assertRequest('POST', '/v1/sms');

        self::assertArrayHasKey('personalization', $body);
        self::assertCount(1, $body['personalization']);
        self::assertSame('+2222222222', $body['personalization'][0]['phone_number']);
        self::assertSame('variable', $body['personalization'][0]['data']['var'] ?? null);
        self::assertSame(123, $body['personalization'][0]['data']['number'] ?? null);
    }

    public function test_send_excludes_personalization_when_not_set(): void
    {
        $this->addSuccessResponse();

        $params = (new SmsParams())
            ->setFrom('+1111111111')
            ->addRecipient('+2222222222')
            ->setText('TEXT');

        $this->sms->send($params);

        $body = $this->assertRequest('POST', '/v1/sms');

        $this->assertBodyExcludes(['personalization'], $body);
    }

    /**
     * @dataProvider invalidSmsParamsProvider
     * @param SmsParams $smsParams
     * @param string $expectedMessage
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    #[DataProvider('invalidSmsParamsProvider')]
    public function test_send_rejects_invalid_params(SmsParams $smsParams, string $expectedMessage): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($expectedMessage);

        $httpLayer = $this->createStub(HttpLayer::class);
        $httpLayer->method('post')
            ->withAnyParameters()
            ->willReturn([]);

        (new Sms($httpLayer, self::OPTIONS))->send($smsParams);
    }

    public static function validSmsParamsProvider(): array
    {
        return [
            'simple request' => [
                (new SmsParams())
                    ->setFrom('+1111111111')
                    ->setTo([
                        '+2222222222',
                        '+3333333333',
                    ])
                    ->setText('Text'),
            ],
            'using addRecipient helper' => [
                (new SmsParams())
                    ->setFrom('+1111111111')
                    ->addRecipient('+2222222222')
                    ->setText('TEXT'),
            ],
            'with multiple recipients' => [
                (new SmsParams())
                    ->setFrom('+1111111111')
                    ->addRecipient('+2222222222')
                    ->addRecipient('+3333333333')
                    ->setText('TEXT'),
            ],
        ];
    }

    public static function invalidSmsParamsProvider(): array
    {
        return [
            'from is required' => [
                (new SmsParams())
                    ->setTo(['+222222222'])
                    ->setText('TEXT'),
                'From phone number is required',
            ],
            'from must start with plus' => [
                (new SmsParams())
                    ->setFrom('1111111111')
                    ->setTo(['+2222222222'])
                    ->setText('TEXT'),
                'From phone number must start with +',
            ],
            'at least one recipient required' => [
                (new SmsParams())
                    ->setFrom('+1111111111')
                    ->setTo([])
                    ->setText('TEXT'),
                'At least one recipient is required',
            ],
            'recipient must start with plus' => [
                (new SmsParams())
                    ->setFrom('+1111111111')
                    ->setTo(['2222222222'])
                    ->setText('TEXT'),
                'Recipient phone number must start with +',
            ],
            'text is required' => [
                (new SmsParams())
                    ->setFrom('+1111111111')
                    ->setTo(['+2222222222'])
                    ->setText(''),
                'Text cannot be empty',
            ],
        ];
    }
}
