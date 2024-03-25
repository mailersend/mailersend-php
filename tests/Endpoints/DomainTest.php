<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use Illuminate\Support\Arr;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\Domain;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Builder\DomainParams;
use MailerSend\Helpers\Builder\DomainSettingsParams;
use MailerSend\Tests\TestCase;
use Psr\Http\Message\ResponseInterface;

class DomainTest extends TestCase
{
    protected Domain $domain;
    protected ResponseInterface $defaultResponse;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = new Client();

        $this->domain = new Domain(new HttpLayer(self::OPTIONS, $this->client), self::OPTIONS);

        $this->defaultResponse = $this->createMock(ResponseInterface::class);
        $this->defaultResponse->method('getStatusCode')->willReturn(200);
    }

    /**
     * @dataProvider validDomainListDataProvider
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_get_all(array $domainParams, array $expected): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $this->client->addResponse($response);

        $response = $this->domain->getAll(
            $domainParams['page'] ?? null,
            $domainParams['limit'] ?? null,
            $domainParams['verified'] ?? null
        );

        $request = $this->client->getLastRequest();

        parse_str($request->getUri()->getQuery(), $query);

        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/v1/domains', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);

        self::assertEquals(Arr::get($expected, 'page'), Arr::get($query, 'page'));
        self::assertEquals(Arr::get($expected, 'limit'), Arr::get($query, 'limit'));
        self::assertEquals(Arr::get($expected, 'verified'), Arr::get($query, 'verified'));
    }

    /**
     * @dataProvider invalidDomainListDataProvider
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_get_all_with_errors(array $domainParams): void
    {
        $this->expectException(MailerSendAssertException::class);

        $this->domain->getAll(
            $domainParams['page'] ?? null,
            $domainParams['limit'] ?? null,
            $domainParams['verified'] ?? null
        );
    }

    public function test_find_requires_domain_id()
    {
        $this->expectException(MailerSendAssertException::class);

        $this->domain->find('');
    }

    public function test_create(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $this->client->addResponse($response);

        $response = $this->domain->create(
            (new DomainParams('mailersend.com'))
        );

        $request = $this->client->getLastRequest();
        $request_body = json_decode((string)$request->getBody(), true, 512, JSON_THROW_ON_ERROR);

        self::assertEquals('POST', $request->getMethod());
        self::assertEquals('/v1/domains', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);
        self::assertSame('mailersend.com', Arr::get($request_body, 'name'));
    }

    public function test_delete_requires_domain_id()
    {
        $this->expectException(MailerSendAssertException::class);

        $this->domain->delete('');
    }

    /**
     * @dataProvider validDomainRecipientsDataProvider
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_recipients(array $domainParams, array $expected): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $this->client->addResponse($response);

        $domain_id = 'domain_id';

        $response = $this->domain->recipients(
            $domain_id,
            $domainParams['page'] ?? null,
            $domainParams['limit'] ?? null
        );

        $request = $this->client->getLastRequest();

        parse_str($request->getUri()->getQuery(), $query);

        self::assertEquals('GET', $request->getMethod());
        self::assertEquals("/v1/domains/$domain_id/recipients", $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);

        self::assertEquals(Arr::get($expected, 'page'), Arr::get($query, 'page'));
        self::assertEquals(Arr::get($expected, 'limit'), Arr::get($query, 'limit'));
    }

    /**
     * @dataProvider invalidDomainRecipientsDataProvider
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_recipients_with_errors(array $domainParams): void
    {
        $this->expectException(MailerSendAssertException::class);

        $this->domain->recipients(
            $domainParams['domain_id'] ?? null,
            $domainParams['page'] ?? null,
            $domainParams['limit'] ?? null
        );
    }

    /**
     * @dataProvider domainSettingsDataProvider
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_domain_settings(DomainSettingsParams $domainSettingsParams): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $this->client->addResponse($response);

        $domain_id = 'domain_id';

        $response = $this->domain->domainSettings($domain_id, $domainSettingsParams);

        $request = $this->client->getLastRequest();
        $request_body = json_decode((string) $request->getBody(), true);

        self::assertEquals('PUT', $request->getMethod());
        self::assertEquals("/v1/domains/$domain_id/settings", $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);

        self::assertEquals($domainSettingsParams->getSendPaused(), Arr::get($request_body, 'send_paused'));
        self::assertEquals($domainSettingsParams->getTrackClicks(), Arr::get($request_body, 'track_clicks'));
        self::assertEquals($domainSettingsParams->getTrackOpens(), Arr::get($request_body, 'track_opens'));
        self::assertEquals($domainSettingsParams->getTrackUnsubscribe(), Arr::get($request_body, 'track_unsubscribe'));
        self::assertEquals($domainSettingsParams->getTrackContent(), Arr::get($request_body, 'track_content'));
        self::assertEquals($domainSettingsParams->getTrackUnsubscribeHtml(), Arr::get($request_body, 'track_unsubscribe_html'));
        self::assertEquals($domainSettingsParams->getTrackUnsubscribePlain(), Arr::get($request_body, 'track_unsubscribe_plain'));
        self::assertEquals($domainSettingsParams->getCustomTrackingEnabled(), Arr::get($request_body, 'custom_tracking_enabled'));
        self::assertEquals($domainSettingsParams->getCustomTrackingSubdomain(), Arr::get($request_body, 'custom_tracking_subdomain'));
        self::assertEquals($domainSettingsParams->getPrecedenceBulk(), Arr::get($request_body, 'precedence_bulk'));
        self::assertEquals($domainSettingsParams->getIgnoreDuplicatedRecipients(), Arr::get($request_body, 'ignore_duplicated_recipients'));
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function test_verify_required_domain_id(): void
    {
        $this->expectException(MailerSendAssertException::class);

        $this->domain->verify('');
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function test_get_dns_records_requires_id(): void
    {
        $this->expectException(MailerSendAssertException::class);

        $this->domain->getDnsRecords('');
    }

    public function validDomainListDataProvider(): array
    {
        return [
            'empty request' => [
                [],
                [
                    'page' => null,
                    'limit' => null,
                    'verified' => null,
                ],
            ],
            'with page' => [
                [
                    'page' => 1,
                ],
                [
                    'page' => 1,
                    'limit' => null,
                    'verified' => null,
                ],
            ],
            'with limit' => [
                [
                    'limit' => 10,
                ],
                [
                    'page' => null,
                    'limit' => 10,
                    'verified' => null,
                ],
            ],
            'with verified true' => [
                [
                    'verified' => true,
                ],
                [
                    'page' => null,
                    'limit' => null,
                    'verified' => true,
                ],
            ],
            'with verified false' => [
                [
                    'verified' => false,
                ],
                [
                    'page' => null,
                    'limit' => null,
                    'verified' => false,
                ],
            ],
            'complete request' => [
                [
                    'page' => 1,
                    'limit' => 10,
                    'verified' => true,
                ],
                [
                    'page' => 1,
                    'limit' => 10,
                    'verified' => true,
                ],
            ],
        ];
    }

    public function validDomainRecipientsDataProvider(): array
    {
        return [
            'empty request' => [
                [],
                [
                    'page' => null,
                    'limit' => null,
                ],
            ],
            'with page' => [
                [
                    'page' => 1
                ],
                [
                    'page' => 1,
                    'limit' => null,
                ],
            ],
            'with limit' => [
                [
                    'limit' => 10,
                ],
                [
                    'page' => null,
                    'limit' => 10,
                ]
            ],
            'complete request' => [
                [
                    'page' => 1,
                    'limit' => 10,
                ],
                [
                    'page' => 1,
                    'limit' => 10,
                ]
            ]
        ];
    }

    public function invalidDomainListDataProvider(): array
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

    public function invalidDomainRecipientsDataProvider(): array
    {
        return [
            'domain id missing' => [
                [
                    'domain_id' => '',
                ]
            ],
            'with limit under 10' => [
                [
                    'domain_id' => 'domain_id',
                    'limit' => 9,
                ],
            ],
            'with limit over 100' => [
                [
                    'domain_id' => 'domain_id',
                    'limit' => 101,
                ],
            ]
        ];
    }

    public function domainSettingsDataProvider(): array
    {
        return [
            'complete request' => [
                  (new DomainSettingsParams())
                    ->setSendPaused(true)
                    ->setTrackClicks(true)
                    ->setTrackOpens(false)
                    ->setTrackUnsubscribe(false)
                    ->setTrackContent(true)
                    ->setTrackUnsubscribeHtml('html')
                    ->setTrackUnsubscribePlain('plain')
                    ->setCustomTrackingEnabled(true)
                    ->setCustomTrackingSubdomain(false)
                    ->setPrecedenceBulk(false)
                    ->setIgnoreDuplicatedRecipients(false),
            ],
            'with send paused' => [
                (new DomainSettingsParams())
                    ->setSendPaused(true),
            ],
            'with track clicks' => [
                (new DomainSettingsParams())
                    ->setTrackClicks(true),
            ],
            'with track opens' => [
                (new DomainSettingsParams())
                    ->setTrackOpens(true),
            ],
            'with track unsubscribes' => [
                (new DomainSettingsParams())
                    ->setTrackUnsubscribe(true),
            ],
            'with track content' => [
                (new DomainSettingsParams())
                    ->setTrackContent(true),
            ],
            'with unsubscribe html' => [
                (new DomainSettingsParams())
                    ->setTrackUnsubscribeHtml('html'),
            ],
            'with unsubscribe plain' => [
                (new DomainSettingsParams())
                    ->setTrackUnsubscribePlain('plain'),
            ],
            'with custom tracking enabled' => [
                (new DomainSettingsParams())
                    ->setCustomTrackingEnabled(true),
            ],
            'with custom tracking subdomain' => [
                (new DomainSettingsParams())
                    ->setCustomTrackingSubdomain(true),
            ],
            'with precedence bulk' => [
                (new DomainSettingsParams())
                    ->setCustomTrackingSubdomain(true),
            ],
            'with ignore duplicated emails' => [
                (new DomainSettingsParams())
                    ->setIgnoreDuplicatedRecipients(true),
            ],
        ];
    }
}
