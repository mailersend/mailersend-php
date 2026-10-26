<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\WhatsappMessage;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Builder\WhatsappMessageParams;
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

        $response = $this->whatsappMessage->getAll(new WhatsappMessageParams());

        $this->assertRequest('GET', '/v1/whatsapp/messages');
        self::assertEquals(200, $response['status_code']);
    }

    public function test_get_all_sends_all_params(): void
    {
        $this->addSuccessResponse();

        $params = (new WhatsappMessageParams())
            ->setWhatsappAccountId('whatsapp_account_id')
            ->setFrom('15550001234')
            ->setTemplateId('template_id')
            ->setDateFrom(1791936000)
            ->setDateTo(1792022400)
            ->setPage(3)
            ->setLimit(50);

        $this->whatsappMessage->getAll($params);

        parse_str($this->client->getLastRequest()->getUri()->getQuery(), $query);

        $this->assertQueryParams([
            'whatsapp_account_id' => 'whatsapp_account_id',
            'from' => '15550001234',
            'template_id' => 'template_id',
            'date_from' => '1791936000',
            'date_to' => '1792022400',
            'page' => '3',
            'limit' => '50',
        ], $query);
    }

    public function test_get_all_sends_datetime_string_dates(): void
    {
        $this->addSuccessResponse();

        $params = (new WhatsappMessageParams())
            ->setDateFrom('2026-10-14T00:00:00Z')
            ->setDateTo('2026-10-15 00:00:00');

        $this->whatsappMessage->getAll($params);

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

        $this->whatsappMessage->getAll((new WhatsappMessageParams())->setLimit($boundary)->setPage($boundary));

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

        $this->whatsappMessage->getAll(new WhatsappMessageParams());

        parse_str($this->client->getLastRequest()->getUri()->getQuery(), $query);

        self::assertSame([], $query);
    }

    /**
     * @dataProvider invalidParamsDataProvider
     */
    #[DataProvider('invalidParamsDataProvider')]
    public function test_get_all_rejects_invalid_params(WhatsappMessageParams $params, string $expectedMessage): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($expectedMessage);

        $this->whatsappMessage->getAll($params);
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

    public static function invalidParamsDataProvider(): array
    {
        return [
            'empty whatsapp account id' => [
                (new WhatsappMessageParams())->setWhatsappAccountId(''),
                'WhatsApp account id cannot be empty.',
            ],
            'limit below minimum' => [
                (new WhatsappMessageParams())->setLimit(0),
                'Limit is supposed to be between 1 and 100.',
            ],
            'limit above maximum' => [
                (new WhatsappMessageParams())->setLimit(101),
                'Limit is supposed to be between 1 and 100.',
            ],
            'page below minimum' => [
                (new WhatsappMessageParams())->setPage(0),
                'Page is supposed to be between 1 and 100.',
            ],
            'page above maximum' => [
                (new WhatsappMessageParams())->setPage(101),
                'Page is supposed to be between 1 and 100.',
            ],
            'date_to not after date_from' => [
                (new WhatsappMessageParams())->setDateFrom(1792022400)->setDateTo(1792022400),
                'Date to must be greater than date from.',
            ],
        ];
    }
}
