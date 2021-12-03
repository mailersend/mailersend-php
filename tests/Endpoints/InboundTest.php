<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use MailerSend\Common\Constants;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\Inbound;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Builder\CatchFilter;
use MailerSend\Helpers\Builder\Filter;
use MailerSend\Helpers\Builder\Forward;
use MailerSend\Helpers\Builder\Inbound as InboundBuilder;
use MailerSend\Helpers\Builder\MatchFilter;
use MailerSend\Tests\TestCase;
use Psr\Http\Message\ResponseInterface;
use Tightenco\Collect\Support\Arr;

class InboundTest extends TestCase
{
    protected Inbound $inboundRouting;
    protected ResponseInterface $defaultResponse;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = new Client();

        $this->inboundRouting = new Inbound(new HttpLayer(self::OPTIONS, $this->client), self::OPTIONS);

        $this->defaultResponse = $this->createMock(ResponseInterface::class);
        $this->defaultResponse->method('getStatusCode')->willReturn(200);
    }

    /**
     * @dataProvider validInboundRoutingListDataProvider
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_get_all(array $params, array $expected): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $this->client->addResponse($response);

        $response = $this->inboundRouting->getAll(
            Arr::get($params, 'domain_id'),
            Arr::get($params, 'page'),
            Arr::get($params, 'limit'),
        );

        $request = $this->client->getLastRequest();

        parse_str($request->getUri()->getQuery(), $query);

        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/v1/inbound', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);

        self::assertEquals(Arr::get($expected, 'domain_id'), Arr::get($query, 'domain_id'));
        self::assertEquals(Arr::get($expected, 'page'), Arr::get($query, 'page'));
        self::assertEquals(Arr::get($expected, 'limit'), Arr::get($query, 'limit'));
    }

    /**
     * @dataProvider invalidInboundRoutingListDataProvider
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_get_all_with_errors(array $params): void
    {
        $this->expectException(MailerSendAssertException::class);

        $this->inboundRouting->getAll(
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
    public function test_find_requires_inbound_id(): void
    {
        $this->expectException(MailerSendAssertException::class);

        $this->inboundRouting->find('');
    }

    /**
     * @dataProvider validInboundRoutingCreateDataProvider
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_create(InboundBuilder $params, array $expected): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $this->client->addResponse($response);

        $response = $this->inboundRouting->create($params);

        $request = $this->client->getLastRequest();
        $request_body = json_decode((string)$request->getBody(), true, 512, JSON_THROW_ON_ERROR);

        self::assertEquals('POST', $request->getMethod());
        self::assertEquals('/v1/inbound', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);
        self::assertSame(Arr::get($expected, 'domain_id'), Arr::get($request_body, 'domain_id'));
        self::assertSame(Arr::get($expected, 'name'), Arr::get($request_body, 'name'));
        self::assertSame(Arr::get($expected, 'domain_enabled'), Arr::get($request_body, 'domain_enabled'));
        self::assertSame(Arr::get($expected, 'inbound_domain'), Arr::get($request_body, 'inbound_domain'));
        self::assertSame(Arr::get($expected, 'catch_filter'), Arr::get($request_body, 'catch_filter'));
        self::assertSame(Arr::get($expected, 'match_filter'), Arr::get($request_body, 'match_filter'));
        self::assertSame(Arr::get($expected, 'forwards'), Arr::get($request_body, 'forwards'));
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function test_update(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $this->client->addResponse($response);

        $params = (new InboundBuilder('domainId', 'name', false));

        $response = $this->inboundRouting->update(
            'inboundId',
            $params,
        );

        $request = $this->client->getLastRequest();
        $request_body = json_decode((string)$request->getBody(), true, 512, JSON_THROW_ON_ERROR);

        self::assertEquals('PUT', $request->getMethod());
        self::assertEquals('/v1/inbound/inboundId', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);
        self::assertSame('domainId', Arr::get($request_body, 'domain_id'));
        self::assertSame('name', Arr::get($request_body, 'name'));
        self::assertFalse(Arr::get($request_body, 'domain_enabled'));
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws MailerSendAssertException
     * @throws \JsonException
     */
    public function test_delete(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $this->client->addResponse($response);

        $response = $this->inboundRouting->delete('inboundId');

        $request = $this->client->getLastRequest();

        self::assertEquals('DELETE', $request->getMethod());
        self::assertEquals('/v1/inbound/inboundId', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function test_delete_required_inboundId(): void
    {
        $this->expectException(MailerSendAssertException::class);

        $this->inboundRouting->delete('');
    }

    public function validInboundRoutingListDataProvider(): array
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

    public function invalidInboundRoutingListDataProvider(): array
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

    public function validInboundRoutingCreateDataProvider(): array
    {
        return [
            'enabled, catch all, match all' => [
                'params' => (new InboundBuilder('domainId', 'name', true))
                    ->setInboundDomain('inboundDomain')
                    ->setCatchFilter(
                        (new CatchFilter(Constants::TYPE_CATCH_ALL))
                    )
                    ->setMatchFilter(
                        (new MatchFilter(Constants::TYPE_MATCH_ALL))
                    )
                    ->addForward(new Forward(Constants::TYPE_WEBHOOK, 'value')),
                'expected' => [
                    'domain_id' => 'domainId',
                    'name' => 'name',
                    'domain_enabled' => true,
                    'inbound_domain' => 'inboundDomain',
                    'catch_filter' => [
                        'type' => Constants::TYPE_CATCH_ALL,
                    ],
                    'match_filter' => [
                        'type' => Constants::TYPE_MATCH_ALL,
                    ],
                    'forwards' => [
                        [
                            'type' => Constants::TYPE_WEBHOOK,
                            'value' => 'value',
                        ],
                    ],
                ],
            ],
            'disabled, catch all, match all' => [
                'params' => (new InboundBuilder('domainId', 'name', false))
                    ->setInboundDomain('inboundDomain')
                    ->setCatchFilter(
                        (new CatchFilter(Constants::TYPE_CATCH_ALL))
                    )
                    ->setMatchFilter(
                        (new MatchFilter(Constants::TYPE_MATCH_ALL))
                    )
                    ->addForward(new Forward(Constants::TYPE_WEBHOOK, 'value')),
                'expected' => [
                    'domain_id' => 'domainId',
                    'name' => 'name',
                    'domain_enabled' => false,
                    'inbound_domain' => 'inboundDomain',
                    'catch_filter' => [
                        'type' => Constants::TYPE_CATCH_ALL,
                    ],
                    'match_filter' => [
                        'type' => Constants::TYPE_MATCH_ALL,
                    ],
                    'forwards' => [
                        [
                            'type' => Constants::TYPE_WEBHOOK,
                            'value' => 'value',
                        ],
                    ],
                ],
            ],
            'enabled, catch recipient, match all' => [
                'params' => (new InboundBuilder('domainId', 'name', true))
                    ->setInboundDomain('inboundDomain')
                    ->setCatchFilter(
                        (new CatchFilter(Constants::TYPE_CATCH_RECIPIENT))
                            ->addFilter(new Filter(Constants::COMPARER_EQUAL, 'value'))
                    )
                    ->setMatchFilter(
                        (new MatchFilter(Constants::TYPE_MATCH_ALL))
                    )
                    ->addForward(new Forward(Constants::TYPE_WEBHOOK, 'value')),
                'expected' => [
                    'domain_id' => 'domainId',
                    'name' => 'name',
                    'domain_enabled' => true,
                    'inbound_domain' => 'inboundDomain',
                    'catch_filter' => [
                        'type' => Constants::TYPE_CATCH_RECIPIENT,
                        'filters' => [
                            [
                                'comparer' => Constants::COMPARER_EQUAL,
                                'value' => 'value',
                            ]
                        ]
                    ],
                    'match_filter' => [
                        'type' => Constants::TYPE_MATCH_ALL,
                    ],
                    'forwards' => [
                        [
                            'type' => Constants::TYPE_WEBHOOK,
                            'value' => 'value',
                        ],
                    ],
                ],
            ],
            'enabled, catch all, match domain' => [
                'params' => (new InboundBuilder('domainId', 'name', true))
                    ->setInboundDomain('inboundDomain')
                    ->setCatchFilter(
                        (new CatchFilter(Constants::TYPE_CATCH_ALL))
                    )
                    ->setMatchFilter(
                        (new MatchFilter(Constants::TYPE_MATCH_DOMAIN))
                            ->addFilter(new Filter(Constants::COMPARER_EQUAL, 'value', 'key'))
                    )
                    ->addForward(new Forward(Constants::TYPE_WEBHOOK, 'value')),
                'expected' => [
                    'domain_id' => 'domainId',
                    'name' => 'name',
                    'domain_enabled' => true,
                    'inbound_domain' => 'inboundDomain',
                    'catch_filter' => [
                        'type' => Constants::TYPE_CATCH_ALL,
                    ],
                    'match_filter' => [
                        'type' => Constants::TYPE_MATCH_DOMAIN,
                        'filters' => [
                            [
                                'comparer' => Constants::COMPARER_EQUAL,
                                'value' => 'value',
                                'key' => 'key',
                            ],
                        ],
                    ],
                    'forwards' => [
                        [
                            'type' => Constants::TYPE_WEBHOOK,
                            'value' => 'value',
                        ],
                    ],
                ],
            ],
            'enabled, catch all, match header' => [
                'params' => (new InboundBuilder('domainId', 'name', true))
                    ->setInboundDomain('inboundDomain')
                    ->setCatchFilter(
                        (new CatchFilter(Constants::TYPE_CATCH_ALL))
                    )
                    ->setMatchFilter(
                        (new MatchFilter(Constants::TYPE_MATCH_HEADER))
                            ->addFilter(new Filter(Constants::COMPARER_EQUAL, 'value', 'key'))
                    )
                    ->addForward(new Forward(Constants::TYPE_WEBHOOK, 'value')),
                'expected' => [
                    'domain_id' => 'domainId',
                    'name' => 'name',
                    'domain_enabled' => true,
                    'inbound_domain' => 'inboundDomain',
                    'catch_filter' => [
                        'type' => Constants::TYPE_CATCH_ALL,
                    ],
                    'match_filter' => [
                        'type' => Constants::TYPE_MATCH_HEADER,
                        'filters' => [
                            [
                                'comparer' => Constants::COMPARER_EQUAL,
                                'value' => 'value',
                                'key' => 'key',
                            ],
                        ],
                    ],
                    'forwards' => [
                        [
                            'type' => Constants::TYPE_WEBHOOK,
                            'value' => 'value',
                        ],
                    ],
                ],
            ],
            'enabled, catch all, match sender' => [
                'params' => (new InboundBuilder('domainId', 'name', true))
                    ->setInboundDomain('inboundDomain')
                    ->setCatchFilter(
                        (new CatchFilter(Constants::TYPE_CATCH_ALL))
                    )
                    ->setMatchFilter(
                        (new MatchFilter(Constants::TYPE_MATCH_SENDER))
                            ->addFilter(new Filter(Constants::COMPARER_EQUAL, 'value', 'key'))
                    )
                    ->addForward(new Forward(Constants::TYPE_WEBHOOK, 'value')),
                'expected' => [
                    'domain_id' => 'domainId',
                    'name' => 'name',
                    'domain_enabled' => true,
                    'inbound_domain' => 'inboundDomain',
                    'catch_filter' => [
                        'type' => Constants::TYPE_CATCH_ALL,
                    ],
                    'match_filter' => [
                        'type' => Constants::TYPE_MATCH_SENDER,
                        'filters' => [
                            [
                                'comparer' => Constants::COMPARER_EQUAL,
                                'value' => 'value',
                                'key' => 'key',
                            ],
                        ],
                    ],
                    'forwards' => [
                        [
                            'type' => Constants::TYPE_WEBHOOK,
                            'value' => 'value',
                        ],
                    ],
                ],
            ],
            'multiple filters, multiple forwards' => [
                'params' => (new InboundBuilder('domainId', 'name', true))
                    ->setInboundDomain('inboundDomain')
                    ->setCatchFilter(
                        (new CatchFilter(Constants::TYPE_CATCH_RECIPIENT))
                            ->addFilter(new Filter(Constants::COMPARER_EQUAL, 'value'))
                            ->addFilter(new Filter(Constants::COMPARER_CONTAINS, 'value'))
                    )
                    ->setMatchFilter(
                        (new MatchFilter(Constants::TYPE_MATCH_DOMAIN))
                            ->addFilter(new Filter(Constants::COMPARER_EQUAL, 'value', 'key'))
                            ->addFilter(new Filter(Constants::COMPARER_NOT_ENDS_WITH, 'value', 'key'))
                    )
                    ->addForward(new Forward(Constants::TYPE_WEBHOOK, 'value'))
                    ->addForward(new Forward(Constants::TYPE_WEBHOOK, 'value')),
                'expected' => [
                    'domain_id' => 'domainId',
                    'name' => 'name',
                    'domain_enabled' => true,
                    'inbound_domain' => 'inboundDomain',
                    'catch_filter' => [
                        'type' => Constants::TYPE_CATCH_RECIPIENT,
                        'filters' => [
                            [
                                'comparer' => Constants::COMPARER_EQUAL,
                                'value' => 'value',
                            ],
                            [
                                'comparer' => Constants::COMPARER_CONTAINS,
                                'value' => 'value',
                            ],
                        ],
                    ],
                    'match_filter' => [
                        'type' => Constants::TYPE_MATCH_DOMAIN,
                        'filters' => [
                            [
                                'comparer' => Constants::COMPARER_EQUAL,
                                'value' => 'value',
                                'key' => 'key',
                            ],
                            [
                                'comparer' => Constants::COMPARER_NOT_ENDS_WITH,
                                'value' => 'value',
                                'key' => 'key',
                            ],
                        ],
                    ],
                    'forwards' => [
                        [
                            'type' => Constants::TYPE_WEBHOOK,
                            'value' => 'value',
                        ],
                        [
                            'type' => Constants::TYPE_WEBHOOK,
                            'value' => 'value',
                        ],
                    ],
                ],
            ],
        ];
    }
}
