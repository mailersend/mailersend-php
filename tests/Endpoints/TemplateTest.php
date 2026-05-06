<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use MailerSend\Helpers\Arr;
use MailerSend\Common\Constants;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\Template;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Builder\TemplateParams;
use MailerSend\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Http\Message\ResponseInterface;

class TemplateTest extends TestCase
{
    protected Template $template;
    protected ResponseInterface $defaultResponse;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = new Client();

        $this->template = new Template(new HttpLayer(self::OPTIONS, $this->client), self::OPTIONS);

        $this->defaultResponse = $this->createStub(ResponseInterface::class);
        $this->defaultResponse->method('getStatusCode')->willReturn(200);
    }

    /**
     * @dataProvider validTemplateListDataProvider
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     */
    #[DataProvider('validTemplateListDataProvider')]
    public function test_get_all(array $params, array $expected): void
    {
        $this->addSuccessResponse();

        $response = $this->template->getAll(
            Arr::get($params, 'domain_id'),
            Arr::get($params, 'page'),
            Arr::get($params, 'limit'),
        );

        $request = $this->client->getLastRequest();

        parse_str($request->getUri()->getQuery(), $query);

        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/v1/templates', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);

        self::assertEquals(Arr::get($expected, 'domain_id'), Arr::get($query, 'domain_id'));
        self::assertEquals(Arr::get($expected, 'page'), Arr::get($query, 'page'));
        self::assertEquals(Arr::get($expected, 'limit'), Arr::get($query, 'limit'));
    }

    /**
     * @dataProvider invalidTemplateListDataProvider
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    #[DataProvider('invalidTemplateListDataProvider')]
    public function test_get_all_rejects_invalid_params(array $params, string $message): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($message);

        $this->template->getAll(
            Arr::get($params, 'domain_id'),
            Arr::get($params, 'page'),
            Arr::get($params, 'limit'),
        );
    }

    /**
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_find(): void
    {
        $this->addSuccessResponse();

        $response = $this->template->find('template-id');

        $this->assertRequest('GET', '/v1/templates/template-id');

        self::assertEquals(200, $response['status_code']);
    }

    /**
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_find_requires_template_id(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Template id is required.');

        $this->template->find('');
    }

    /**
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_create(): void
    {
        $this->addSuccessResponse();

        $params = (new TemplateParams())
            ->setName('My Template')
            ->setHtml('<h1>Hello</h1>')
            ->setText('Hello')
            ->setDomainId('domain-id')
            ->setCategories(['newsletter'])
            ->setTags(['tag1'])
            ->setAutoGenerate(true);

        $response = $this->template->create($params);

        $body = $this->assertRequest('POST', '/v1/templates');

        self::assertEquals(200, $response['status_code']);
        self::assertSame('My Template', Arr::get($body, 'name'));
        self::assertSame('<h1>Hello</h1>', Arr::get($body, 'html'));
        self::assertSame('Hello', Arr::get($body, 'text'));
        self::assertSame('domain-id', Arr::get($body, 'domain_id'));
        self::assertSame(['newsletter'], Arr::get($body, 'categories'));
        self::assertSame(['tag1'], Arr::get($body, 'tags'));
        self::assertTrue(Arr::get($body, 'auto_generate'));
    }

    /**
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_create_requires_html(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('HTML is required.');

        $this->template->create(new TemplateParams());
    }

    /**
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_create_name_max_length_is_validated(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Name must be 50 characters or fewer.');

        $params = (new TemplateParams())
            ->setHtml('<h1>Hello</h1>')
            ->setName(str_repeat('a', 51));

        $this->template->create($params);
    }

    /**
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_update(): void
    {
        $this->addSuccessResponse();

        $params = (new TemplateParams())
            ->setName('Updated Template')
            ->setHtml('<p>Updated</p>')
            ->setText('Updated')
            ->setDomainId('domain-id')
            ->setCategories(['promo'])
            ->setTags(['tag2'])
            ->setAutoGenerate(false);

        $response = $this->template->update('template-id', $params);

        $body = $this->assertRequest('PUT', '/v1/templates/template-id');

        self::assertEquals(200, $response['status_code']);
        self::assertSame('Updated Template', Arr::get($body, 'name'));
        self::assertSame('<p>Updated</p>', Arr::get($body, 'html'));
        self::assertSame('Updated', Arr::get($body, 'text'));
        self::assertSame('domain-id', Arr::get($body, 'domain_id'));
        self::assertSame(['promo'], Arr::get($body, 'categories'));
        self::assertSame(['tag2'], Arr::get($body, 'tags'));
        self::assertFalse(Arr::get($body, 'auto_generate'));
    }

    /**
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_update_requires_template_id(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Template id is required.');

        $this->template->update('', new TemplateParams());
    }

    /**
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_update_name_max_length_is_validated(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Name must be 50 characters or fewer.');

        $params = (new TemplateParams())
            ->setName(str_repeat('a', 51));

        $this->template->update('template-id', $params);
    }

    /**
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_delete(): void
    {
        $this->addSuccessResponse();

        $response = $this->template->delete('template-id');

        $this->assertRequest('DELETE', '/v1/templates/template-id');

        self::assertEquals(200, $response['status_code']);
    }

    /**
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_delete_requires_template_id(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Template id is required.');

        $this->template->delete('');
    }

    public static function validTemplateListDataProvider(): array
    {
        return [
            'empty request' => [
                'params' => [],
                'expected' => [
                    'domain_id' => null,
                    'page' => null,
                    'limit' => null,
                ],
            ],
            'with domain id' => [
                'params' => [
                    'domain_id' => 'domain_id',
                ],
                'expected' => [
                    'domain_id' => 'domain_id',
                    'page' => null,
                    'limit' => null,
                ],
            ],
            'with page' => [
                'params' => [
                    'page' => 1,
                ],
                'expected' => [
                    'domain_id' => null,
                    'page' => 1,
                    'limit' => null,
                ],
            ],
            'with limit' => [
                'params' => [
                    'limit' => 10,
                ],
                'expected' => [
                    'domain_id' => null,
                    'page' => null,
                    'limit' => 10,
                ],
            ],
            'complete request' => [
                'params' => [
                    'domain_id' => 'domain_id',
                    'page' => 1,
                    'limit' => 10,
                ],
                'expected' => [
                    'domain_id' => 'domain_id',
                    'page' => 1,
                    'limit' => 10,
                ],
            ],
        ];
    }

    public static function invalidTemplateListDataProvider(): array
    {
        return [
            'limit below minimum' => [
                'params' => [
                    'limit' => 9,
                ],
                'message' => 'Limit is supposed to be between ' . Constants::MIN_LIMIT . ' and ' . Constants::MAX_LIMIT . '.',
            ],
            'limit above maximum' => [
                'params' => [
                    'limit' => 101,
                ],
                'message' => 'Limit is supposed to be between ' . Constants::MIN_LIMIT . ' and ' . Constants::MAX_LIMIT . '.',
            ],
        ];
    }
}
