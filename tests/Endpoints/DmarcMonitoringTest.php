<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\DmarcMonitoring;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Builder\DmarcMonitoringParams;
use MailerSend\Helpers\Builder\DmarcMonitoringUpdateParams;
use MailerSend\Helpers\Arr;
use MailerSend\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Http\Message\ResponseInterface;

class DmarcMonitoringTest extends TestCase
{
    protected DmarcMonitoring $dmarcMonitoring;
    protected ResponseInterface $defaultResponse;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = new Client();
        $this->dmarcMonitoring = new DmarcMonitoring(new HttpLayer(self::OPTIONS, $this->client), self::OPTIONS);

        $this->defaultResponse = $this->createStub(ResponseInterface::class);
        $this->defaultResponse->method('getStatusCode')->willReturn(200);
    }

    // -------------------------------------------------------------------------
    // getAll
    // -------------------------------------------------------------------------

    /**
     * @dataProvider validGetAllDataProvider
     */
    #[DataProvider('validGetAllDataProvider')]
    public function test_get_all(array $params, array $expected): void
    {
        $response = $this->createStub(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $this->client->addResponse($response);

        $response = $this->dmarcMonitoring->getAll(
            $params['page'] ?? null,
            $params['limit'] ?? null,
            $params['query'] ?? null,
            $params['sort_by'] ?? null,
            $params['order'] ?? null
        );

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);

        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/v1/dmarc-monitoring', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);
        self::assertEquals(Arr::get($expected, 'page'), Arr::get($query, 'page'));
        self::assertEquals(Arr::get($expected, 'limit'), Arr::get($query, 'limit'));
        self::assertEquals(Arr::get($expected, 'query'), Arr::get($query, 'query'));
        self::assertEquals(Arr::get($expected, 'sort_by'), Arr::get($query, 'sort_by'));
        self::assertEquals(Arr::get($expected, 'order'), Arr::get($query, 'order'));
    }

    /**
     * @dataProvider invalidGetAllDataProvider
     */
    #[DataProvider('invalidGetAllDataProvider')]
    public function test_get_all_with_errors(array $params): void
    {
        $this->expectException(MailerSendAssertException::class);

        $this->dmarcMonitoring->getAll(
            $params['page'] ?? null,
            $params['limit'] ?? null,
            $params['query'] ?? null,
            $params['sort_by'] ?? null,
            $params['order'] ?? null
        );
    }

    public static function validGetAllDataProvider(): array
    {
        return [
            'empty request' => [
                [],
                ['page' => null, 'limit' => null],
            ],
            'with page' => [
                ['page' => 1],
                ['page' => '1', 'limit' => null],
            ],
            'with limit' => [
                ['limit' => 10],
                ['page' => null, 'limit' => '10'],
            ],
            'with page and limit' => [
                ['page' => 2, 'limit' => 25],
                ['page' => '2', 'limit' => '25'],
            ],
            'with query' => [
                ['query' => 'example.com'],
                ['query' => 'example.com'],
            ],
            'with sort_by' => [
                ['sort_by' => 'created_at'],
                ['sort_by' => 'created_at'],
            ],
            'with order' => [
                ['order' => 'desc'],
                ['order' => 'desc'],
            ],
            'with all filters' => [
                ['page' => 1, 'limit' => 25, 'query' => 'example.com', 'sort_by' => 'updated_at', 'order' => 'asc'],
                ['page' => '1', 'limit' => '25', 'query' => 'example.com', 'sort_by' => 'updated_at', 'order' => 'asc'],
            ],
        ];
    }

    public static function invalidGetAllDataProvider(): array
    {
        return [
            'limit too low' => [['limit' => 9]],
            'limit too high' => [['limit' => 101]],
            'invalid sort_by' => [['sort_by' => 'invalid_field']],
            'invalid order' => [['order' => 'random']],
        ];
    }

    // -------------------------------------------------------------------------
    // create
    // -------------------------------------------------------------------------

    public function test_create(): void
    {
        $response = $this->createStub(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(201);
        $this->client->addResponse($response);

        $response = $this->dmarcMonitoring->create(new DmarcMonitoringParams('domain-id-123'));

        $request = $this->client->getLastRequest();
        $request_body = json_decode((string) $request->getBody(), true, 512, JSON_THROW_ON_ERROR);

        self::assertEquals('POST', $request->getMethod());
        self::assertEquals('/v1/dmarc-monitoring', $request->getUri()->getPath());
        self::assertSame('domain-id-123', Arr::get($request_body, 'domain_id'));
    }

    public function test_create_requires_domain_id(): void
    {
        $this->expectException(MailerSendAssertException::class);

        $this->dmarcMonitoring->create(new DmarcMonitoringParams(''));
    }

    // -------------------------------------------------------------------------
    // update
    // -------------------------------------------------------------------------

    public function test_update(): void
    {
        $response = $this->createStub(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $this->client->addResponse($response);

        $monitorId = 'monitor-id-123';
        $response = $this->dmarcMonitoring->update($monitorId, new DmarcMonitoringUpdateParams('v=DMARC1; p=reject;'));

        $request = $this->client->getLastRequest();
        $request_body = json_decode((string) $request->getBody(), true, 512, JSON_THROW_ON_ERROR);

        self::assertEquals('PUT', $request->getMethod());
        self::assertEquals("/v1/dmarc-monitoring/$monitorId", $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);
        self::assertSame('v=DMARC1; p=reject;', Arr::get($request_body, 'wanted_dmarc_record'));
    }

    public function test_update_requires_monitor_id(): void
    {
        $this->expectException(MailerSendAssertException::class);

        $this->dmarcMonitoring->update('', new DmarcMonitoringUpdateParams('v=DMARC1; p=reject;'));
    }

    public function test_update_requires_wanted_dmarc_record(): void
    {
        $this->expectException(MailerSendAssertException::class);

        $this->dmarcMonitoring->update('monitor-id-123', new DmarcMonitoringUpdateParams(''));
    }

    // -------------------------------------------------------------------------
    // delete
    // -------------------------------------------------------------------------

    public function test_delete(): void
    {
        $response = $this->createStub(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(204);
        $this->client->addResponse($response);

        $monitorId = 'monitor-id-123';
        $this->dmarcMonitoring->delete($monitorId);

        $request = $this->client->getLastRequest();

        self::assertEquals('DELETE', $request->getMethod());
        self::assertEquals("/v1/dmarc-monitoring/$monitorId", $request->getUri()->getPath());
    }

    public function test_delete_requires_monitor_id(): void
    {
        $this->expectException(MailerSendAssertException::class);

        $this->dmarcMonitoring->delete('');
    }

    // -------------------------------------------------------------------------
    // getAggregatedReports
    // -------------------------------------------------------------------------

    /**
     * @dataProvider validPaginatedWithMonitorIdDataProvider
     */
    #[DataProvider('validPaginatedWithMonitorIdDataProvider')]
    public function test_get_aggregated_reports(array $params, array $expected): void
    {
        $response = $this->createStub(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $this->client->addResponse($response);

        $monitorId = 'monitor-id-123';
        $response = $this->dmarcMonitoring->getAggregatedReports(
            $monitorId,
            $params['page'] ?? null,
            $params['limit'] ?? null,
            $params['date_from'] ?? null,
            $params['date_to'] ?? null,
            $params['search'] ?? null,
            $params['category'] ?? null,
            $params['report_source'] ?? null
        );

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);

        self::assertEquals('GET', $request->getMethod());
        self::assertEquals("/v1/dmarc-monitoring/$monitorId/report", $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);
        self::assertEquals(Arr::get($expected, 'page'), Arr::get($query, 'page'));
        self::assertEquals(Arr::get($expected, 'limit'), Arr::get($query, 'limit'));
        self::assertEquals(Arr::get($expected, 'date_from'), Arr::get($query, 'date_from'));
        self::assertEquals(Arr::get($expected, 'date_to'), Arr::get($query, 'date_to'));
        self::assertEquals(Arr::get($expected, 'search'), Arr::get($query, 'search'));
        self::assertEquals(Arr::get($expected, 'category'), Arr::get($query, 'category'));
        self::assertEquals(Arr::get($expected, 'report_source'), Arr::get($query, 'report_source'));
    }

    public function test_get_aggregated_reports_with_filters(): void
    {
        $response = $this->createStub(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $this->client->addResponse($response);

        $monitorId = 'monitor-id-123';
        $this->dmarcMonitoring->getAggregatedReports(
            $monitorId,
            1,
            25,
            '2024-01-01',
            '2024-01-31',
            'example.com',
            'dmarc',
            'google.com'
        );

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);

        self::assertEquals('2024-01-01', Arr::get($query, 'date_from'));
        self::assertEquals('2024-01-31', Arr::get($query, 'date_to'));
        self::assertEquals('example.com', Arr::get($query, 'search'));
        self::assertEquals('dmarc', Arr::get($query, 'category'));
        self::assertEquals('google.com', Arr::get($query, 'report_source'));
    }

    public function test_get_aggregated_reports_requires_monitor_id(): void
    {
        $this->expectException(MailerSendAssertException::class);

        $this->dmarcMonitoring->getAggregatedReports('');
    }

    /**
     * @dataProvider invalidLimitDataProvider
     */
    #[DataProvider('invalidLimitDataProvider')]
    public function test_get_aggregated_reports_with_invalid_limit(int $limit): void
    {
        $this->expectException(MailerSendAssertException::class);

        $this->dmarcMonitoring->getAggregatedReports('monitor-id-123', null, $limit);
    }

    // -------------------------------------------------------------------------
    // getIpReports
    // -------------------------------------------------------------------------

    /**
     * @dataProvider validPaginatedWithMonitorIdDataProvider
     */
    #[DataProvider('validPaginatedWithMonitorIdDataProvider')]
    public function test_get_ip_reports(array $params, array $expected): void
    {
        $response = $this->createStub(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $this->client->addResponse($response);

        $monitorId = 'monitor-id-123';
        $ip = '1.2.3.4';
        $response = $this->dmarcMonitoring->getIpReports(
            $monitorId,
            $ip,
            $params['page'] ?? null,
            $params['limit'] ?? null,
            $params['date_from'] ?? null,
            $params['date_to'] ?? null,
            $params['search'] ?? null,
            $params['category'] ?? null,
            $params['report_source'] ?? null
        );

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);

        self::assertEquals('GET', $request->getMethod());
        self::assertEquals("/v1/dmarc-monitoring/$monitorId/report/$ip", $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);
        self::assertEquals(Arr::get($expected, 'page'), Arr::get($query, 'page'));
        self::assertEquals(Arr::get($expected, 'limit'), Arr::get($query, 'limit'));
        self::assertEquals(Arr::get($expected, 'date_from'), Arr::get($query, 'date_from'));
        self::assertEquals(Arr::get($expected, 'date_to'), Arr::get($query, 'date_to'));
        self::assertEquals(Arr::get($expected, 'search'), Arr::get($query, 'search'));
        self::assertEquals(Arr::get($expected, 'category'), Arr::get($query, 'category'));
        self::assertEquals(Arr::get($expected, 'report_source'), Arr::get($query, 'report_source'));
    }

    public function test_get_ip_reports_with_filters(): void
    {
        $response = $this->createStub(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $this->client->addResponse($response);

        $monitorId = 'monitor-id-123';
        $ip = '1.2.3.4';
        $this->dmarcMonitoring->getIpReports(
            $monitorId,
            $ip,
            1,
            25,
            '2024-01-01',
            '2024-01-31',
            'example.com',
            'dmarc',
            'google.com'
        );

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);

        self::assertEquals('2024-01-01', Arr::get($query, 'date_from'));
        self::assertEquals('2024-01-31', Arr::get($query, 'date_to'));
        self::assertEquals('example.com', Arr::get($query, 'search'));
        self::assertEquals('dmarc', Arr::get($query, 'category'));
        self::assertEquals('google.com', Arr::get($query, 'report_source'));
    }

    public function test_get_ip_reports_requires_monitor_id(): void
    {
        $this->expectException(MailerSendAssertException::class);

        $this->dmarcMonitoring->getIpReports('', '1.2.3.4');
    }

    public function test_get_ip_reports_requires_ip(): void
    {
        $this->expectException(MailerSendAssertException::class);

        $this->dmarcMonitoring->getIpReports('monitor-id-123', '');
    }

    /**
     * @dataProvider invalidLimitDataProvider
     */
    #[DataProvider('invalidLimitDataProvider')]
    public function test_get_ip_reports_with_invalid_limit(int $limit): void
    {
        $this->expectException(MailerSendAssertException::class);

        $this->dmarcMonitoring->getIpReports('monitor-id-123', '1.2.3.4', null, $limit);
    }

    // -------------------------------------------------------------------------
    // getReportSources
    // -------------------------------------------------------------------------

    public function test_get_report_sources(): void
    {
        $response = $this->createStub(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $this->client->addResponse($response);

        $monitorId = 'monitor-id-123';
        $response = $this->dmarcMonitoring->getReportSources($monitorId, '2024-01-01', '2024-01-31');

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);

        self::assertEquals('GET', $request->getMethod());
        self::assertEquals("/v1/dmarc-monitoring/$monitorId/report-sources", $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);
        self::assertEquals('2024-01-01', Arr::get($query, 'date_from'));
        self::assertEquals('2024-01-31', Arr::get($query, 'date_to'));
        self::assertNull(Arr::get($query, 'status'));
    }

    public function test_get_report_sources_with_status(): void
    {
        $response = $this->createStub(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $this->client->addResponse($response);

        $monitorId = 'monitor-id-123';
        $this->dmarcMonitoring->getReportSources($monitorId, '2024-01-01', '2024-01-31', 'accepted');

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);

        self::assertEquals('accepted', Arr::get($query, 'status'));
    }

    public function test_get_report_sources_requires_monitor_id(): void
    {
        $this->expectException(MailerSendAssertException::class);

        $this->dmarcMonitoring->getReportSources('', '2024-01-01', '2024-01-31');
    }

    public function test_get_report_sources_requires_date_from(): void
    {
        $this->expectException(MailerSendAssertException::class);

        $this->dmarcMonitoring->getReportSources('monitor-id-123', '', '2024-01-31');
    }

    public function test_get_report_sources_requires_date_to(): void
    {
        $this->expectException(MailerSendAssertException::class);

        $this->dmarcMonitoring->getReportSources('monitor-id-123', '2024-01-01', '');
    }

    public function test_get_report_sources_with_invalid_status(): void
    {
        $this->expectException(MailerSendAssertException::class);

        $this->dmarcMonitoring->getReportSources('monitor-id-123', '2024-01-01', '2024-01-31', 'invalid_status');
    }

    // -------------------------------------------------------------------------
    // markIpAsFavorite
    // -------------------------------------------------------------------------

    public function test_mark_ip_as_favorite(): void
    {
        $response = $this->createStub(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $this->client->addResponse($response);

        $monitorId = 'monitor-id-123';
        $ip = '1.2.3.4';
        $this->dmarcMonitoring->markIpAsFavorite($monitorId, $ip);

        $request = $this->client->getLastRequest();

        self::assertEquals('PUT', $request->getMethod());
        self::assertEquals("/v1/dmarc-monitoring/$monitorId/favorite/$ip", $request->getUri()->getPath());
    }

    public function test_mark_ip_as_favorite_requires_monitor_id(): void
    {
        $this->expectException(MailerSendAssertException::class);

        $this->dmarcMonitoring->markIpAsFavorite('', '1.2.3.4');
    }

    public function test_mark_ip_as_favorite_requires_ip(): void
    {
        $this->expectException(MailerSendAssertException::class);

        $this->dmarcMonitoring->markIpAsFavorite('monitor-id-123', '');
    }

    // -------------------------------------------------------------------------
    // removeIpFromFavorites
    // -------------------------------------------------------------------------

    public function test_remove_ip_from_favorites(): void
    {
        $response = $this->createStub(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $this->client->addResponse($response);

        $monitorId = 'monitor-id-123';
        $ip = '1.2.3.4';
        $this->dmarcMonitoring->removeIpFromFavorites($monitorId, $ip);

        $request = $this->client->getLastRequest();

        self::assertEquals('DELETE', $request->getMethod());
        self::assertEquals("/v1/dmarc-monitoring/$monitorId/favorite/$ip", $request->getUri()->getPath());
    }

    public function test_remove_ip_from_favorites_requires_monitor_id(): void
    {
        $this->expectException(MailerSendAssertException::class);

        $this->dmarcMonitoring->removeIpFromFavorites('', '1.2.3.4');
    }

    public function test_remove_ip_from_favorites_requires_ip(): void
    {
        $this->expectException(MailerSendAssertException::class);

        $this->dmarcMonitoring->removeIpFromFavorites('monitor-id-123', '');
    }

    // -------------------------------------------------------------------------
    // Shared data providers
    // -------------------------------------------------------------------------

    public static function validPaginatedWithMonitorIdDataProvider(): array
    {
        return [
            'empty request' => [
                [],
                ['page' => null, 'limit' => null],
            ],
            'with page' => [
                ['page' => 1],
                ['page' => '1', 'limit' => null],
            ],
            'with limit' => [
                ['limit' => 10],
                ['page' => null, 'limit' => '10'],
            ],
        ];
    }

    public static function invalidLimitDataProvider(): array
    {
        return [
            'limit too low' => [9],
            'limit too high' => [101],
        ];
    }
}
