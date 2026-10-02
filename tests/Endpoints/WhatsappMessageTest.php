<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\WhatsappMessage;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class WhatsappMessageTest extends TestCase
{
    protected WhatsappMessage $whatsappMessage;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = new Client();

        $this->whatsappMessage = new WhatsappMessage(new HttpLayer(self::OPTIONS, $this->client), self::OPTIONS);
    }

    public function test_get_all_uses_correct_method_and_path(): void
    {
        $this->addSuccessResponse();

        $response = $this->whatsappMessage->getAll();

        $this->assertRequest('GET', '/v1/whatsapp/messages');
        self::assertEquals(200, $response['status_code']);
    }

    public function test_get_all_sends_page_and_limit_params(): void
    {
        $this->addSuccessResponse();

        $this->whatsappMessage->getAll(3, 50);

        parse_str($this->client->getLastRequest()->getUri()->getQuery(), $query);

        $this->assertQueryParams(['page' => '3', 'limit' => '50'], $query);
    }

    public function test_get_all_excludes_optional_params_when_not_set(): void
    {
        $this->addSuccessResponse();

        $this->whatsappMessage->getAll(null, null);

        parse_str($this->client->getLastRequest()->getUri()->getQuery(), $query);

        self::assertArrayNotHasKey('page', $query);
        self::assertArrayNotHasKey('limit', $query);
    }

    /**
     * @dataProvider invalidLimitDataProvider
     */
    #[DataProvider('invalidLimitDataProvider')]
    public function test_get_all_rejects_invalid_limit(int $limit): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Limit is supposed to be between 10 and 100.');

        $this->whatsappMessage->getAll(null, $limit);
    }

    public function test_find(): void
    {
        $this->addSuccessResponse();

        $response = $this->whatsappMessage->find('whatsapp-message-id');

        $this->assertRequest('GET', '/v1/whatsapp/messages/whatsapp-message-id');
        self::assertEquals(200, $response['status_code']);
    }

    public function test_find_requires_whatsapp_message_id(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('WhatsApp message id is required.');

        $this->whatsappMessage->find('');
    }

    public static function invalidLimitDataProvider(): array
    {
        return [
            'limit below minimum' => [9],
            'limit above maximum' => [101],
        ];
    }
}
