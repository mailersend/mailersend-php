<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\WhatsappInboundMessage;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Builder\WhatsappInboundMessageParams;
use MailerSend\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class WhatsappInboundMessageTest extends TestCase
{
    protected WhatsappInboundMessage $whatsappInboundMessage;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = new Client();

        $this->whatsappInboundMessage = new WhatsappInboundMessage(new HttpLayer(self::OPTIONS, $this->client), self::OPTIONS);
    }

    public function test_get_all_uses_correct_method_and_path(): void
    {
        $this->addSuccessResponse();

        $response = $this->whatsappInboundMessage->getAll(new WhatsappInboundMessageParams());

        $this->assertRequest('GET', '/v1/whatsapp/inbound-messages');
        self::assertEquals(200, $response['status_code']);
    }

    public function test_get_all_sends_all_params(): void
    {
        $this->addSuccessResponse();

        $params = (new WhatsappInboundMessageParams())
            ->setWhatsappAccountId('whatsapp_account_id')
            ->setType(['text', 'image'])
            ->setDateFrom(1790000000)
            ->setDateTo(1790086400)
            ->setPage(2)
            ->setLimit(25);

        $this->whatsappInboundMessage->getAll($params);

        parse_str($this->client->getLastRequest()->getUri()->getQuery(), $query);

        $this->assertQueryParams([
            'whatsapp_account_id' => 'whatsapp_account_id',
            'type' => ['text', 'image'],
            'date_from' => '1790000000',
            'date_to' => '1790086400',
            'page' => '2',
            'limit' => '25',
        ], $query);
    }

    public function test_get_all_excludes_optional_params_when_not_set(): void
    {
        $this->addSuccessResponse();

        $this->whatsappInboundMessage->getAll(new WhatsappInboundMessageParams());

        parse_str($this->client->getLastRequest()->getUri()->getQuery(), $query);

        self::assertSame([], $query);
    }

    /**
     * @dataProvider invalidParamsDataProvider
     */
    #[DataProvider('invalidParamsDataProvider')]
    public function test_get_all_rejects_invalid_params(WhatsappInboundMessageParams $params, string $expectedMessage): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($expectedMessage);

        $this->whatsappInboundMessage->getAll($params);
    }

    public function test_find(): void
    {
        $this->addSuccessResponse();

        $response = $this->whatsappInboundMessage->find('inbound-message-id');

        $this->assertRequest('GET', '/v1/whatsapp/inbound-messages/inbound-message-id');
        self::assertEquals(200, $response['status_code']);
    }

    public function test_find_requires_whatsapp_inbound_message_id(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('WhatsApp inbound message id is required.');

        $this->whatsappInboundMessage->find('');
    }

    public static function invalidParamsDataProvider(): array
    {
        return [
            'empty whatsapp account id' => [
                (new WhatsappInboundMessageParams())->setWhatsappAccountId(''),
                'WhatsApp account id cannot be empty.',
            ],
            'limit below minimum' => [
                (new WhatsappInboundMessageParams())->setLimit(9),
                'Limit is supposed to be between 10 and 100.',
            ],
            'limit above maximum' => [
                (new WhatsappInboundMessageParams())->setLimit(101),
                'Limit is supposed to be between 10 and 100.',
            ],
            'date_to not after date_from' => [
                (new WhatsappInboundMessageParams())->setDateFrom(1790086400)->setDateTo(1790086400),
                'Provided "1790086400" is not greater than "1790086400".',
            ],
            'invalid type' => [
                (new WhatsappInboundMessageParams())->setType(['text', 'bogus']),
                'The following types are invalid: bogus',
            ],
        ];
    }
}
