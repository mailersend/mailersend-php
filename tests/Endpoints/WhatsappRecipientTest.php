<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\WhatsappRecipient;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Builder\WhatsappRecipientParams;
use MailerSend\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class WhatsappRecipientTest extends TestCase
{
    protected WhatsappRecipient $whatsappRecipient;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = new Client();

        $this->whatsappRecipient = new WhatsappRecipient(new HttpLayer(self::OPTIONS, $this->client), self::OPTIONS);
    }

    public function test_get_all_uses_correct_method_and_path(): void
    {
        $this->addSuccessResponse();

        $response = $this->whatsappRecipient->getAll(new WhatsappRecipientParams());

        $this->assertRequest('GET', '/v1/whatsapp/recipients');
        self::assertEquals(200, $response['status_code']);
    }

    public function test_get_all_sends_all_params(): void
    {
        $this->addSuccessResponse();

        $params = (new WhatsappRecipientParams())
            ->setStatus('active')
            ->setPage(2)
            ->setLimit(25);

        $this->whatsappRecipient->getAll($params);

        parse_str($this->client->getLastRequest()->getUri()->getQuery(), $query);

        $this->assertQueryParams(['status' => 'active', 'page' => '2', 'limit' => '25'], $query);
    }

    public function test_get_all_excludes_optional_params_when_not_set(): void
    {
        $this->addSuccessResponse();

        $this->whatsappRecipient->getAll(new WhatsappRecipientParams());

        parse_str($this->client->getLastRequest()->getUri()->getQuery(), $query);

        self::assertSame([], $query);
    }

    /**
     * @dataProvider invalidParamsDataProvider
     */
    #[DataProvider('invalidParamsDataProvider')]
    public function test_get_all_rejects_invalid_params(WhatsappRecipientParams $params, string $expectedMessage): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($expectedMessage);

        $this->whatsappRecipient->getAll($params);
    }

    public function test_find(): void
    {
        $this->addSuccessResponse();

        $response = $this->whatsappRecipient->find('recipient-id');

        $this->assertRequest('GET', '/v1/whatsapp/recipients/recipient-id');
        self::assertEquals(200, $response['status_code']);
    }

    public function test_find_requires_whatsapp_recipient_id(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('WhatsApp recipient id is required.');

        $this->whatsappRecipient->find('');
    }

    public static function invalidParamsDataProvider(): array
    {
        return [
            'limit below minimum' => [
                (new WhatsappRecipientParams())->setLimit(9),
                'Limit is supposed to be between 10 and 100.',
            ],
            'limit above maximum' => [
                (new WhatsappRecipientParams())->setLimit(101),
                'Limit is supposed to be between 10 and 100.',
            ],
            'invalid status' => [
                (new WhatsappRecipientParams())->setStatus('opt_out'),
                'Value "opt_out" is not an element of the valid values: active, invalid, suppressed, blocked',
            ],
        ];
    }
}
