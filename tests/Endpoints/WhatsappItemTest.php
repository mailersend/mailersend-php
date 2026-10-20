<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\WhatsappItem;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Builder\WhatsappItemParams;
use MailerSend\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class WhatsappItemTest extends TestCase
{
    protected WhatsappItem $whatsappItem;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = new Client();

        $this->whatsappItem = new WhatsappItem(new HttpLayer(self::OPTIONS, $this->client), self::OPTIONS);
    }

    public function test_get_all_uses_correct_method_and_path(): void
    {
        $this->addSuccessResponse();

        $response = $this->whatsappItem->getAll(new WhatsappItemParams());

        $this->assertRequest('GET', '/v1/whatsapp/items');
        self::assertEquals(200, $response['status_code']);
    }

    public function test_get_all_sends_all_params(): void
    {
        $this->addSuccessResponse();

        $params = (new WhatsappItemParams())
            ->setRecipient('48600000002')
            ->setWhatsappMessageId('whatsapp_message_id')
            ->setStatus(['delivered', 'read'])
            ->setWhatsappAccountId('whatsapp_account_id')
            ->setFrom('15550001234')
            ->setTemplateId('template_id')
            ->setDateFrom(1791936000)
            ->setDateTo(1792022400)
            ->setPage(2)
            ->setLimit(25);

        $this->whatsappItem->getAll($params);

        parse_str($this->client->getLastRequest()->getUri()->getQuery(), $query);

        $this->assertQueryParams([
            'recipient' => '48600000002',
            'whatsapp_message_id' => 'whatsapp_message_id',
            'status' => ['delivered', 'read'],
            'whatsapp_account_id' => 'whatsapp_account_id',
            'from' => '15550001234',
            'template_id' => 'template_id',
            'date_from' => '1791936000',
            'date_to' => '1792022400',
            'page' => '2',
            'limit' => '25',
        ], $query);
    }

    public function test_get_all_sends_datetime_string_dates(): void
    {
        $this->addSuccessResponse();

        $params = (new WhatsappItemParams())
            ->setDateFrom('2026-10-14T00:00:00Z')
            ->setDateTo('2026-10-15 00:00:00');

        $this->whatsappItem->getAll($params);

        parse_str($this->client->getLastRequest()->getUri()->getQuery(), $query);

        $this->assertQueryParams([
            'date_from' => '2026-10-14T00:00:00Z',
            'date_to' => '2026-10-15 00:00:00',
        ], $query);
    }

    public function test_get_all_accepts_limit_and_page_boundaries(): void
    {
        $this->addSuccessResponse();
        $this->addSuccessResponse();

        $this->whatsappItem->getAll((new WhatsappItemParams())->setLimit(1)->setPage(1));
        $this->whatsappItem->getAll((new WhatsappItemParams())->setLimit(100)->setPage(100));

        parse_str($this->client->getLastRequest()->getUri()->getQuery(), $query);

        $this->assertQueryParams(['limit' => '100', 'page' => '100'], $query);
    }

    public function test_get_all_excludes_optional_params_when_not_set(): void
    {
        $this->addSuccessResponse();

        $this->whatsappItem->getAll(new WhatsappItemParams());

        parse_str($this->client->getLastRequest()->getUri()->getQuery(), $query);

        self::assertSame([], $query);
    }

    /**
     * @dataProvider invalidParamsDataProvider
     */
    #[DataProvider('invalidParamsDataProvider')]
    public function test_get_all_rejects_invalid_params(WhatsappItemParams $params, string $expectedMessage): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($expectedMessage);

        $this->whatsappItem->getAll($params);
    }

    public function test_find(): void
    {
        $this->addSuccessResponse();

        $response = $this->whatsappItem->find('whatsapp-item-id');

        $this->assertRequest('GET', '/v1/whatsapp/items/whatsapp-item-id');
        self::assertEquals(200, $response['status_code']);
    }

    public function test_find_requires_whatsapp_item_id(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('WhatsApp item id is required.');

        $this->whatsappItem->find('');
    }

    public static function invalidParamsDataProvider(): array
    {
        return [
            'empty whatsapp account id' => [
                (new WhatsappItemParams())->setWhatsappAccountId(''),
                'WhatsApp account id cannot be empty.',
            ],
            'limit below minimum' => [
                (new WhatsappItemParams())->setLimit(0),
                'Limit is supposed to be between 1 and 100.',
            ],
            'limit above maximum' => [
                (new WhatsappItemParams())->setLimit(101),
                'Limit is supposed to be between 1 and 100.',
            ],
            'page below minimum' => [
                (new WhatsappItemParams())->setPage(0),
                'Page is supposed to be between 1 and 100.',
            ],
            'page above maximum' => [
                (new WhatsappItemParams())->setPage(101),
                'Page is supposed to be between 1 and 100.',
            ],
            'date_to not after date_from' => [
                (new WhatsappItemParams())->setDateFrom(1792022400)->setDateTo(1791936000),
                'Date to must be greater than date from.',
            ],
            'invalid status' => [
                (new WhatsappItemParams())->setStatus(['sent', 'bogus']),
                'The following statuses are invalid: bogus',
            ],
        ];
    }
}
