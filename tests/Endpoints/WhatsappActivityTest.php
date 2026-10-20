<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\WhatsappActivity;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Builder\WhatsappActivityParams;
use MailerSend\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class WhatsappActivityTest extends TestCase
{
    protected WhatsappActivity $whatsappActivity;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = new Client();

        $this->whatsappActivity = new WhatsappActivity(new HttpLayer(self::OPTIONS, $this->client), self::OPTIONS);
    }

    public function test_get_all_uses_correct_method_and_path(): void
    {
        $this->addSuccessResponse();

        $response = $this->whatsappActivity->getAll(new WhatsappActivityParams());

        $this->assertRequest('GET', '/v1/whatsapp/activities');
        self::assertEquals(200, $response['status_code']);
    }

    public function test_get_all_sends_all_params(): void
    {
        $this->addSuccessResponse();

        $params = (new WhatsappActivityParams())
            ->setWhatsappItemId('whatsapp_item_id')
            ->setWhatsappMessageId('whatsapp_message_id')
            ->setRecipient('48600000002')
            ->setType(['delivered', 'failed'])
            ->setDateFrom(1791936000)
            ->setDateTo(1792022400)
            ->setPage(2)
            ->setLimit(25);

        $this->whatsappActivity->getAll($params);

        parse_str($this->client->getLastRequest()->getUri()->getQuery(), $query);

        $this->assertQueryParams([
            'whatsapp_item_id' => 'whatsapp_item_id',
            'whatsapp_message_id' => 'whatsapp_message_id',
            'recipient' => '48600000002',
            'type' => ['delivered', 'failed'],
            'date_from' => '1791936000',
            'date_to' => '1792022400',
            'page' => '2',
            'limit' => '25',
        ], $query);
    }

    public function test_get_all_sends_datetime_string_dates(): void
    {
        $this->addSuccessResponse();

        $params = (new WhatsappActivityParams())
            ->setDateFrom('2026-10-14T00:00:00Z')
            ->setDateTo('2026-10-15 00:00:00');

        $this->whatsappActivity->getAll($params);

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

        $this->whatsappActivity->getAll((new WhatsappActivityParams())->setLimit(1)->setPage(1));
        $this->whatsappActivity->getAll((new WhatsappActivityParams())->setLimit(100)->setPage(100));

        parse_str($this->client->getLastRequest()->getUri()->getQuery(), $query);

        $this->assertQueryParams(['limit' => '100', 'page' => '100'], $query);
    }

    public function test_get_all_excludes_optional_params_when_not_set(): void
    {
        $this->addSuccessResponse();

        $this->whatsappActivity->getAll(new WhatsappActivityParams());

        parse_str($this->client->getLastRequest()->getUri()->getQuery(), $query);

        self::assertSame([], $query);
    }

    /**
     * @dataProvider invalidParamsDataProvider
     */
    #[DataProvider('invalidParamsDataProvider')]
    public function test_get_all_rejects_invalid_params(WhatsappActivityParams $params, string $expectedMessage): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($expectedMessage);

        $this->whatsappActivity->getAll($params);
    }

    public static function invalidParamsDataProvider(): array
    {
        return [
            'limit below minimum' => [
                (new WhatsappActivityParams())->setLimit(0),
                'Limit is supposed to be between 1 and 100.',
            ],
            'limit above maximum' => [
                (new WhatsappActivityParams())->setLimit(101),
                'Limit is supposed to be between 1 and 100.',
            ],
            'page below minimum' => [
                (new WhatsappActivityParams())->setPage(0),
                'Page is supposed to be between 1 and 100.',
            ],
            'page above maximum' => [
                (new WhatsappActivityParams())->setPage(101),
                'Page is supposed to be between 1 and 100.',
            ],
            'date_to not after date_from' => [
                (new WhatsappActivityParams())->setDateFrom(1792022400)->setDateTo(1792022400),
                'Date to must be greater than date from.',
            ],
            'invalid type' => [
                (new WhatsappActivityParams())->setType(['read', 'opened']),
                'The following types are invalid: opened',
            ],
        ];
    }
}
