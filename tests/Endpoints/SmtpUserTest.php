<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use Illuminate\Support\Arr;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\SmtpUser;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Tests\TestCase;
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

        $this->defaultResponse = $this->createMock(ResponseInterface::class);
        $this->defaultResponse->method('getStatusCode')->willReturn(200);
    }

    /**
     * @dataProvider validSmtpUserRoutingListDataProvider
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_get_all(array $params, array $expected): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $this->client->addResponse($response);

        $response = $this->smtpUser->getAll(
            Arr::get($params, 'domain_id'),
            Arr::get($params, 'limit'),
        );

        $request = $this->client->getLastRequest();

        parse_str($request->getUri()->getQuery(), $query);
        $domainId = Arr::get($params, 'domain_id');

        self::assertEquals('GET', $request->getMethod());
        self::assertEquals("/v1/domains/$domainId/smtp-users", $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);

        self::assertEquals(Arr::get($expected, 'limit'), Arr::get($query, 'limit'));
    }

    /**
     * @dataProvider invalidSmtpUserRoutingListDataProvider
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_get_all_with_errors(array $params): void
    {
        $this->expectException(MailerSendAssertException::class);

        $this->smtpUser->getAll(
            Arr::get($params, 'domain_id'),
            Arr::get($params, 'limit'),
        );
    }

    /**
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_find_requires_smtp_user_id(): void
    {
        $this->expectException(MailerSendAssertException::class);

        $this->smtpUser->find('domainId', '');
    }

    /**
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_create(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $this->client->addResponse($response);
        $domainId = 'domainId';
        $response = $this->smtpUser->create(
            $domainId,
            (new \MailerSend\Helpers\Builder\SmtpUserParams('Test Smtp'))
        );

        $request = $this->client->getLastRequest();
        $request_body = json_decode((string)$request->getBody(), true, 512, JSON_THROW_ON_ERROR);

        self::assertEquals('POST', $request->getMethod());
        self::assertEquals("/v1/domains/$domainId/smtp-users", $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);
        self::assertSame('Test Smtp', Arr::get($request_body, 'name'));
        self::assertSame(true, Arr::get($request_body, 'enabled'));
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

        $params = (new \MailerSend\Helpers\Builder\SmtpUserParams('Test Smtp New'))
                        ->setEnabled(true);

        $response = $this->smtpUser->update(
            'domainId',
            'smtpUserId',
            $params,
        );

        $request = $this->client->getLastRequest();
        $request_body = json_decode((string)$request->getBody(), true, 512, JSON_THROW_ON_ERROR);

        self::assertEquals('PUT', $request->getMethod());
        self::assertEquals('/v1/domains/domainId/smtp-users/smtpUserId', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);
        self::assertSame('Test Smtp New', Arr::get($request_body, 'name'));
        self::assertTrue(Arr::get($request_body, 'enabled'));
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

        $response = $this->smtpUser->delete('domainId', 'smtpUserId');

        $request = $this->client->getLastRequest();

        self::assertEquals('DELETE', $request->getMethod());
        self::assertEquals('/v1/domains/domainId/smtp-users/smtpUserId', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function test_delete_required_smtpUserId(): void
    {
        $this->expectException(MailerSendAssertException::class);

        $this->smtpUser->delete('domainId', '');
    }

    public function validSmtpUserRoutingListDataProvider(): array
    {
        return [
            'empty request' => [
                'params' => [],
                'expected' => [
                    'domain_id' => null,
                    'limit' => null,
                ],
            ],
            'with domain id' => [
                'params' => [
                    'domain_id' => 'domain_id',
                ],
                'expected' => [
                    'domain_id' => 'domain_id',
                    'limit' => null,
                ],
            ],
            'with limit' => [
                [
                    'limit' => 10,
                ],
                [
                    'domain_id' => null,
                    'limit' => 10,
                ],
            ],
            'complete request' => [
                [
                    'domain_id' => 'domain_id',
                    'limit' => 10,
                ],
                [
                    'domain_id' => 'domain_id',
                    'limit' => 10,
                ],
            ],
        ];
    }

    public function invalidSmtpUserRoutingListDataProvider(): array
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
