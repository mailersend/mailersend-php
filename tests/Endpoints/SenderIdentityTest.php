<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use Illuminate\Support\Arr;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\SenderIdentity;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Builder\SenderIdentity as SenderIdentityBuilder;
use MailerSend\Tests\TestCase;
use Psr\Http\Message\ResponseInterface;

class SenderIdentityTest extends TestCase
{
    protected SenderIdentity $senderIdentityRouting;
    protected ResponseInterface $defaultResponse;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = new Client();

        $this->senderIdentityRouting = new SenderIdentity(new HttpLayer(self::OPTIONS, $this->client), self::OPTIONS);

        $this->defaultResponse = $this->createMock(ResponseInterface::class);
        $this->defaultResponse->method('getStatusCode')->willReturn(200);
    }

    /**
     * @dataProvider validSenderIdentityRoutingListDataProvider
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_get_all(array $params, array $expected): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $this->client->addResponse($response);

        $response = $this->senderIdentityRouting->getAll(
            Arr::get($params, 'domain_id'),
            Arr::get($params, 'page'),
            Arr::get($params, 'limit'),
        );

        $request = $this->client->getLastRequest();

        parse_str($request->getUri()->getQuery(), $query);

        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/v1/identities', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);

        self::assertEquals(Arr::get($expected, 'domain_id'), Arr::get($query, 'domain_id'));
        self::assertEquals(Arr::get($expected, 'page'), Arr::get($query, 'page'));
        self::assertEquals(Arr::get($expected, 'limit'), Arr::get($query, 'limit'));
    }

    /**
     * @dataProvider invalidSenderIdentityRoutingListDataProvider
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_get_all_with_errors(array $params): void
    {
        $this->expectException(MailerSendAssertException::class);

        $this->senderIdentityRouting->getAll(
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
    public function test_find_requires_identity_id(): void
    {
        $this->expectException(MailerSendAssertException::class);

        $this->senderIdentityRouting->find('');
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

        $response = $this->senderIdentityRouting->create(
            (new SenderIdentityBuilder('domainId', 'Test', 'test@test.com'))
                ->setReplyToEmail('john@test.com')
                ->setReplyToName('John Doe')
        );

        $request = $this->client->getLastRequest();
        $request_body = json_decode((string)$request->getBody(), true, 512, JSON_THROW_ON_ERROR);

        self::assertEquals('POST', $request->getMethod());
        self::assertEquals('/v1/identities', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);
        self::assertSame('domainId', Arr::get($request_body, 'domain_id'));
        self::assertSame('Test', Arr::get($request_body, 'name'));
        self::assertSame('john@test.com', Arr::get($request_body, 'reply_to_email'));
        self::assertSame('John Doe', Arr::get($request_body, 'reply_to_name'));
        self::assertSame(false, Arr::get($request_body, 'add_note'));
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

        $params = (new SenderIdentityBuilder('domainId', 'Test User', 'test@test.com'))
                        ->setAddNote(true)
                        ->setPersonalNote('Hi, please use this note');

        $response = $this->senderIdentityRouting->update(
            'identityId',
            $params,
        );

        $request = $this->client->getLastRequest();
        $request_body = json_decode((string)$request->getBody(), true, 512, JSON_THROW_ON_ERROR);

        self::assertEquals('PUT', $request->getMethod());
        self::assertEquals('/v1/identities/identityId', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);
        self::assertSame('domainId', Arr::get($request_body, 'domain_id'));
        self::assertSame('Test User', Arr::get($request_body, 'name'));
        self::assertSame('Hi, please use this note', Arr::get($request_body, 'personal_note'));
        self::assertTrue(Arr::get($request_body, 'add_note'));
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

        $response = $this->senderIdentityRouting->delete('identityId');

        $request = $this->client->getLastRequest();

        self::assertEquals('DELETE', $request->getMethod());
        self::assertEquals('/v1/identities/identityId', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function test_delete_required_identityId(): void
    {
        $this->expectException(MailerSendAssertException::class);

        $this->senderIdentityRouting->delete('');
    }

    public function validSenderIdentityRoutingListDataProvider(): array
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

    public function invalidSenderIdentityRoutingListDataProvider(): array
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

    /**
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_find_by_email_requires_valid_email(): void
    {
        $this->expectException(MailerSendAssertException::class);

        $this->senderIdentityRouting->findByEmail('test@mail');
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws MailerSendAssertException
     * @throws \JsonException
     */
    public function test_delete_by_email(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $this->client->addResponse($response);

        $response = $this->senderIdentityRouting->deleteByEmail('test@identity.com');

        $request = $this->client->getLastRequest();

        self::assertEquals('DELETE', $request->getMethod());
        self::assertEquals('/v1/identities/email/test@identity.com', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function test_update_by_email(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $this->client->addResponse($response);

        $params = (new SenderIdentityBuilder('domainId', 'Test User', 'test@test.com'))
            ->setAddNote(true)
            ->setPersonalNote('Hi, please use this note');

        $response = $this->senderIdentityRouting->updateByEmail(
            'test@identity.com',
            $params,
        );

        $request = $this->client->getLastRequest();
        $request_body = json_decode((string)$request->getBody(), true, 512, JSON_THROW_ON_ERROR);

        self::assertEquals('PUT', $request->getMethod());
        self::assertEquals('/v1/identities/email/test@identity.com', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);
        self::assertSame('domainId', Arr::get($request_body, 'domain_id'));
        self::assertSame('Test User', Arr::get($request_body, 'name'));
        self::assertSame('Hi, please use this note', Arr::get($request_body, 'personal_note'));
        self::assertTrue(Arr::get($request_body, 'add_note'));
    }
}
