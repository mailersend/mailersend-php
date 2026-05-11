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

class DmarcMonitoringTest extends TestCase
{
    protected DmarcMonitoring $dmarcMonitoring;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = new Client();
        $this->dmarcMonitoring = new DmarcMonitoring(new HttpLayer(self::OPTIONS, $this->client), self::OPTIONS);
    }

    /**
     * @dataProvider validGetAllDataProvider
     */
    #[DataProvider('validGetAllDataProvider')]
    public function test_get_all(array $params, array $expected): void
    {
        $this->addSuccessResponse(200);

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

    public function test_get_all_excludes_unset_optional_params(): void
    {
        $this->addSuccessResponse(200);

        $this->dmarcMonitoring->getAll();

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);

        self::assertArrayNotHasKey('page', $query);
        self::assertArrayNotHasKey('query', $query);
        self::assertArrayNotHasKey('sort_by', $query);
        self::assertArrayNotHasKey('order', $query);
    }

    /**
     * @dataProvider invalidGetAllDataProvider
     * @param int|string|null $page
     * @param int|string|null $limit
     * @param string|null $query
     * @param string|null $sortBy
     * @param string|null $order
     */
    #[DataProvider('invalidGetAllDataProvider')]
    public function test_get_all_rejects_invalid_params(
        $page,
        $limit,
        $query,
        $sortBy,
        $order,
        string $message
    ): void {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($message);

        $this->dmarcMonitoring->getAll($page, $limit, $query, $sortBy, $order);
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
            'with sort_by created_at' => [
                ['sort_by' => 'created_at'],
                ['sort_by' => 'created_at'],
            ],
            'with sort_by updated_at' => [
                ['sort_by' => 'updated_at'],
                ['sort_by' => 'updated_at'],
            ],
            'with sort_by dmarc_valid' => [
                ['sort_by' => 'dmarc_valid'],
                ['sort_by' => 'dmarc_valid'],
            ],
            'with sort_by spf_status' => [
                ['sort_by' => 'spf_status'],
                ['sort_by' => 'spf_status'],
            ],
            'with order asc' => [
                ['order' => 'asc'],
                ['order' => 'asc'],
            ],
            'with order desc' => [
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
            'limit too low'              => [null, 9,   null, null,      null,  'Limit is supposed to be between 10 and 100.'],
            'limit too high'             => [null, 101, null, null,      null,  'Limit is supposed to be between 10 and 100.'],
            'invalid sort_by'            => [null, null, null, 'invalid_field', null, 'sort_by must be one of: created_at, updated_at, dmarc_valid, spf_status.'],
            'invalid order'              => [null, null, null, null,      'random', 'order must be asc or desc.'],
            'query exceeds 255 chars'    => [null, null, str_repeat('a', 256), null, null, 'Query may not be greater than 255 characters.'],
            'page below minimum'         => [0,    null, null, null,      null,  'Page must be at least 1.'],
        ];
    }

    public function test_create(): void
    {
        $this->addSuccessResponse(201);

        $response = $this->dmarcMonitoring->create(new DmarcMonitoringParams('domain-id-123'));

        $body = $this->assertRequest('POST', '/v1/dmarc-monitoring');

        self::assertEquals(201, $response['status_code']);
        $this->assertBodyContains(['domain_id' => 'domain-id-123'], $body);
    }

    public function test_create_requires_domain_id(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Domain id is required.');

        $this->dmarcMonitoring->create(new DmarcMonitoringParams(''));
    }

    public function test_update(): void
    {
        $this->addSuccessResponse(200);

        $monitorId = 'monitor-id-123';
        $response = $this->dmarcMonitoring->update($monitorId, new DmarcMonitoringUpdateParams('v=DMARC1; p=reject;'));

        $body = $this->assertRequest('PUT', "/v1/dmarc-monitoring/$monitorId");

        self::assertEquals(200, $response['status_code']);
        $this->assertBodyContains(['wanted_dmarc_record' => 'v=DMARC1; p=reject;'], $body);
    }

    public function test_update_requires_monitor_id(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Monitor id is required.');

        $this->dmarcMonitoring->update('', new DmarcMonitoringUpdateParams('v=DMARC1; p=reject;'));
    }

    public function test_update_requires_wanted_dmarc_record(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Wanted DMARC record is required.');

        $this->dmarcMonitoring->update('monitor-id-123', new DmarcMonitoringUpdateParams(''));
    }

    /**
     * @dataProvider invalidUpdateDataProvider
     */
    #[DataProvider('invalidUpdateDataProvider')]
    public function test_update_rejects_invalid_params(string $wantedDmarcRecord, string $message): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($message);

        $this->dmarcMonitoring->update('monitor-id-123', new DmarcMonitoringUpdateParams($wantedDmarcRecord));
    }

    public static function invalidUpdateDataProvider(): array
    {
        return [
            'wanted_dmarc_record exceeds 1000 chars' => [str_repeat('a', 1001), 'Wanted DMARC record may not be greater than 1000 characters.'],
        ];
    }

    public function test_delete(): void
    {
        $this->addSuccessResponse(204);

        $monitorId = 'monitor-id-123';
        $this->dmarcMonitoring->delete($monitorId);

        $this->assertRequest('DELETE', "/v1/dmarc-monitoring/$monitorId");
    }

    public function test_delete_forwards_status_code(): void
    {
        $this->addSuccessResponse(204);
        $response = $this->dmarcMonitoring->delete('monitor-id-123');
        self::assertEquals(204, $response['status_code']);
    }

    public function test_delete_requires_monitor_id(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Monitor id is required.');

        $this->dmarcMonitoring->delete('');
    }

    /**
     * @dataProvider validAggregatedReportsDataProvider
     */
    #[DataProvider('validAggregatedReportsDataProvider')]
    public function test_get_aggregated_reports(array $params, array $expected): void
    {
        $this->addSuccessResponse(200);

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

        foreach ($expected as $key => $value) {
            self::assertEquals($value, Arr::get($query, $key), "Query param '$key' mismatch.");
        }
    }

    public function test_get_aggregated_reports_excludes_unset_optional_params(): void
    {
        $this->addSuccessResponse(200);

        $this->dmarcMonitoring->getAggregatedReports('monitor-id-123');

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);

        self::assertArrayNotHasKey('page', $query);
        self::assertArrayNotHasKey('date_from', $query);
        self::assertArrayNotHasKey('date_to', $query);
        self::assertArrayNotHasKey('search', $query);
        self::assertArrayNotHasKey('category', $query);
        self::assertArrayNotHasKey('report_source', $query);
    }

    public function test_get_aggregated_reports_requires_monitor_id(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Monitor id is required.');

        $this->dmarcMonitoring->getAggregatedReports('');
    }

    /**
     * @dataProvider invalidLimitDataProvider
     */
    #[DataProvider('invalidLimitDataProvider')]
    public function test_get_aggregated_reports_rejects_invalid_limit(int $limit, string $message): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($message);

        $this->dmarcMonitoring->getAggregatedReports('monitor-id-123', null, $limit);
    }

    /**
     * @dataProvider invalidGetAggregatedReportsDataProvider
     */
    #[DataProvider('invalidGetAggregatedReportsDataProvider')]
    public function test_get_aggregated_reports_rejects_invalid_params(
        ?int $page,
        ?string $search,
        ?string $reportSource,
        string $message
    ): void {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($message);

        $this->dmarcMonitoring->getAggregatedReports('monitor-id-123', $page, null, null, null, $search, null, $reportSource);
    }

    public static function invalidGetAggregatedReportsDataProvider(): array
    {
        return [
            'search exceeds 255 chars'         => [null, str_repeat('a', 256), null,                   'Search may not be greater than 255 characters.'],
            'report_source exceeds 255 chars'  => [null, null,                 str_repeat('a', 256),   'Report source may not be greater than 255 characters.'],
            'page below minimum'               => [0,   null,                  null,                   'Page must be at least 1.'],
        ];
    }

    public static function validAggregatedReportsDataProvider(): array
    {
        return [
            'empty request' => [
                [],
                ['page' => null, 'limit' => null],
            ],
            'with page' => [
                ['page' => 1],
                ['page' => '1'],
            ],
            'with limit' => [
                ['limit' => 10],
                ['limit' => '10'],
            ],
            'with date_from' => [
                ['date_from' => '2024-01-01'],
                ['date_from' => '2024-01-01'],
            ],
            'with date_to' => [
                ['date_to' => '2024-01-31'],
                ['date_to' => '2024-01-31'],
            ],
            'with search' => [
                ['search' => 'example.com'],
                ['search' => 'example.com'],
            ],
            'with category' => [
                ['category' => 'dmarc'],
                ['category' => 'dmarc'],
            ],
            'with report_source' => [
                ['report_source' => 'google.com'],
                ['report_source' => 'google.com'],
            ],
            'with all params' => [
                [
                    'page'          => 1,
                    'limit'         => 25,
                    'date_from'     => '2024-01-01',
                    'date_to'       => '2024-01-31',
                    'search'        => 'example.com',
                    'category'      => 'dmarc',
                    'report_source' => 'google.com',
                ],
                [
                    'page'          => '1',
                    'limit'         => '25',
                    'date_from'     => '2024-01-01',
                    'date_to'       => '2024-01-31',
                    'search'        => 'example.com',
                    'category'      => 'dmarc',
                    'report_source' => 'google.com',
                ],
            ],
        ];
    }

    /**
     * @dataProvider validIpReportsDataProvider
     */
    #[DataProvider('validIpReportsDataProvider')]
    public function test_get_ip_reports(array $params, array $expected): void
    {
        $this->addSuccessResponse(200);

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

        foreach ($expected as $key => $value) {
            self::assertEquals($value, Arr::get($query, $key), "Query param '$key' mismatch.");
        }
    }

    public function test_get_ip_reports_excludes_unset_optional_params(): void
    {
        $this->addSuccessResponse(200);

        $this->dmarcMonitoring->getIpReports('monitor-id-123', '1.2.3.4');

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);

        self::assertArrayNotHasKey('page', $query);
        self::assertArrayNotHasKey('date_from', $query);
        self::assertArrayNotHasKey('date_to', $query);
        self::assertArrayNotHasKey('search', $query);
        self::assertArrayNotHasKey('category', $query);
        self::assertArrayNotHasKey('report_source', $query);
    }

    public function test_get_ip_reports_requires_monitor_id(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Monitor id is required.');

        $this->dmarcMonitoring->getIpReports('', '1.2.3.4');
    }

    public function test_get_ip_reports_requires_ip(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('IP address is required.');

        $this->dmarcMonitoring->getIpReports('monitor-id-123', '');
    }

    /**
     * @dataProvider invalidLimitDataProvider
     */
    #[DataProvider('invalidLimitDataProvider')]
    public function test_get_ip_reports_rejects_invalid_limit(int $limit, string $message): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($message);

        $this->dmarcMonitoring->getIpReports('monitor-id-123', '1.2.3.4', null, $limit);
    }

    /**
     * @dataProvider invalidGetIpReportsDataProvider
     */
    #[DataProvider('invalidGetIpReportsDataProvider')]
    public function test_get_ip_reports_rejects_invalid_params(
        ?int $page,
        ?string $search,
        ?string $reportSource,
        string $message
    ): void {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($message);

        $this->dmarcMonitoring->getIpReports('monitor-id-123', '1.2.3.4', $page, null, null, null, $search, null, $reportSource);
    }

    public static function invalidGetIpReportsDataProvider(): array
    {
        return [
            'search exceeds 255 chars'         => [null, str_repeat('a', 256), null,                   'Search may not be greater than 255 characters.'],
            'report_source exceeds 255 chars'  => [null, null,                 str_repeat('a', 256),   'Report source may not be greater than 255 characters.'],
            'page below minimum'               => [0,   null,                  null,                   'Page must be at least 1.'],
        ];
    }

    public static function validIpReportsDataProvider(): array
    {
        return [
            'empty request' => [
                [],
                ['page' => null, 'limit' => null],
            ],
            'with page' => [
                ['page' => 1],
                ['page' => '1'],
            ],
            'with limit' => [
                ['limit' => 10],
                ['limit' => '10'],
            ],
            'with date_from' => [
                ['date_from' => '2024-01-01'],
                ['date_from' => '2024-01-01'],
            ],
            'with date_to' => [
                ['date_to' => '2024-01-31'],
                ['date_to' => '2024-01-31'],
            ],
            'with search' => [
                ['search' => 'example.com'],
                ['search' => 'example.com'],
            ],
            'with category' => [
                ['category' => 'dmarc'],
                ['category' => 'dmarc'],
            ],
            'with report_source' => [
                ['report_source' => 'google.com'],
                ['report_source' => 'google.com'],
            ],
            'with all params' => [
                [
                    'page'          => 1,
                    'limit'         => 25,
                    'date_from'     => '2024-01-01',
                    'date_to'       => '2024-01-31',
                    'search'        => 'example.com',
                    'category'      => 'dmarc',
                    'report_source' => 'google.com',
                ],
                [
                    'page'          => '1',
                    'limit'         => '25',
                    'date_from'     => '2024-01-01',
                    'date_to'       => '2024-01-31',
                    'search'        => 'example.com',
                    'category'      => 'dmarc',
                    'report_source' => 'google.com',
                ],
            ],
        ];
    }

    public function test_get_report_sources(): void
    {
        $this->addSuccessResponse(200);

        $monitorId = 'monitor-id-123';
        $response = $this->dmarcMonitoring->getReportSources($monitorId, '2024-01-01', '2024-01-31');

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);

        self::assertEquals('GET', $request->getMethod());
        self::assertEquals("/v1/dmarc-monitoring/$monitorId/report-sources", $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);
        self::assertEquals('2024-01-01', Arr::get($query, 'date_from'));
        self::assertEquals('2024-01-31', Arr::get($query, 'date_to'));
        self::assertArrayNotHasKey('status', $query);
    }

    public function test_get_report_sources_with_status(): void
    {
        $this->addSuccessResponse(200);

        $monitorId = 'monitor-id-123';
        $this->dmarcMonitoring->getReportSources($monitorId, '2024-01-01', '2024-01-31', 'accepted');

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);

        self::assertEquals('accepted', Arr::get($query, 'status'));
    }

    /**
     * @dataProvider invalidReportSourcesDataProvider
     */
    #[DataProvider('invalidReportSourcesDataProvider')]
    public function test_get_report_sources_rejects_invalid_params(
        string $monitorId,
        string $dateFrom,
        string $dateTo,
        $status,
        string $message
    ): void {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($message);

        $this->dmarcMonitoring->getReportSources($monitorId, $dateFrom, $dateTo, $status);
    }

    public static function invalidReportSourcesDataProvider(): array
    {
        return [
            'missing monitor id' => [
                '', '2024-01-01', '2024-01-31', null,
                'Monitor id is required.',
            ],
            'missing date_from' => [
                'monitor-id-123', '', '2024-01-31', null,
                'date_from is required.',
            ],
            'missing date_to' => [
                'monitor-id-123', '2024-01-01', '', null,
                'date_to is required.',
            ],
            'invalid status' => [
                'monitor-id-123', '2024-01-01', '2024-01-31', 'invalid_status',
                'status must be one of: accepted, rejected, quarantined.',
            ],
        ];
    }

    public function test_mark_ip_as_favorite(): void
    {
        $this->addSuccessResponse(200);

        $monitorId = 'monitor-id-123';
        $ip = '1.2.3.4';
        $this->dmarcMonitoring->markIpAsFavorite($monitorId, $ip);

        $this->assertRequest('PUT', "/v1/dmarc-monitoring/$monitorId/favorite/$ip");
    }

    /**
     * @dataProvider invalidMonitorIdAndIpDataProvider
     */
    #[DataProvider('invalidMonitorIdAndIpDataProvider')]
    public function test_mark_ip_as_favorite_rejects_invalid_params(
        string $monitorId,
        string $ip,
        string $message
    ): void {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($message);

        $this->dmarcMonitoring->markIpAsFavorite($monitorId, $ip);
    }

    public function test_remove_ip_from_favorites(): void
    {
        $this->addSuccessResponse(200);

        $monitorId = 'monitor-id-123';
        $ip = '1.2.3.4';
        $this->dmarcMonitoring->removeIpFromFavorites($monitorId, $ip);

        $this->assertRequest('DELETE', "/v1/dmarc-monitoring/$monitorId/favorite/$ip");
    }

    /**
     * @dataProvider invalidMonitorIdAndIpDataProvider
     */
    #[DataProvider('invalidMonitorIdAndIpDataProvider')]
    public function test_remove_ip_from_favorites_rejects_invalid_params(
        string $monitorId,
        string $ip,
        string $message
    ): void {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($message);

        $this->dmarcMonitoring->removeIpFromFavorites($monitorId, $ip);
    }

    public static function invalidLimitDataProvider(): array
    {
        return [
            'limit too low'  => [9,   'Limit is supposed to be between 10 and 100.'],
            'limit too high' => [101, 'Limit is supposed to be between 10 and 100.'],
        ];
    }

    public static function invalidMonitorIdAndIpDataProvider(): array
    {
        return [
            'missing monitor id' => ['',               '1.2.3.4', 'Monitor id is required.'],
            'missing ip'         => ['monitor-id-123', '',        'IP address is required.'],
        ];
    }
}
