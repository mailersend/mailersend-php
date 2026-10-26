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
            ->setFrom('48600000001')
            ->setType(['text', 'image'])
            ->setDateFrom(1790000000)
            ->setDateTo(1790086400)
            ->setPage(2)
            ->setLimit(25);

        $this->whatsappInboundMessage->getAll($params);

        parse_str($this->client->getLastRequest()->getUri()->getQuery(), $query);

        $this->assertQueryParams([
            'whatsapp_account_id' => 'whatsapp_account_id',
            'from' => '48600000001',
            'type' => ['text', 'image'],
            'date_from' => '1790000000',
            'date_to' => '1790086400',
            'page' => '2',
            'limit' => '25',
        ], $query);
    }

    public function test_get_all_sends_datetime_string_dates(): void
    {
        $this->addSuccessResponse();

        $params = (new WhatsappInboundMessageParams())
            ->setDateFrom('2026-10-14T00:00:00Z')
            ->setDateTo('2026-10-15 00:00:00');

        $this->whatsappInboundMessage->getAll($params);

        parse_str($this->client->getLastRequest()->getUri()->getQuery(), $query);

        $this->assertQueryParams([
            'date_from' => '2026-10-14T00:00:00Z',
            'date_to' => '2026-10-15 00:00:00',
        ], $query);
    }

    /**
     * @dataProvider limitAndPageBoundariesDataProvider
     */
    #[DataProvider('limitAndPageBoundariesDataProvider')]
    public function test_get_all_accepts_limit_and_page_boundaries(int $boundary): void
    {
        $this->addSuccessResponse();

        $this->whatsappInboundMessage->getAll((new WhatsappInboundMessageParams())->setLimit($boundary)->setPage($boundary));

        parse_str($this->client->getLastRequest()->getUri()->getQuery(), $query);

        $this->assertQueryParams(['limit' => (string) $boundary, 'page' => (string) $boundary], $query);
    }

    public static function limitAndPageBoundariesDataProvider(): array
    {
        return [
            'lower bound' => [1],
            'upper bound' => [100],
        ];
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
                (new WhatsappInboundMessageParams())->setLimit(0),
                'Limit is supposed to be between 1 and 100.',
            ],
            'limit above maximum' => [
                (new WhatsappInboundMessageParams())->setLimit(101),
                'Limit is supposed to be between 1 and 100.',
            ],
            'page below minimum' => [
                (new WhatsappInboundMessageParams())->setPage(0),
                'Page is supposed to be between 1 and 100.',
            ],
            'page above maximum' => [
                (new WhatsappInboundMessageParams())->setPage(101),
                'Page is supposed to be between 1 and 100.',
            ],
            'date_to not after date_from' => [
                (new WhatsappInboundMessageParams())->setDateFrom(1790086400)->setDateTo(1790086400),
                'Date to must be greater than date from.',
            ],
            'invalid type' => [
                (new WhatsappInboundMessageParams())->setType(['text', 'bogus']),
                'The following types are invalid: bogus',
            ],
        ];
    }
}
