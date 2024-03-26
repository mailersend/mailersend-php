<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use Illuminate\Support\Arr;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\Template;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Tests\TestCase;
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

        $this->defaultResponse = $this->createMock(ResponseInterface::class);
        $this->defaultResponse->method('getStatusCode')->willReturn(200);
    }

    /**
     * @dataProvider validTemplateListDataProvider
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     */
    public function test_get_all(array $params, array $expected): void
    {
        $response = $this->createMock(ResponseInterface::class);
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
    public function test_delete_requires_template_id(): void
    {
        $this->expectException(MailerSendAssertException::class);

        $this->template->delete('');
    }

    public function validTemplateListDataProvider(): array
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

    public function invalidTemplateListDataProvider(): array
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
