<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use MailerSend\Helpers\Arr;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\SmtpUser;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Builder\SmtpUserParams;
use MailerSend\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Http\Message\ResponseInterface;

class SmtpUserTest extends TestCase
{
    protected SmtpUser $smtpUser;
    protected ResponseInterface $defaultResponse;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = new Client();

        $this->smtpUser = new SmtpUser(new HttpLayer(self::OPTIONS, $this->client), self::OPTIONS);

        $this->defaultResponse = $this->createStub(ResponseInterface::class);
        $this->defaultResponse->method('getStatusCode')->willReturn(200);
    }

    /**
     * @dataProvider validSmtpUserRoutingListDataProvider
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    #[DataProvider('validSmtpUserRoutingListDataProvider')]
    public function test_get_all(array $params, array $expected): void
    {
        $response = $this->createStub(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $this->client->addResponse($response);

        $response = $this->smtpUser->getAll(
            'domain_id',
            Arr::get($params, 'limit'),
            Arr::get($params, 'page'),
        );

        $request = $this->client->getLastRequest();

        parse_str($request->getUri()->getQuery(), $query);

        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/v1/domains/domain_id/smtp-users', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);

        self::assertEquals(Arr::get($expected, 'limit'), Arr::get($query, 'limit'));
        self::assertEquals(Arr::get($expected, 'page'), Arr::get($query, 'page'));
    }

    public function test_get_all_sends_default_limit(): void
    {
        $this->addSuccessResponse();

        $this->smtpUser->getAll('domain_id');

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);

        $this->assertQueryParams(['limit' => '25'], $query);
    }

    /**
     * @dataProvider invalidSmtpUserRoutingListDataProvider
     * @param array $params
     * @param string $message
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    #[DataProvider('invalidSmtpUserRoutingListDataProvider')]
    public function test_get_all_with_errors(array $params, string $message): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($message);

        $this->smtpUser->getAll(
            Arr::get($params, 'domain_id'),
            Arr::get($params, 'limit'),
            Arr::get($params, 'page'),
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

        $response = $this->smtpUser->find('domainId', 'smtpUserId');

        $body = $this->assertRequest('GET', '/v1/domains/domainId/smtp-users/smtpUserId');

        self::assertEquals(200, $response['status_code']);
    }

    /**
     * @dataProvider missingIdsDataProvider
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    #[DataProvider('missingIdsDataProvider')]
    public function test_find_rejects_missing_ids(string $domainId, string $smtpUserId, string $message): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($message);

        $this->smtpUser->find($domainId, $smtpUserId);
    }

    /**
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_create(): void
    {
        $this->addSuccessResponse();

        $domainId = 'domainId';
        $response = $this->smtpUser->create(
            $domainId,
            new SmtpUserParams('Test Smtp')
        );

        $body = $this->assertRequest('POST', "/v1/domains/$domainId/smtp-users");

        self::assertEquals(200, $response['status_code']);
        self::assertSame('Test Smtp', Arr::get($body, 'name'));
        $this->assertBodyExcludes(['enabled'], $body);
    }

    /**
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_create_with_enabled_false(): void
    {
        $this->addSuccessResponse();

        $domainId = 'domainId';
        $response = $this->smtpUser->create(
            $domainId,
            (new SmtpUserParams('Test Smtp'))->setEnabled(false)
        );

        $body = $this->assertRequest('POST', "/v1/domains/$domainId/smtp-users");

        self::assertEquals(200, $response['status_code']);
        self::assertSame(false, Arr::get($body, 'enabled'));
    }

    /**
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws MailerSendAssertException
     */
    public function test_create_requires_domain_id(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Domain id is required.');

        $this->smtpUser->create('', new SmtpUserParams('Test Smtp'));
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function test_update(): void
    {
        $this->addSuccessResponse();

        $params = (new SmtpUserParams('Test Smtp New'))->setEnabled(true);

        $response = $this->smtpUser->update('domainId', 'smtpUserId', $params);

        $body = $this->assertRequest('PUT', '/v1/domains/domainId/smtp-users/smtpUserId');

        self::assertEquals(200, $response['status_code']);
        self::assertSame('Test Smtp New', Arr::get($body, 'name'));
        self::assertTrue(Arr::get($body, 'enabled'));
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function test_update_with_enabled_false(): void
    {
        $this->addSuccessResponse();

        $params = (new SmtpUserParams('Test Smtp'))->setEnabled(false);

        $response = $this->smtpUser->update('domainId', 'smtpUserId', $params);

        $body = $this->assertRequest('PUT', '/v1/domains/domainId/smtp-users/smtpUserId');

        self::assertEquals(200, $response['status_code']);
        self::assertSame(false, Arr::get($body, 'enabled'));
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function test_update_without_name_excludes_it(): void
    {
        $this->addSuccessResponse();

        $this->smtpUser->update('domainId', 'smtpUserId', (new SmtpUserParams())->setEnabled(false));

        $body = $this->assertRequest('PUT', '/v1/domains/domainId/smtp-users/smtpUserId');

        self::assertSame(false, Arr::get($body, 'enabled'));
        $this->assertBodyExcludes(['name'], $body);
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function test_update_without_enabled_excludes_it(): void
    {
        $this->addSuccessResponse();

        $this->smtpUser->update('domainId', 'smtpUserId', new SmtpUserParams('Renamed Smtp'));

        $body = $this->assertRequest('PUT', '/v1/domains/domainId/smtp-users/smtpUserId');

        self::assertSame('Renamed Smtp', Arr::get($body, 'name'));
        $this->assertBodyExcludes(['enabled'], $body);
    }

    /**
     * @dataProvider missingIdsDataProvider
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws MailerSendAssertException
     * @throws \JsonException
     */
    #[DataProvider('missingIdsDataProvider')]
    public function test_update_rejects_missing_ids(string $domainId, string $smtpUserId, string $message): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($message);

        $this->smtpUser->update($domainId, $smtpUserId, new SmtpUserParams('Test'));
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws MailerSendAssertException
     * @throws \JsonException
     */
    public function test_delete(): void
    {
        $this->addSuccessResponse();

        $response = $this->smtpUser->delete('domainId', 'smtpUserId');

        $this->assertRequest('DELETE', '/v1/domains/domainId/smtp-users/smtpUserId');

        self::assertEquals(200, $response['status_code']);
    }

    /**
     * @dataProvider missingIdsDataProvider
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    #[DataProvider('missingIdsDataProvider')]
    public function test_delete_rejects_missing_ids(string $domainId, string $smtpUserId, string $message): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($message);

        $this->smtpUser->delete($domainId, $smtpUserId);
    }

    public static function validSmtpUserRoutingListDataProvider(): array
    {
        return [
            'without limit and page' => [
                'params' => [],
                'expected' => [
                    'limit' => null,
                    'page' => null,
                ],
            ],
            'with limit' => [
                'params' => [
                    'limit' => 10,
                ],
                'expected' => [
                    'limit' => 10,
                    'page' => null,
                ],
            ],
            'with page' => [
                'params' => [
                    'page' => 2,
                ],
                'expected' => [
                    'limit' => null,
                    'page' => 2,
                ],
            ],
            'complete request' => [
                'params' => [
                    'limit' => 10,
                    'page' => 2,
                ],
                'expected' => [
                    'limit' => 10,
                    'page' => 2,
                ],
            ],
        ];
    }

    public static function invalidSmtpUserRoutingListDataProvider(): array
    {
        return [
            'without domain id' => [
                [],
                'Domain id is required.',
            ],
            'with empty domain id' => [
                ['domain_id' => ''],
                'Domain id is required.',
            ],
            'with limit under 10' => [
                ['domain_id' => 'domain_id', 'limit' => 9],
                'Limit is supposed to be between 10 and 100.',
            ],
            'with limit over 100' => [
                ['domain_id' => 'domain_id', 'limit' => 101],
                'Limit is supposed to be between 10 and 100.',
            ],
        ];
    }

    public static function missingIdsDataProvider(): array
    {
        return [
            'missing domain id' => ['', 'smtpUserId', 'Domain id is required.'],
            'missing smtp user id' => ['domainId', '', 'Smtp user id is required.'],
        ];
    }
}
