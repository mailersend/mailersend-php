<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\WhatsappTemplate;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Builder\WhatsappTemplateParams;
use MailerSend\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class WhatsappTemplateTest extends TestCase
{
    protected WhatsappTemplate $whatsappTemplate;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = new Client();

        $this->whatsappTemplate = new WhatsappTemplate(new HttpLayer(self::OPTIONS, $this->client), self::OPTIONS);
    }

    public function test_get_all_uses_correct_method_and_path(): void
    {
        $this->addSuccessResponse();

        $response = $this->whatsappTemplate->getAll(new WhatsappTemplateParams());

        $this->assertRequest('GET', '/v1/whatsapp/templates');
        self::assertEquals(200, $response['status_code']);
    }

    public function test_get_all_sends_all_params(): void
    {
        $this->addSuccessResponse();

        $params = (new WhatsappTemplateParams())
            ->setWhatsappAccountId('whatsapp_account_id')
            ->setStatus('approved')
            ->setCategory('UTILITY')
            ->setLanguage('en_US')
            ->setPage(2)
            ->setLimit(25);

        $this->whatsappTemplate->getAll($params);

        parse_str($this->client->getLastRequest()->getUri()->getQuery(), $query);

        $this->assertQueryParams([
            'whatsapp_account_id' => 'whatsapp_account_id',
            'status' => 'approved',
            'category' => 'UTILITY',
            'language' => 'en_US',
            'page' => '2',
            'limit' => '25',
        ], $query);
    }

    /**
     * @dataProvider limitAndPageBoundariesDataProvider
     */
    #[DataProvider('limitAndPageBoundariesDataProvider')]
    public function test_get_all_accepts_limit_and_page_boundaries(int $boundary): void
    {
        $this->addSuccessResponse();

        $this->whatsappTemplate->getAll((new WhatsappTemplateParams())->setLimit($boundary)->setPage($boundary));

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

        $this->whatsappTemplate->getAll(new WhatsappTemplateParams());

        parse_str($this->client->getLastRequest()->getUri()->getQuery(), $query);

        self::assertSame([], $query);
    }

    /**
     * @dataProvider invalidParamsDataProvider
     */
    #[DataProvider('invalidParamsDataProvider')]
    public function test_get_all_rejects_invalid_params(WhatsappTemplateParams $params, string $expectedMessage): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($expectedMessage);

        $this->whatsappTemplate->getAll($params);
    }

    public function test_find(): void
    {
        $this->addSuccessResponse();

        $response = $this->whatsappTemplate->find('whatsapp-template-id');

        $this->assertRequest('GET', '/v1/whatsapp/templates/whatsapp-template-id');
        self::assertEquals(200, $response['status_code']);
    }

    public function test_find_requires_whatsapp_template_id(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('WhatsApp template id is required.');

        $this->whatsappTemplate->find('');
    }

    public static function invalidParamsDataProvider(): array
    {
        return [
            'empty whatsapp account id' => [
                (new WhatsappTemplateParams())->setWhatsappAccountId(''),
                'WhatsApp account id cannot be empty.',
            ],
            'limit below minimum' => [
                (new WhatsappTemplateParams())->setLimit(0),
                'Limit is supposed to be between 1 and 100.',
            ],
            'limit above maximum' => [
                (new WhatsappTemplateParams())->setLimit(101),
                'Limit is supposed to be between 1 and 100.',
            ],
            'page below minimum' => [
                (new WhatsappTemplateParams())->setPage(0),
                'Page is supposed to be between 1 and 100.',
            ],
            'page above maximum' => [
                (new WhatsappTemplateParams())->setPage(101),
                'Page is supposed to be between 1 and 100.',
            ],
            'invalid status' => [
                (new WhatsappTemplateParams())->setStatus('active'),
                'Value "active" is not an element of the valid values: draft, pending, approved',
            ],
            'invalid category' => [
                (new WhatsappTemplateParams())->setCategory('utility'),
                'Value "utility" is not an element of the valid values: UTILITY, AUTHENTICATION, MARKETING',
            ],
        ];
    }
}
