<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use MailerSend\Helpers\Arr;
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
use PHPUnit\Framework\Attributes\DataProvider;

class InboundTest extends TestCase
{
    protected Inbound $inboundRouting;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = new Client();

        $this->inboundRouting = new Inbound(new HttpLayer(self::OPTIONS, $this->client), self::OPTIONS);
    }

    /**
     * @dataProvider validInboundRoutingListDataProvider
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    #[DataProvider('validInboundRoutingListDataProvider')]
    public function test_get_all(array $params, array $expected): void
    {
        $this->addSuccessResponse();

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
    #[DataProvider('invalidInboundRoutingListDataProvider')]
    public function test_get_all_rejects_invalid_params(array $params, string $message): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($message);

        $this->inboundRouting->getAll(
            Arr::get($params, 'domain_id'),
            Arr::get($params, 'page'),
            Arr::get($params, 'limit'),
        );
    }

    /**
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws MailerSendAssertException
     */
    public function test_find(): void
    {
        $this->addSuccessResponse();

        $response = $this->inboundRouting->find('inboundId');

        $body = $this->assertRequest('GET', '/v1/inbound/inboundId');

        self::assertEquals(200, $response['status_code']);
        self::assertEmpty($body);
    }

    /**
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_find_requires_inbound_id(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Inbound id is required.');

        $this->inboundRouting->find('');
    }

    /**
     * @dataProvider validInboundRoutingCreateDataProvider
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    #[DataProvider('validInboundRoutingCreateDataProvider')]
    public function test_create(InboundBuilder $params, array $expected): void
    {
        $this->addSuccessResponse();

        $response = $this->inboundRouting->create($params);

        $body = $this->assertRequest('POST', '/v1/inbound');

        self::assertEquals(200, $response['status_code']);
        self::assertSame(Arr::get($expected, 'domain_id'), Arr::get($body, 'domain_id'));
        self::assertSame(Arr::get($expected, 'name'), Arr::get($body, 'name'));
        self::assertSame(Arr::get($expected, 'domain_enabled'), Arr::get($body, 'domain_enabled'));
        self::assertSame(Arr::get($expected, 'inbound_domain'), Arr::get($body, 'inbound_domain'));
        self::assertSame(Arr::get($expected, 'catch_filter'), Arr::get($body, 'catch_filter'));
        self::assertSame(Arr::get($expected, 'match_filter'), Arr::get($body, 'match_filter'));
        self::assertSame(Arr::get($expected, 'forwards'), Arr::get($body, 'forwards'));
        self::assertSame(Arr::get($expected, 'catch_type'), Arr::get($body, 'catch_type'));
        self::assertSame(Arr::get($expected, 'match_type'), Arr::get($body, 'match_type'));
        self::assertSame(Arr::get($expected, 'inbound_priority'), Arr::get($body, 'inbound_priority'));
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function test_update(): void
    {
        $this->addSuccessResponse();

        $params = (new InboundBuilder('domainId', 'name', false));

        $response = $this->inboundRouting->update('inboundId', $params);

        $body = $this->assertRequest('PUT', '/v1/inbound/inboundId');

        self::assertEquals(200, $response['status_code']);
        self::assertSame('domainId', Arr::get($body, 'domain_id'));
        self::assertSame('name', Arr::get($body, 'name'));
        self::assertFalse(Arr::get($body, 'domain_enabled'));
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function test_update_with_all_params(): void
    {
        $this->addSuccessResponse();

        $params = (new InboundBuilder('domainId', 'name', true))
            ->setInboundDomain('inboundDomain')
            ->setCatchFilter(
                (new CatchFilter(Constants::TYPE_CATCH_RECIPIENT))
                    ->addFilter(new Filter(Constants::COMPARER_EQUAL, 'value'))
            )
            ->setCatchType(Constants::CATCH_TYPE_ONE)
            ->setMatchFilter(
                (new MatchFilter(Constants::TYPE_MATCH_SENDER))
                    ->addFilter(new Filter(Constants::COMPARER_EQUAL, 'value', 'key'))
            )
            ->setMatchType(Constants::MATCH_TYPE_ONE)
            ->setInboundPriority(10)
            ->addForward(new Forward(Constants::TYPE_WEBHOOK, 'https://example.com'));

        $response = $this->inboundRouting->update('inboundId', $params);

        $body = $this->assertRequest('PUT', '/v1/inbound/inboundId');

        self::assertEquals(200, $response['status_code']);
        self::assertSame('domainId', Arr::get($body, 'domain_id'));
        self::assertSame('name', Arr::get($body, 'name'));
        self::assertTrue(Arr::get($body, 'domain_enabled'));
        self::assertSame('inboundDomain', Arr::get($body, 'inbound_domain'));
        self::assertSame(Constants::CATCH_TYPE_ONE, Arr::get($body, 'catch_type'));
        self::assertSame(Constants::MATCH_TYPE_ONE, Arr::get($body, 'match_type'));
        self::assertSame(10, Arr::get($body, 'inbound_priority'));
        self::assertNotNull(Arr::get($body, 'catch_filter'));
        self::assertNotNull(Arr::get($body, 'match_filter'));
        self::assertNotEmpty(Arr::get($body, 'forwards'));
    }

    /**
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_update_requires_inbound_id(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Inbound id is required.');

        $this->inboundRouting->update('', new InboundBuilder('domain-id', 'name', true));
    }

    /**
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws MailerSendAssertException
     */
    public function test_create_sends_all_optional_params(): void
    {
        $this->addSuccessResponse();

        $params = (new InboundBuilder('domainId', 'name', true))
            ->setInboundDomain('inbound.example.com')
            ->setCatchFilter(
                (new CatchFilter(Constants::TYPE_CATCH_RECIPIENT))
                    ->addFilter(new Filter(Constants::COMPARER_EQUAL, 'value'))
            )
            ->setCatchType(Constants::CATCH_TYPE_ONE)
            ->setMatchFilter(
                (new MatchFilter(Constants::TYPE_MATCH_SENDER))
                    ->addFilter(new Filter(Constants::COMPARER_EQUAL, 'value', 'key'))
            )
            ->setMatchType(Constants::MATCH_TYPE_ONE)
            ->setInboundPriority(10)
            ->addForward(new Forward(Constants::TYPE_WEBHOOK, 'https://example.com'));

        $response = $this->inboundRouting->create($params);

        $body = $this->assertRequest('POST', '/v1/inbound');

        self::assertEquals(200, $response['status_code']);
        $this->assertBodyContains([
            'domain_id' => 'domainId',
            'name' => 'name',
            'domain_enabled' => true,
            'inbound_domain' => 'inbound.example.com',
            'catch_type' => Constants::CATCH_TYPE_ONE,
            'match_type' => Constants::MATCH_TYPE_ONE,
            'inbound_priority' => 10,
        ], $body);
        self::assertNotNull($body['catch_filter'] ?? null);
        self::assertNotNull($body['match_filter'] ?? null);
        self::assertNotEmpty($body['forwards'] ?? []);
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws MailerSendAssertException
     * @throws \JsonException
     */
    public function test_delete(): void
    {
        $this->addSuccessResponse();

        $response = $this->inboundRouting->delete('inboundId');

        $this->assertRequest('DELETE', '/v1/inbound/inboundId');

        self::assertEquals(200, $response['status_code']);
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function test_delete_requires_inbound_id(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Inbound id is required.');

        $this->inboundRouting->delete('');
    }

    public static function validInboundRoutingListDataProvider(): array
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
                    'page' => '1',
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
                    'limit' => '10',
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
                    'page' => '1',
                    'limit' => '10',
                ],
            ],
        ];
    }

    public static function invalidInboundRoutingListDataProvider(): array
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

    public static function validInboundRoutingCreateDataProvider(): array
    {
        return [
            'minimal params only' => [
                'params' => new InboundBuilder('domainId', 'name', true),
                'expected' => [
                    'domain_id' => 'domainId',
                    'name' => 'name',
                    'domain_enabled' => true,
                    'inbound_domain' => null,
                    'catch_filter' => null,
                    'match_filter' => null,
                    'forwards' => [],
                    'catch_type' => null,
                    'match_type' => null,
                    'inbound_priority' => null,
                ],
            ],
            'enabled, catch all, match all' => [
                'params' => (new InboundBuilder('domainId', 'name', true))
                    ->setInboundDomain('inboundDomain')
                    ->setCatchFilter(
                        (new CatchFilter(Constants::TYPE_CATCH_ALL))
                    )
                    ->setMatchFilter(
                        (new MatchFilter(Constants::TYPE_MATCH_ALL))
                    )
                    ->addForward(new Forward(Constants::TYPE_WEBHOOK, 'https://example.com')),
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
                            'value' => 'https://example.com',
                        ],
                    ],
                    'catch_type' => null,
                    'match_type' => null,
                    'inbound_priority' => null,
                ],
            ],
            'enabled, catch all, match all, with priority' => [
                'params' => (new InboundBuilder('domainId', 'name', true))
                    ->setInboundDomain('inboundDomain')
                    ->setCatchFilter(
                        (new CatchFilter(Constants::TYPE_CATCH_ALL))
                    )
                    ->setMatchFilter(
                        (new MatchFilter(Constants::TYPE_MATCH_ALL))
                    )
                    ->addForward(new Forward(Constants::TYPE_WEBHOOK, 'https://example.com'))
                    ->setInboundPriority(50),
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
                            'value' => 'https://example.com',
                        ],
                    ],
                    'catch_type' => null,
                    'match_type' => null,
                    'inbound_priority' => 50,
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
                    ->addForward(new Forward(Constants::TYPE_WEBHOOK, 'https://example.com')),
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
                            'value' => 'https://example.com',
                        ],
                    ],
                    'catch_type' => null,
                    'match_type' => null,
                    'inbound_priority' => null,
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
                    ->addForward(new Forward(Constants::TYPE_WEBHOOK, 'https://example.com')),
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
                            'value' => 'https://example.com',
                        ],
                    ],
                    'catch_type' => null,
                    'match_type' => null,
                    'inbound_priority' => null,
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
                    ->addForward(new Forward(Constants::TYPE_WEBHOOK, 'https://example.com')),
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
                            'value' => 'https://example.com',
                        ],
                    ],
                    'catch_type' => null,
                    'match_type' => null,
                    'inbound_priority' => null,
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
                    ->addForward(new Forward(Constants::TYPE_WEBHOOK, 'https://example.com')),
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
                            'value' => 'https://example.com',
                        ],
                    ],
                    'catch_type' => null,
                    'match_type' => null,
                    'inbound_priority' => null,
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
                    ->addForward(new Forward(Constants::TYPE_WEBHOOK, 'https://example.com')),
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
                            'value' => 'https://example.com',
                        ],
                    ],
                    'catch_type' => null,
                    'match_type' => null,
                    'inbound_priority' => null,
                ],
            ],
            'enabled, catch all, match sender, match one' => [
                'params' => (new InboundBuilder('domainId', 'name', true))
                    ->setInboundDomain('inboundDomain')
                    ->setCatchFilter(
                        (new CatchFilter(Constants::TYPE_CATCH_ALL))
                    )
                    ->setMatchFilter(
                        (new MatchFilter(Constants::TYPE_MATCH_SENDER))
                            ->addFilter(new Filter(Constants::COMPARER_EQUAL, 'value', 'key'))
                    )
                    ->setMatchType(Constants::MATCH_TYPE_ONE)
                    ->addForward(new Forward(Constants::TYPE_WEBHOOK, 'https://example.com')),
                'expected' => [
                    'domain_id' => 'domainId',
                    'name' => 'name',
                    'domain_enabled' => true,
                    'inbound_domain' => 'inboundDomain',
                    'catch_type' => null,
                    'match_type' => 'one',
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
                            'value' => 'https://example.com',
                        ],
                    ],
                    'inbound_priority' => null,
                ],
            ],
            'enabled, catch recipient, catch one, match all' => [
                'params' => (new InboundBuilder('domainId', 'name', true))
                    ->setInboundDomain('inboundDomain')
                    ->setCatchFilter(
                        (new CatchFilter(Constants::TYPE_CATCH_RECIPIENT))
                            ->addFilter(new Filter(Constants::COMPARER_EQUAL, 'value'))
                    )
                    ->setCatchType(Constants::CATCH_TYPE_ONE)
                    ->setMatchFilter(
                        (new MatchFilter(Constants::TYPE_MATCH_ALL))
                    )
                    ->addForward(new Forward(Constants::TYPE_WEBHOOK, 'https://example.com')),
                'expected' => [
                    'domain_id' => 'domainId',
                    'name' => 'name',
                    'domain_enabled' => true,
                    'inbound_domain' => 'inboundDomain',
                    'catch_type' => 'one',
                    'match_type' => null,
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
                            'value' => 'https://example.com',
                        ],
                    ],
                    'inbound_priority' => null,
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
                    ->addForward(new Forward(Constants::TYPE_WEBHOOK, 'https://example.com'))
                    ->addForward(new Forward(Constants::TYPE_WEBHOOK, 'https://example.com')),
                'expected' => [
                    'domain_id' => 'domainId',
                    'name' => 'name',
                    'domain_enabled' => true,
                    'inbound_domain' => 'inboundDomain',
                    'catch_type' => null,
                    'match_type' => null,
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
                            'value' => 'https://example.com',
                        ],
                        [
                            'type' => Constants::TYPE_WEBHOOK,
                            'value' => 'https://example.com',
                        ],
                    ],
                    'inbound_priority' => null,
                ],
            ],
        ];
    }

    // -------------------------------------------------------------------------
    // Enum / format validation tests
    // -------------------------------------------------------------------------

    /**
     * @dataProvider invalidCatchFilterTypeDataProvider
     */
    #[DataProvider('invalidCatchFilterTypeDataProvider')]
    public function test_catch_filter_rejects_invalid_type(string $type): void
    {
        $this->expectException(MailerSendAssertException::class);

        new CatchFilter($type);
    }

    public static function invalidCatchFilterTypeDataProvider(): array
    {
        return [
            'unknown type' => ['invalid_type'],
            'match_all is not a catch type' => [Constants::TYPE_MATCH_ALL],
            'empty string' => [''],
        ];
    }

    /**
     * @dataProvider invalidMatchFilterTypeDataProvider
     */
    #[DataProvider('invalidMatchFilterTypeDataProvider')]
    public function test_match_filter_rejects_invalid_type(string $type): void
    {
        $this->expectException(MailerSendAssertException::class);

        new MatchFilter($type);
    }

    public static function invalidMatchFilterTypeDataProvider(): array
    {
        return [
            'unknown type' => ['invalid_type'],
            'catch_all is not a match type' => [Constants::TYPE_CATCH_ALL],
            'empty string' => [''],
        ];
    }

    /**
     * @dataProvider invalidFilterComparerDataProvider
     */
    #[DataProvider('invalidFilterComparerDataProvider')]
    public function test_filter_rejects_invalid_comparer(string $comparer): void
    {
        $this->expectException(MailerSendAssertException::class);

        new Filter($comparer, 'test@example.com');
    }

    public static function invalidFilterComparerDataProvider(): array
    {
        return [
            'unknown comparer' => ['invalid_comparer'],
            'empty string' => [''],
            'partial match' => ['equal_extra'],
        ];
    }

    /**
     * @dataProvider invalidForwardTypeDataProvider
     */
    #[DataProvider('invalidForwardTypeDataProvider')]
    public function test_forward_rejects_invalid_type(string $type): void
    {
        $this->expectException(MailerSendAssertException::class);

        new Forward($type, 'https://example.com');
    }

    public static function invalidForwardTypeDataProvider(): array
    {
        return [
            'unknown type' => ['invalid_type'],
            'empty string' => [''],
            'ftp' => ['ftp'],
        ];
    }

    public function test_forward_rejects_value_exceeding_191_chars(): void
    {
        $this->expectException(MailerSendAssertException::class);

        new Forward(Constants::TYPE_WEBHOOK, 'https://example.com/' . str_repeat('a', 180));
    }

    public function test_forward_webhook_rejects_invalid_url(): void
    {
        $this->expectException(MailerSendAssertException::class);

        new Forward(Constants::TYPE_WEBHOOK, 'not-a-valid-url');
    }

    public function test_forward_email_rejects_invalid_email(): void
    {
        $this->expectException(MailerSendAssertException::class);

        new Forward(Constants::TYPE_EMAIL, 'not-a-valid-email');
    }

    // -------------------------------------------------------------------------
    // Cardinality validation tests
    // -------------------------------------------------------------------------

    public function test_inbound_builder_rejects_more_than_5_forwards_via_add_forward(): void
    {
        $this->expectException(MailerSendAssertException::class);

        $builder = new InboundBuilder('domainId', 'name', true);

        for ($i = 0; $i < 6; $i++) {
            $builder->addForward(new Forward(Constants::TYPE_WEBHOOK, 'https://example.com'));
        }
    }

    public function test_inbound_builder_rejects_more_than_5_forwards_via_set_forwards(): void
    {
        $this->expectException(MailerSendAssertException::class);

        $forwards = [];
        for ($i = 0; $i < 6; $i++) {
            $forwards[] = new Forward(Constants::TYPE_WEBHOOK, 'https://example.com');
        }

        (new InboundBuilder('domainId', 'name', true))->setForwards($forwards);
    }

    public function test_inbound_builder_rejects_empty_forwards_via_set_forwards(): void
    {
        $this->expectException(MailerSendAssertException::class);

        (new InboundBuilder('domainId', 'name', true))->setForwards([]);
    }

    public function test_catch_filter_rejects_more_than_5_filters_via_add_filter(): void
    {
        $this->expectException(MailerSendAssertException::class);

        $filter = new CatchFilter(Constants::TYPE_CATCH_RECIPIENT);

        for ($i = 0; $i < 6; $i++) {
            $filter->addFilter(new Filter(Constants::COMPARER_EQUAL, 'value'));
        }
    }

    public function test_catch_filter_rejects_more_than_5_filters_via_set_filters(): void
    {
        $this->expectException(MailerSendAssertException::class);

        $filters = [];
        for ($i = 0; $i < 6; $i++) {
            $filters[] = new Filter(Constants::COMPARER_EQUAL, 'value');
        }

        (new CatchFilter(Constants::TYPE_CATCH_RECIPIENT))->setFilters($filters);
    }

    public function test_match_filter_rejects_more_than_5_filters_via_add_filter(): void
    {
        $this->expectException(MailerSendAssertException::class);

        $filter = new MatchFilter(Constants::TYPE_MATCH_SENDER);

        for ($i = 0; $i < 6; $i++) {
            $filter->addFilter(new Filter(Constants::COMPARER_EQUAL, 'value'));
        }
    }

    public function test_match_filter_rejects_more_than_5_filters_via_set_filters(): void
    {
        $this->expectException(MailerSendAssertException::class);

        $filters = [];
        for ($i = 0; $i < 6; $i++) {
            $filters[] = new Filter(Constants::COMPARER_EQUAL, 'value');
        }

        (new MatchFilter(Constants::TYPE_MATCH_SENDER))->setFilters($filters);
    }
}
