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

    #[DataProvider('validWhatsappParamsProvider')]
    public function test_send_whatsapp(WhatsappParams $params): void
    {
        $response = $this->createStub(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(202);

        $this->client->addResponse($response);

        $result = $this->whatsapp->send($params);

        $request = $this->client->getLastRequest();
        $request_body = json_decode((string) $request->getBody(), true);

        self::assertEquals('POST', $request->getMethod());
        self::assertEquals('/v1/whatsapp/send', $request->getUri()->getPath());
        self::assertEquals(202, $result['status_code']);

        self::assertEquals($params->getFrom(), Arr::get($request_body, 'from'));
        self::assertEquals($params->getTo(), Arr::get($request_body, 'to'));
        self::assertEquals($params->getTemplateId(), Arr::get($request_body, 'template_id'));
        self::assertCount(count($params->getPersonalization()), Arr::get($request_body, 'personalization') ?? []);
    }

    #[DataProvider('invalidWhatsappParamsProvider')]
    public function test_send_whatsapp_with_errors(WhatsappParams $params): void
    {
        $this->expectException(MailerSendAssertException::class);

        $httpLayer = $this->createStub(HttpLayer::class);
        $httpLayer->method('post')
            ->withAnyParameters()
            ->willReturn([]);

        (new Whatsapp($httpLayer, self::OPTIONS))->send($params);
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
            'with personalization' => [
                (new WhatsappParams())
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
                    ]),
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
            ],
            'at least one recipient' => [
                (new WhatsappParams())
                    ->setFrom('12345678901')
                    ->setTo([])
                    ->setTemplateId('template_id_123'),
            ],
            'template_id is required' => [
                (new WhatsappParams())
                    ->setFrom('12345678901')
                    ->setTo(['19191234567']),
            ],
            'max 10 recipients' => [
                (new WhatsappParams())
                    ->setFrom('12345678901')
                    ->setTo(['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11'])
                    ->setTemplateId('template_id_123'),
            ],
        ];
    }
}