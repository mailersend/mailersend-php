<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\Whatsapp;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Builder\WhatsappParams;
use MailerSend\Helpers\Builder\WhatsappPersonalization;
use MailerSend\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Http\Message\ResponseInterface;
use MailerSend\Helpers\Arr;

class WhatsappTest extends TestCase
{
    protected Whatsapp $whatsapp;
    protected ResponseInterface $defaultResponse;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = new Client();

        $this->whatsapp = new Whatsapp(new HttpLayer(self::OPTIONS, $this->client), self::OPTIONS);

        $this->defaultResponse = $this->createStub(ResponseInterface::class);
        $this->defaultResponse->method('getStatusCode')->willReturn(202);
    }

    /**
     * @dataProvider validWhatsappParamsProvider
     * @param WhatsappParams $params
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    #[DataProvider('validWhatsappParamsProvider')]
    public function test_send_whatsapp(WhatsappParams $params): void
    {
        $this->addSuccessResponse(202);

        $result = $this->whatsapp->send($params);

        $request_body = $this->assertRequest('POST', '/v1/whatsapp/send');

        self::assertEquals(202, $result['status_code']);
        self::assertEquals($params->getFrom(), Arr::get($request_body, 'from'));
        self::assertEquals($params->getTo(), Arr::get($request_body, 'to'));
        self::assertEquals($params->getTemplateId(), Arr::get($request_body, 'template_id'));
    }

    public function test_send_whatsapp_with_personalization(): void
    {
        $this->addSuccessResponse(202);

        $params = (new WhatsappParams())
            ->setFrom('12345678901')
            ->setTo(['19191234567', '19199876543'])
            ->setTemplateId('template_id_123')
            ->setPersonalization([
                (new WhatsappPersonalization('19191234567'))
                    ->setHeader(['John'])
                    ->setBody(['order #1234', 'tomorrow'])
                    ->setButtons(['https://example.com/track/1234']),
                (new WhatsappPersonalization('19199876543'))
                    ->setHeader(['Jane'])
                    ->setBody(['order #5678', 'Friday']),
            ]);

        $this->whatsapp->send($params);

        $request_body = $this->assertRequest('POST', '/v1/whatsapp/send');

        self::assertSame([
            [
                'to' => '19191234567',
                'data' => [
                    'header' => ['John'],
                    'body' => ['order #1234', 'tomorrow'],
                    'buttons' => ['https://example.com/track/1234'],
                ],
            ],
            [
                'to' => '19199876543',
                'data' => [
                    'header' => ['Jane'],
                    'body' => ['order #5678', 'Friday'],
                ],
            ],
        ], Arr::get($request_body, 'personalization'));
    }

    public function test_send_whatsapp_omits_personalization_when_not_set(): void
    {
        $this->addSuccessResponse(202);

        $params = (new WhatsappParams())
            ->setFrom('12345678901')
            ->setTo(['19191234567'])
            ->setTemplateId('template_id_123');

        $this->whatsapp->send($params);

        $request_body = $this->assertRequest('POST', '/v1/whatsapp/send');

        $this->assertBodyExcludes(['personalization'], $request_body);
    }

    /**
     * @dataProvider invalidWhatsappParamsProvider
     * @param WhatsappParams $params
     * @param string $expectedMessage
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    #[DataProvider('invalidWhatsappParamsProvider')]
    public function test_send_whatsapp_rejects_invalid_params(WhatsappParams $params, string $expectedMessage): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($expectedMessage);

        $this->whatsapp->send($params);
    }

    public static function validWhatsappParamsProvider(): array
    {
        return [
            'simple request' => [
                (new WhatsappParams())
                    ->setFrom('12345678901')
                    ->setTo(['19191234567'])
                    ->setTemplateId('template_id_123'),
            ],
            'using addRecipient helper' => [
                (new WhatsappParams())
                    ->setFrom('12345678901')
                    ->addRecipient('19191234567')
                    ->addRecipient('19199876543')
                    ->setTemplateId('template_id_123'),
            ],
        ];
    }

    public static function invalidWhatsappParamsProvider(): array
    {
        return [
            'from is required' => [
                (new WhatsappParams())
                    ->setTo(['19191234567'])
                    ->setTemplateId('template_id_123'),
                'From phone number is required',
            ],
            'at least one recipient' => [
                (new WhatsappParams())
                    ->setFrom('12345678901')
                    ->setTo([])
                    ->setTemplateId('template_id_123'),
                'At least one recipient is required',
            ],
            'template_id is required' => [
                (new WhatsappParams())
                    ->setFrom('12345678901')
                    ->setTo(['19191234567']),
                'Template ID is required',
            ],
            'max 10 recipients' => [
                (new WhatsappParams())
                    ->setFrom('12345678901')
                    ->setTo(['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11'])
                    ->setTemplateId('template_id_123'),
                'Maximum 10 recipients allowed',
            ],
        ];
    }
}
