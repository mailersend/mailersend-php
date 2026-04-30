<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use MailerSend\Common\Constants;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\BlocklistMonitoring;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Arr;
use MailerSend\Helpers\Builder\BlocklistMonitoringParams;
use MailerSend\Helpers\Builder\BlocklistMonitoringUpdateParams;
use MailerSend\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Http\Message\ResponseInterface;

class BlocklistMonitoringTest extends TestCase
{
    protected BlocklistMonitoring $blocklistMonitoring;
    protected ResponseInterface $defaultResponse;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = new Client();
        $this->blocklistMonitoring = new BlocklistMonitoring(new HttpLayer(self::OPTIONS, $this->client), self::OPTIONS);

        $this->defaultResponse = $this->createStub(ResponseInterface::class);
        $this->defaultResponse->method('getStatusCode')->willReturn(200);
    }

    /**
     * @dataProvider validGetAllDataProvider
     */
    #[DataProvider('validGetAllDataProvider')]
    public function test_get_all(array $params, array $expected): void
    {
        $response = $this->createStub(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $this->client->addResponse($response);

        $response = $this->blocklistMonitoring->getAll(
            $params['page'] ?? null,
            $params['limit'] ?? null,
            $params['query'] ?? null,
            $params['sort_by'] ?? null,
            $params['order'] ?? null
        );

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);

        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/v1/blocklist-monitoring', $request->getUri()->getPath());
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

        $this->blocklistMonitoring->getAll(
            $params['page'] ?? null,
            $params['limit'] ?? null,
            $params['query'] ?? null,
            $params['sort_by'] ?? null,
            $params['order'] ?? null
        );
    }

    public function test_find(): void
    {
        $this->client->addResponse($this->defaultResponse);

        $response = $this->blocklistMonitoring->find('monitor-id');

        $request = $this->client->getLastRequest();

        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/v1/blocklist-monitoring/monitor-id', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);
    }

    public function test_find_requires_monitor_id(): void
    {
        $this->expectException(MailerSendAssertException::class);

        $this->blocklistMonitoring->find('');
    }

    public function test_create(): void
    {
        $this->client->addResponse($this->defaultResponse);

        $params = (new BlocklistMonitoringParams('example.com'))
            ->setName('My Monitor')
            ->setNotify(true)
            ->setNotifyEmail('notify@example.com')
            ->setNotifyAddress('127.0.0.1');

        $response = $this->blocklistMonitoring->create($params);

        $request = $this->client->getLastRequest();
        $body = json_decode((string) $request->getBody(), true, 512, JSON_THROW_ON_ERROR);

        self::assertEquals('POST', $request->getMethod());
        self::assertEquals('/v1/blocklist-monitoring', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);
        self::assertSame('example.com', Arr::get($body, 'address'));
        self::assertSame('My Monitor', Arr::get($body, 'name'));
        self::assertTrue(Arr::get($body, 'notify'));
        self::assertSame('notify@example.com', Arr::get($body, 'notify_email'));
        self::assertSame('127.0.0.1', Arr::get($body, 'notify_address'));
    }

    public function test_create_requires_address(): void
    {
        $this->expectException(MailerSendAssertException::class);

        $params = (new BlocklistMonitoringParams('')); // empty address triggers validation in create()
        $this->blocklistMonitoring->create($params);
    }

    public function test_update(): void
    {
        $this->client->addResponse($this->defaultResponse);

        $params = (new BlocklistMonitoringUpdateParams())
            ->setName('Updated Monitor')
            ->setNotify(false)
            ->setNotifyEmail('new@example.com');

        $response = $this->blocklistMonitoring->update('monitor-id', $params);

        $request = $this->client->getLastRequest();
        $body = json_decode((string) $request->getBody(), true, 512, JSON_THROW_ON_ERROR);

        self::assertEquals('PUT', $request->getMethod());
        self::assertEquals('/v1/blocklist-monitoring/monitor-id', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);
        self::assertSame('Updated Monitor', Arr::get($body, 'name'));
        self::assertFalse(Arr::get($body, 'notify'));
        self::assertSame('new@example.com', Arr::get($body, 'notify_email'));
    }

    public function test_update_requires_monitor_id(): void
    {
        $this->expectException(MailerSendAssertException::class);

        $this->blocklistMonitoring->update('', new BlocklistMonitoringUpdateParams());
    }

    public function test_delete(): void
    {
        $this->client->addResponse($this->defaultResponse);

        $response = $this->blocklistMonitoring->delete('monitor-id');

        $request = $this->client->getLastRequest();

        self::assertEquals('DELETE', $request->getMethod());
        self::assertEquals('/v1/blocklist-monitoring/monitor-id', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);
    }

    public function test_delete_requires_monitor_id(): void
    {
        $this->expectException(MailerSendAssertException::class);

        $this->blocklistMonitoring->delete('');
    }

    public static function validGetAllDataProvider(): array
    {
        return [
            'empty request' => [
                'params' => [],
                'expected' => [],
            ],
            'with page' => [
                'params' => ['page' => 2],
                'expected' => ['page' => '2'],
            ],
            'with limit' => [
                'params' => ['limit' => 25],
                'expected' => ['limit' => '25'],
            ],
            'with query' => [
                'params' => ['query' => 'example.com'],
                'expected' => ['query' => 'example.com'],
            ],
            'with sort_by' => [
                'params' => ['sort_by' => 'name'],
                'expected' => ['sort_by' => 'name'],
            ],
            'with order' => [
                'params' => ['order' => 'asc'],
                'expected' => ['order' => 'asc'],
            ],
            'complete request' => [
                'params' => [
                    'page' => 1,
                    'limit' => 10,
                    'query' => 'domain.com',
                    'sort_by' => 'created_at',
                    'order' => 'desc',
                ],
                'expected' => [
                    'page' => '1',
                    'limit' => '10',
                    'query' => 'domain.com',
                    'sort_by' => 'created_at',
                    'order' => 'desc',
                ],
            ],
        ];
    }

    public static function invalidGetAllDataProvider(): array
    {
        return [
            'limit below minimum' => [
                'params' => ['limit' => Constants::MIN_LIMIT - 1],
            ],
            'limit above maximum' => [
                'params' => ['limit' => Constants::MAX_LIMIT + 1],
            ],
            'invalid sort_by value' => [
                'params' => ['sort_by' => 'invalid_field'],
            ],
            'invalid order value' => [
                'params' => ['order' => 'random'],
            ],
        ];
    }
}
