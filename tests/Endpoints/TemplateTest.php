<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use MailerSend\Helpers\Arr;
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
        $response = $this->createStub(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $this->client->addResponse($response);

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
    public function test_get_all_with_errors(array $params): void
    {
        $this->expectException(MailerSendAssertException::class);

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
        $response = $this->createStub(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $this->client->addResponse($response);

        $response = $this->template->find('template-id');

        $request = $this->client->getLastRequest();

        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/v1/templates/template-id', $request->getUri()->getPath());
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

        $this->template->find('');
    }

    /**
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_create(): void
    {
        $response = $this->createStub(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $this->client->addResponse($response);

        $params = (new TemplateParams())
            ->setName('My Template')
            ->setHtml('<h1>Hello</h1>')
            ->setText('Hello')
            ->setDomainId('domain-id')
            ->setCategories(['newsletter'])
            ->setTags(['tag1'])
            ->setAutoGenerate(true);

        $response = $this->template->create($params);

        $request = $this->client->getLastRequest();
        $body = json_decode((string) $request->getBody(), true, 512, JSON_THROW_ON_ERROR);

        self::assertEquals('POST', $request->getMethod());
        self::assertEquals('/v1/templates', $request->getUri()->getPath());
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
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_update(): void
    {
        $response = $this->createStub(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $this->client->addResponse($response);

        $params = (new TemplateParams())
            ->setName('Updated Template')
            ->setHtml('<p>Updated</p>');

        $response = $this->template->update('template-id', $params);

        $request = $this->client->getLastRequest();
        $body = json_decode((string) $request->getBody(), true, 512, JSON_THROW_ON_ERROR);

        self::assertEquals('PUT', $request->getMethod());
        self::assertEquals('/v1/templates/template-id', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);
        self::assertSame('Updated Template', Arr::get($body, 'name'));
        self::assertSame('<p>Updated</p>', Arr::get($body, 'html'));
    }

    /**
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_update_requires_template_id(): void
    {
        $this->expectException(MailerSendAssertException::class);

        $this->template->update('', new TemplateParams());
    }

    /**
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_delete(): void
    {
        $response = $this->createStub(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $this->client->addResponse($response);

        $response = $this->template->delete('template-id');

        $request = $this->client->getLastRequest();

        self::assertEquals('DELETE', $request->getMethod());
        self::assertEquals('/v1/templates/template-id', $request->getUri()->getPath());
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
                [
                    'page' => 1,
                ],
                [
                    'domain_id' => null,
                    'page' => 1,
                    'limit' => null,
                ],
            ],
            'with limit' => [
                [
                    'limit' => 10,
                ],
                [
                    'domain_id' => null,
                    'page' => null,
                    'limit' => 10,
                ],
            ],
            'complete request' => [
                [
                    'domain_id' => 'domain_id',
                    'page' => 1,
                    'limit' => 10,
                ],
                [
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
            'with limit under 10' => [
                [
                    'limit' => 9,
                ],
            ],
            'with limit over 100' => [
                [
                    'limit' => 101,
                ],
            ]
        ];
    }
}
