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

    public function setUp(): void
    {
        parent::setUp();

        $this->client = new Client();
        $this->blocklistMonitoring = new BlocklistMonitoring(new HttpLayer(self::OPTIONS, $this->client), self::OPTIONS);
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
    public function test_get_all_with_errors(array $params, string $errorMessage): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($errorMessage);

        $this->blocklistMonitoring->getAll(
            $params['page'] ?? null,
            $params['limit'] ?? null,
            $params['query'] ?? null,
            $params['sort_by'] ?? null,
            $params['order'] ?? null
        );
    }

    public function test_get_all_with_no_params_excludes_optional_query_params(): void
    {
        $this->addSuccessResponse();

        $this->blocklistMonitoring->getAll();

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);

        $this->assertQueryParams([], $query);
        self::assertArrayNotHasKey('page', $query);
        self::assertArrayNotHasKey('query', $query);
        self::assertArrayNotHasKey('sort_by', $query);
        self::assertArrayNotHasKey('order', $query);
    }

    public function test_find_uses_correct_method_and_path(): void
    {
        $this->addSuccessResponse();

        $this->blocklistMonitoring->find('monitor-id');

        $this->assertRequest('GET', '/v1/blocklist-monitoring/monitor-id');
    }

    public function test_find_forwards_status_code(): void
    {
        $this->addSuccessResponse();

        $response = $this->blocklistMonitoring->find('monitor-id');

        self::assertEquals(200, $response['status_code']);
    }

    public function test_find_requires_monitor_id(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Monitor id is required.');

        $this->blocklistMonitoring->find('');
    }

    public function test_create_uses_correct_method_and_path(): void
    {
        $this->addSuccessResponse();

        $this->blocklistMonitoring->create(new BlocklistMonitoringParams('example.com'));

        $this->assertRequest('POST', '/v1/blocklist-monitoring');
    }

    public function test_create_forwards_status_code(): void
    {
        $this->addSuccessResponse();

        $response = $this->blocklistMonitoring->create(new BlocklistMonitoringParams('example.com'));

        self::assertEquals(200, $response['status_code']);
    }

    public function test_create_sends_all_params(): void
    {
        $this->addSuccessResponse();

        $params = (new BlocklistMonitoringParams('example.com'))
            ->setName('My Monitor')
            ->setNotify(true)
            ->setNotifyEmail('notify@example.com')
            ->setNotifyAddress('https://hooks.example.com/notify');

        $this->blocklistMonitoring->create($params);

        $body = $this->assertRequest('POST', '/v1/blocklist-monitoring');

        $this->assertBodyContains([
            'address' => 'example.com',
            'name' => 'My Monitor',
            'notify' => true,
            'notify_email' => 'notify@example.com',
            'notify_address' => 'https://hooks.example.com/notify',
        ], $body);
    }

    public function test_create_with_only_address_excludes_optional_fields(): void
    {
        $this->addSuccessResponse();

        $this->blocklistMonitoring->create(new BlocklistMonitoringParams('example.com'));

        $body = $this->assertRequest('POST', '/v1/blocklist-monitoring');

        self::assertSame('example.com', $body['address']);
        $this->assertBodyExcludes(['name', 'notify', 'notify_email', 'notify_address'], $body);
    }

    public function test_create_requires_address(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Address is required.');

        $params = (new BlocklistMonitoringParams(''));
        $this->blocklistMonitoring->create($params);
    }

    public function test_update_uses_correct_method_and_path(): void
    {
        $this->addSuccessResponse();

        $this->blocklistMonitoring->update('monitor-id', new BlocklistMonitoringUpdateParams());

        $this->assertRequest('PUT', '/v1/blocklist-monitoring/monitor-id');
    }

    public function test_update_forwards_status_code(): void
    {
        $this->addSuccessResponse();

        $response = $this->blocklistMonitoring->update('monitor-id', new BlocklistMonitoringUpdateParams());

        self::assertEquals(200, $response['status_code']);
    }

    public function test_update_sends_all_params(): void
    {
        $this->addSuccessResponse();

        $params = (new BlocklistMonitoringUpdateParams())
            ->setName('Updated Monitor')
            ->setNotify(false)
            ->setNotifyEmail('new@example.com')
            ->setNotifyAddress('https://hooks.example.com/updated');

        $this->blocklistMonitoring->update('monitor-id', $params);

        $body = $this->assertRequest('PUT', '/v1/blocklist-monitoring/monitor-id');

        $this->assertBodyContains([
            'name' => 'Updated Monitor',
            'notify' => false,
            'notify_email' => 'new@example.com',
            'notify_address' => 'https://hooks.example.com/updated',
        ], $body);
    }

    public function test_update_with_no_params_sends_empty_body(): void
    {
        $this->addSuccessResponse();

        $this->blocklistMonitoring->update('monitor-id', new BlocklistMonitoringUpdateParams());

        $body = $this->assertRequest('PUT', '/v1/blocklist-monitoring/monitor-id');

        $this->assertBodyExcludes(['name', 'notify', 'notify_email', 'notify_address'], $body);
    }

    public function test_update_requires_monitor_id(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Monitor id is required.');

        $this->blocklistMonitoring->update('', new BlocklistMonitoringUpdateParams());
    }

    public function test_delete_uses_correct_method_and_path(): void
    {
        $this->addSuccessResponse();

        $this->blocklistMonitoring->delete('monitor-id');

        $this->assertRequest('DELETE', '/v1/blocklist-monitoring/monitor-id');
    }

    public function test_delete_forwards_status_code(): void
    {
        $this->addSuccessResponse();

        $response = $this->blocklistMonitoring->delete('monitor-id');

        self::assertEquals(200, $response['status_code']);
    }

    public function test_delete_requires_monitor_id(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Monitor id is required.');

        $this->blocklistMonitoring->delete('');
    }

    /**
     * @dataProvider invalidCreateDataProvider
     */
    #[DataProvider('invalidCreateDataProvider')]
    public function test_create_with_errors(BlocklistMonitoringParams $params, string $errorMessage): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($errorMessage);

        $this->blocklistMonitoring->create($params);
    }

    public function test_create_params_set_name_exceeds_255_chars(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Name may not be greater than 255 characters.');

        (new BlocklistMonitoringParams('example.com'))->setName(str_repeat('a', 256));
    }

    public function test_create_params_set_notify_email_invalid_format(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Notify email is invalid.');

        (new BlocklistMonitoringParams('example.com'))->setNotifyEmail('not-an-email');
    }

    public function test_create_params_set_notify_email_exceeds_255_chars(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Notify email may not be greater than 255 characters.');

        (new BlocklistMonitoringParams('example.com'))->setNotifyEmail(str_repeat('a', 247) . '@test.com');
    }

    public function test_create_params_set_notify_address_invalid_url(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Notify address must be a valid URL.');

        (new BlocklistMonitoringParams('example.com'))->setNotifyAddress('not-a-url');
    }

    public function test_create_params_set_notify_address_exceeds_500_chars(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Notify address may not be greater than 500 characters.');

        (new BlocklistMonitoringParams('example.com'))->setNotifyAddress('https://example.com/' . str_repeat('a', 490));
    }

    /**
     * @dataProvider invalidUpdateDataProvider
     */
    #[DataProvider('invalidUpdateDataProvider')]
    public function test_update_with_errors(BlocklistMonitoringUpdateParams $params, string $errorMessage): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($errorMessage);

        $this->blocklistMonitoring->update('monitor-id', $params);
    }

    public function test_update_params_set_name_exceeds_255_chars(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Name may not be greater than 255 characters.');

        (new BlocklistMonitoringUpdateParams())->setName(str_repeat('a', 256));
    }

    public function test_update_params_set_notify_email_invalid_format(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Notify email is invalid.');

        (new BlocklistMonitoringUpdateParams())->setNotifyEmail('not-an-email');
    }

    public function test_update_params_set_notify_email_exceeds_255_chars(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Notify email may not be greater than 255 characters.');

        (new BlocklistMonitoringUpdateParams())->setNotifyEmail(str_repeat('a', 247) . '@test.com');
    }

    public function test_update_params_set_notify_address_invalid_url(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Notify address must be a valid URL.');

        (new BlocklistMonitoringUpdateParams())->setNotifyAddress('not-a-url');
    }

    public function test_update_params_set_notify_address_exceeds_500_chars(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Notify address may not be greater than 500 characters.');

        (new BlocklistMonitoringUpdateParams())->setNotifyAddress('https://example.com/' . str_repeat('a', 490));
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
                'errorMessage' => 'Limit is supposed to be between ' . Constants::MIN_LIMIT . ' and ' . Constants::MAX_LIMIT . '.',
            ],
            'limit above maximum' => [
                'params' => ['limit' => Constants::MAX_LIMIT + 1],
                'errorMessage' => 'Limit is supposed to be between ' . Constants::MIN_LIMIT . ' and ' . Constants::MAX_LIMIT . '.',
            ],
            'invalid sort_by value' => [
                'params' => ['sort_by' => 'invalid_field'],
                'errorMessage' => 'sort_by must be one of: ' . implode(', ', BlocklistMonitoring::POSSIBLE_SORT_BY) . '.',
            ],
            'invalid order value' => [
                'params' => ['order' => 'random'],
                'errorMessage' => 'order must be asc or desc.',
            ],
            'query exceeds 255 chars' => [
                'params' => ['query' => str_repeat('a', 256)],
                'errorMessage' => 'Query may not be greater than 255 characters.',
            ],
            'page below minimum' => [
                'params' => ['page' => 0],
                'errorMessage' => 'Page must be at least 1.',
            ],
        ];
    }

    public static function invalidCreateDataProvider(): array
    {
        return [
            'address exceeds 255 chars' => [
                'params' => new BlocklistMonitoringParams(str_repeat('a', 256)),
                'errorMessage' => 'Address may not be greater than 255 characters.',
            ],
            'notify true without notify_email or notify_address' => [
                'params' => (new BlocklistMonitoringParams('example.com'))
                    ->setNotify(true),
                'errorMessage' => 'Notify email or notify address is required when notify is enabled.',
            ],
        ];
    }

    public static function invalidUpdateDataProvider(): array
    {
        return [
            'notify true without notify_email or notify_address' => [
                'params' => (new BlocklistMonitoringUpdateParams())
                    ->setNotify(true),
                'errorMessage' => 'Notify email or notify address is required when notify is enabled.',
            ],
        ];
    }
}
