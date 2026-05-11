<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\Domain;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Builder\DomainParams;
use MailerSend\Helpers\Builder\DomainSettingsParams;
use MailerSend\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
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

        $this->defaultResponse = $this->createStub(ResponseInterface::class);
        $this->defaultResponse->method('getStatusCode')->willReturn(200);
    }

    public function test_get_all_sends_correct_method_and_path(): void
    {
        $this->addSuccessResponse();

        $this->domain->getAll();

        $request = $this->client->getLastRequest();
        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/v1/domains', $request->getUri()->getPath());
    }

    public function test_get_all_returns_status_code(): void
    {
        $this->addSuccessResponse();

        $response = $this->domain->getAll();

        self::assertEquals(200, $response['status_code']);
    }

    public function test_get_all_with_page(): void
    {
        $this->addSuccessResponse();

        $this->domain->getAll(2);

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);

        $this->assertQueryParams(['page' => '2'], $query);
    }

    public function test_get_all_with_limit(): void
    {
        $this->addSuccessResponse();

        $this->domain->getAll(null, 10);

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);

        $this->assertQueryParams(['limit' => '10'], $query);
    }

    public function test_get_all_with_verified_true(): void
    {
        $this->addSuccessResponse();

        $this->domain->getAll(null, null, true);

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);

        self::assertArrayHasKey('verified', $query);
        self::assertEquals('1', $query['verified']);
    }

    public function test_get_all_with_verified_false(): void
    {
        $this->addSuccessResponse();

        $this->domain->getAll(null, null, false);

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);

        self::assertArrayHasKey('verified', $query);
        self::assertEquals('0', $query['verified']);
    }

    public function test_get_all_with_all_params(): void
    {
        $this->addSuccessResponse();

        $this->domain->getAll(3, 20, true);

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);

        $this->assertQueryParams(['page' => '3', 'limit' => '20'], $query);
        self::assertEquals('1', $query['verified']);
    }

    /**
     * @dataProvider invalidGetAllLimitProvider
     */
    #[DataProvider('invalidGetAllLimitProvider')]
    public function test_get_all_rejects_invalid_limit(int $limit, string $exceptionMessage): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $this->domain->getAll(null, $limit);
    }

    public static function invalidGetAllLimitProvider(): array
    {
        return [
            'limit below minimum' => [9, 'Limit is supposed to be between 10 and 100.'],
            'limit above maximum' => [101, 'Limit is supposed to be between 10 and 100.'],
        ];
    }

    public function test_find(): void
    {
        $this->addSuccessResponse();

        $domain_id = 'domain_id';

        $response = $this->domain->find($domain_id);

        $this->assertRequest('GET', "/v1/domains/$domain_id");
        self::assertEquals(200, $response['status_code']);
    }

    public function test_find_requires_domain_id(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Domain id is required.');

        $this->domain->find('');
    }

    public function test_create(): void
    {
        $this->addSuccessResponse();

        $response = $this->domain->create(
            (new DomainParams('mailersend.com'))
        );

        $body = $this->assertRequest('POST', '/v1/domains');

        self::assertEquals(200, $response['status_code']);
        self::assertSame('mailersend.com', $body['name']);
    }

    public function test_create_without_optional_params_excludes_them_from_body(): void
    {
        $this->addSuccessResponse();

        $this->domain->create(new DomainParams('mailersend.com'));

        $body = $this->assertRequest('POST', '/v1/domains');

        // DomainParams::toArray() always emits these keys but they should be null when unset
        self::assertNull($body['return_path_subdomain']);
        self::assertNull($body['custom_tracking_subdomain']);
        self::assertNull($body['inbound_routing_subdomain']);
    }

    public function test_create_with_return_path_subdomain(): void
    {
        $this->addSuccessResponse();

        $this->domain->create(
            (new DomainParams('mailersend.com'))->setReturnPathSubdomain('bounce')
        );

        $body = $this->assertRequest('POST', '/v1/domains');

        self::assertSame('bounce', $body['return_path_subdomain']);
    }

    public function test_create_with_custom_tracking_subdomain(): void
    {
        $this->addSuccessResponse();

        $this->domain->create(
            (new DomainParams('mailersend.com'))->setCustomTrackingSubdomain('track')
        );

        $body = $this->assertRequest('POST', '/v1/domains');

        self::assertSame('track', $body['custom_tracking_subdomain']);
    }

    public function test_create_with_inbound_routing_subdomain(): void
    {
        $this->addSuccessResponse();

        $this->domain->create(
            (new DomainParams('mailersend.com'))->setInboundRoutingSubdomain('inbound')
        );

        $body = $this->assertRequest('POST', '/v1/domains');

        self::assertSame('inbound', $body['inbound_routing_subdomain']);
    }

    public function test_create_with_all_optional_params(): void
    {
        $this->addSuccessResponse();

        $response = $this->domain->create(
            (new DomainParams('mailersend.com'))
                ->setReturnPathSubdomain('bounce')
                ->setCustomTrackingSubdomain('track')
                ->setInboundRoutingSubdomain('inbound')
        );

        $body = $this->assertRequest('POST', '/v1/domains');

        self::assertEquals(200, $response['status_code']);
        self::assertSame('mailersend.com', $body['name']);
        self::assertSame('bounce', $body['return_path_subdomain']);
        self::assertSame('track', $body['custom_tracking_subdomain']);
        self::assertSame('inbound', $body['inbound_routing_subdomain']);
    }

    public function test_create_requires_domain_name(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Domain name is required.');

        $this->domain->create(new DomainParams(''));
    }

    public function test_delete(): void
    {
        $this->addSuccessResponse();

        $domain_id = 'domain_id';

        $response = $this->domain->delete($domain_id);

        $this->assertRequest('DELETE', "/v1/domains/$domain_id");
        self::assertEquals(200, $response['status_code']);
    }

    public function test_delete_requires_domain_id(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Domain id is required.');

        $this->domain->delete('');
    }

    public function test_recipients_sends_correct_method_and_path(): void
    {
        $this->addSuccessResponse();

        $domain_id = 'domain_id';

        $this->domain->recipients($domain_id);

        $request = $this->client->getLastRequest();
        self::assertEquals('GET', $request->getMethod());
        self::assertEquals("/v1/domains/$domain_id/recipients", $request->getUri()->getPath());
    }

    public function test_recipients_returns_status_code(): void
    {
        $this->addSuccessResponse();

        $response = $this->domain->recipients('domain_id');

        self::assertEquals(200, $response['status_code']);
    }

    public function test_recipients_with_page(): void
    {
        $this->addSuccessResponse();

        $this->domain->recipients('domain_id', 2);

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);

        $this->assertQueryParams(['page' => '2'], $query);
    }

    public function test_recipients_with_limit(): void
    {
        $this->addSuccessResponse();

        $this->domain->recipients('domain_id', null, 10);

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);

        $this->assertQueryParams(['limit' => '10'], $query);
    }

    public function test_recipients_with_page_and_limit(): void
    {
        $this->addSuccessResponse();

        $this->domain->recipients('domain_id', 3, 20);

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);

        $this->assertQueryParams(['page' => '3', 'limit' => '20'], $query);
    }

    /**
     * @dataProvider invalidRecipientsParamsProvider
     */
    #[DataProvider('invalidRecipientsParamsProvider')]
    public function test_recipients_rejects_invalid_params(string $domainId, ?int $page, ?int $limit, string $exceptionMessage): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $this->domain->recipients($domainId, $page, $limit);
    }

    public static function invalidRecipientsParamsProvider(): array
    {
        return [
            'domain id missing' => ['', null, null, 'Domain id is required.'],
            'limit below minimum' => ['domain_id', null, 9, 'Limit is supposed to be between 10 and 100.'],
            'limit above maximum' => ['domain_id', null, 101, 'Limit is supposed to be between 10 and 100.'],
        ];
    }

    /**
     * @dataProvider domainSettingsDataProvider
     */
    #[DataProvider('domainSettingsDataProvider')]
    public function test_domain_settings(DomainSettingsParams $domainSettingsParams): void
    {
        $this->addSuccessResponse();

        $domain_id = 'domain_id';

        $response = $this->domain->domainSettings($domain_id, $domainSettingsParams);

        $body = $this->assertRequest('PUT', "/v1/domains/$domain_id/settings");

        self::assertEquals(200, $response['status_code']);

        self::assertEquals($domainSettingsParams->getSendPaused(), $body['send_paused'] ?? null);
        self::assertEquals($domainSettingsParams->getTrackClicks(), $body['track_clicks'] ?? null);
        self::assertEquals($domainSettingsParams->getTrackOpens(), $body['track_opens'] ?? null);
        self::assertEquals($domainSettingsParams->getTrackUnsubscribe(), $body['track_unsubscribe'] ?? null);
        self::assertEquals($domainSettingsParams->getTrackContent(), $body['track_content'] ?? null);
        self::assertEquals($domainSettingsParams->getTrackUnsubscribeHtml(), $body['track_unsubscribe_html'] ?? null);
        self::assertEquals($domainSettingsParams->getTrackUnsubscribeHtmlEnabled(), $body['track_unsubscribe_html_enabled'] ?? null);
        self::assertEquals($domainSettingsParams->getTrackUnsubscribePlain(), $body['track_unsubscribe_plain'] ?? null);
        self::assertEquals($domainSettingsParams->getTrackUnsubscribePlainEnabled(), $body['track_unsubscribe_plain_enabled'] ?? null);
        self::assertEquals($domainSettingsParams->getCustomTrackingEnabled(), $body['custom_tracking_enabled'] ?? null);
        self::assertEquals($domainSettingsParams->getCustomTrackingSubdomain(), $body['custom_tracking_subdomain'] ?? null);
        self::assertEquals($domainSettingsParams->getPrecedenceBulk(), $body['precedence_bulk'] ?? null);
        self::assertEquals($domainSettingsParams->getIgnoreDuplicatedRecipients(), $body['ignore_duplicated_recipients'] ?? null);
    }

    public function test_domain_settings_requires_domain_id(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Domain id is required.');

        $this->domain->domainSettings('', new DomainSettingsParams());
    }

    public function test_domain_settings_sends_all_params(): void
    {
        $this->addSuccessResponse();

        $params = (new DomainSettingsParams())
            ->setSendPaused(true)
            ->setTrackClicks(true)
            ->setTrackOpens(false)
            ->setTrackUnsubscribe(true)
            ->setTrackContent(false)
            ->setTrackUnsubscribeHtml('<a href="#">Unsubscribe</a>')
            ->setTrackUnsubscribeHtmlEnabled(true)
            ->setTrackUnsubscribePlain('Unsubscribe')
            ->setTrackUnsubscribePlainEnabled(false)
            ->setCustomTrackingEnabled(true)
            ->setCustomTrackingSubdomain('track.example.com')
            ->setPrecedenceBulk(true)
            ->setIgnoreDuplicatedRecipients(false);

        $this->domain->domainSettings('domain-id', $params);

        $body = $this->assertRequest('PUT', '/v1/domains/domain-id/settings');

        $this->assertBodyContains([
            'send_paused' => true,
            'track_clicks' => true,
            'track_opens' => false,
            'track_unsubscribe' => true,
            'track_content' => false,
            'track_unsubscribe_html' => '<a href="#">Unsubscribe</a>',
            'track_unsubscribe_html_enabled' => true,
            'track_unsubscribe_plain' => 'Unsubscribe',
            'track_unsubscribe_plain_enabled' => false,
            'custom_tracking_enabled' => true,
            'custom_tracking_subdomain' => 'track.example.com',
            'precedence_bulk' => true,
            'ignore_duplicated_recipients' => false,
        ], $body);
    }

    public static function domainSettingsDataProvider(): array
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
                    ->setTrackUnsubscribeHtmlEnabled(true)
                    ->setTrackUnsubscribePlain('plain')
                    ->setTrackUnsubscribePlainEnabled(false)
                    ->setCustomTrackingEnabled(true)
                    ->setCustomTrackingSubdomain('track')
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
            'with unsubscribe html enabled' => [
                (new DomainSettingsParams())
                    ->setTrackUnsubscribeHtmlEnabled(true),
            ],
            'with unsubscribe plain' => [
                (new DomainSettingsParams())
                    ->setTrackUnsubscribePlain('plain'),
            ],
            'with unsubscribe plain enabled' => [
                (new DomainSettingsParams())
                    ->setTrackUnsubscribePlainEnabled(true),
            ],
            'with custom tracking enabled' => [
                (new DomainSettingsParams())
                    ->setCustomTrackingEnabled(true),
            ],
            'with custom tracking subdomain' => [
                (new DomainSettingsParams())
                    ->setCustomTrackingSubdomain('track'),
            ],
            'with precedence bulk' => [
                (new DomainSettingsParams())
                    ->setPrecedenceBulk(true),
            ],
            'with ignore duplicated recipients' => [
                (new DomainSettingsParams())
                    ->setIgnoreDuplicatedRecipients(true),
            ],
        ];
    }

    public function test_verify(): void
    {
        $this->addSuccessResponse();

        $domain_id = 'domain_id';

        $response = $this->domain->verify($domain_id);

        $this->assertRequest('GET', "/v1/domains/$domain_id/verify");
        self::assertEquals(200, $response['status_code']);
    }

    public function test_verify_requires_domain_id(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Domain id is required.');

        $this->domain->verify('');
    }

    public function test_get_dns_records(): void
    {
        $this->addSuccessResponse();

        $domain_id = 'domain_id';

        $response = $this->domain->getDnsRecords($domain_id);

        $this->assertRequest('GET', "/v1/domains/$domain_id/dns-records");
        self::assertEquals(200, $response['status_code']);
    }

    public function test_get_dns_records_requires_domain_id(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Domain id is required.');

        $this->domain->getDnsRecords('');
    }
}
