<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use MailerSend\Helpers\Arr;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\SenderIdentity;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Builder\SenderIdentity as SenderIdentityBuilder;
use MailerSend\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
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

        $this->defaultResponse = $this->createStub(ResponseInterface::class);
        $this->defaultResponse->method('getStatusCode')->willReturn(200);
    }

    /**
     * @dataProvider validSenderIdentityRoutingListDataProvider
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    #[DataProvider('validSenderIdentityRoutingListDataProvider')]
    public function test_get_all(array $params, array $expected): void
    {
        $response = $this->createStub(ResponseInterface::class);
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
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_get_all_omits_optional_params_when_not_set(): void
    {
        $this->addSuccessResponse();

        $this->senderIdentityRouting->getAll();

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);

        self::assertArrayNotHasKey('domain_id', $query);
        self::assertArrayNotHasKey('page', $query);
        self::assertArrayNotHasKey('query', $query);
        self::assertArrayNotHasKey('order_by', $query);
        self::assertArrayNotHasKey('order', $query);
    }

    /**
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_get_all_with_query_param(): void
    {
        $this->addSuccessResponse();

        $response = $this->senderIdentityRouting->getAll(null, null, null, 'john');

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);

        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/v1/identities', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);
        self::assertEquals('john', $query['query']);
    }

    /**
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_get_all_with_order_by_and_order(): void
    {
        $this->addSuccessResponse();

        $response = $this->senderIdentityRouting->getAll(null, null, null, null, 'email', 'asc');

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);

        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/v1/identities', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);
        self::assertEquals('email', $query['order_by']);
        self::assertEquals('asc', $query['order']);
    }

    /**
     * @dataProvider validOrderByProvider
     * @param string $orderBy
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    #[DataProvider('validOrderByProvider')]
    public function test_get_all_accepts_valid_order_by(string $orderBy): void
    {
        $this->addSuccessResponse();

        $this->senderIdentityRouting->getAll(null, null, null, null, $orderBy);

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);

        self::assertEquals($orderBy, $query['order_by']);
    }

    public static function validOrderByProvider(): array
    {
        return [
            'order by email'       => ['email'],
            'order by created_at'  => ['created_at'],
            'order by verified_at' => ['verified_at'],
        ];
    }

    /**
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_get_all_accepts_order_desc(): void
    {
        $this->addSuccessResponse();

        $this->senderIdentityRouting->getAll(null, null, null, null, null, 'desc');

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);

        self::assertEquals('desc', $query['order']);
    }

    /**
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_get_all_rejects_invalid_order_by(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('order_by must be one of: email, created_at, verified_at.');

        $this->senderIdentityRouting->getAll(null, null, null, null, 'invalid_field');
    }

    /**
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_get_all_rejects_invalid_order(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('order must be asc or desc.');

        $this->senderIdentityRouting->getAll(null, null, null, null, null, 'invalid_order');
    }

    /**
     * @dataProvider invalidSenderIdentityRoutingListDataProvider
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    #[DataProvider('invalidSenderIdentityRoutingListDataProvider')]
    public function test_get_all_with_errors(array $params, string $message): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($message);

        $this->senderIdentityRouting->getAll(
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

        $response = $this->senderIdentityRouting->find('identityId');

        $body = $this->assertRequest('GET', '/v1/identities/identityId');

        self::assertEquals(200, $response['status_code']);
        self::assertEmpty($body);
    }

    /**
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_find_requires_identity_id(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Sender identity id is required.');

        $this->senderIdentityRouting->find('');
    }

    /**
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_create(): void
    {
        $this->addSuccessResponse();

        $response = $this->senderIdentityRouting->create(
            (new SenderIdentityBuilder('domainId', 'Test', 'test@test.com'))
                ->setReplyToEmail('john@test.com')
                ->setReplyToName('John Doe')
        );

        $body = $this->assertRequest('POST', '/v1/identities');

        self::assertEquals(200, $response['status_code']);
        self::assertSame('domainId', Arr::get($body, 'domain_id'));
        self::assertSame('Test', Arr::get($body, 'name'));
        self::assertSame('test@test.com', Arr::get($body, 'email'));
        self::assertSame('john@test.com', Arr::get($body, 'reply_to_email'));
        self::assertSame('John Doe', Arr::get($body, 'reply_to_name'));
        self::assertSame(false, Arr::get($body, 'add_note'));
        self::assertNull(Arr::get($body, 'personal_note'));
    }

    /**
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_create_with_add_note_and_personal_note(): void
    {
        $this->addSuccessResponse();

        $response = $this->senderIdentityRouting->create(
            (new SenderIdentityBuilder('domainId', 'Test', 'test@test.com'))
                ->setAddNote(true)
                ->setPersonalNote('Welcome!')
        );

        $body = $this->assertRequest('POST', '/v1/identities');

        self::assertEquals(200, $response['status_code']);
        self::assertTrue(Arr::get($body, 'add_note'));
        self::assertSame('Welcome!', Arr::get($body, 'personal_note'));
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function test_update(): void
    {
        $this->addSuccessResponse();

        $params = (new SenderIdentityBuilder('domainId', 'Test User', 'test@test.com'))
            ->setReplyToEmail('reply@test.com')
            ->setReplyToName('Reply User');

        $response = $this->senderIdentityRouting->update('identityId', $params);

        $body = $this->assertRequest('PUT', '/v1/identities/identityId');

        self::assertEquals(200, $response['status_code']);
        self::assertSame('Test User', Arr::get($body, 'name'));
        self::assertSame('reply@test.com', Arr::get($body, 'reply_to_email'));
        self::assertSame('Reply User', Arr::get($body, 'reply_to_name'));
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function test_update_excludes_create_only_fields(): void
    {
        $this->addSuccessResponse();

        $params = (new SenderIdentityBuilder('domainId', 'Test User', 'test@test.com'))
            ->setReplyToEmail('reply@test.com')
            ->setReplyToName('Reply Name')
            ->setAddNote(true)
            ->setPersonalNote('some note');

        $response = $this->senderIdentityRouting->update('identityId', $params);

        $body = $this->assertRequest('PUT', '/v1/identities/identityId');

        self::assertEquals(200, $response['status_code']);
        self::assertSame('Test User', $body['name']);
        self::assertSame('reply@test.com', $body['reply_to_email']);
        self::assertSame('Reply Name', $body['reply_to_name']);
        $this->assertBodyExcludes(['domain_id', 'email', 'add_note', 'personal_note'], $body);
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws MailerSendAssertException
     * @throws \JsonException
     */
    public function test_delete(): void
    {
        $response = $this->createStub(ResponseInterface::class);
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
        $this->expectExceptionMessage('Sender identity id is required.');

        $this->senderIdentityRouting->delete('');
    }

    public static function validSenderIdentityRoutingListDataProvider(): array
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

    public static function invalidSenderIdentityRoutingListDataProvider(): array
    {
        return [
            'with limit under 10' => [
                ['limit' => 9],
                'Limit is supposed to be between 10 and 100.',
            ],
            'with limit over 100' => [
                ['limit' => 101],
                'Limit is supposed to be between 10 and 100.',
            ],
        ];
    }

    /**
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws MailerSendAssertException
     */
    public function test_find_by_email(): void
    {
        $this->addSuccessResponse();

        $response = $this->senderIdentityRouting->findByEmail('test@identity.com');

        $body = $this->assertRequest('GET', '/v1/identities/email/test@identity.com');

        self::assertEquals(200, $response['status_code']);
        self::assertEmpty($body);
    }

    /**
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_find_by_email_requires_valid_email(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Valid email is required');

        $this->senderIdentityRouting->findByEmail('test@mail');
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws MailerSendAssertException
     * @throws \JsonException
     */
    public function test_delete_by_email(): void
    {
        $response = $this->createStub(ResponseInterface::class);
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
    public function test_delete_by_email_requires_valid_email(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Valid email is required.');

        $this->senderIdentityRouting->deleteByEmail('not-an-email');
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function test_update_by_email(): void
    {
        $this->addSuccessResponse();

        $params = (new SenderIdentityBuilder('domainId', 'Test User', 'test@test.com'))
            ->setReplyToEmail('reply@test.com')
            ->setReplyToName('Reply User');

        $response = $this->senderIdentityRouting->updateByEmail('test@identity.com', $params);

        $body = $this->assertRequest('PUT', '/v1/identities/email/test@identity.com');

        self::assertEquals(200, $response['status_code']);
        self::assertSame('Test User', Arr::get($body, 'name'));
        self::assertSame('reply@test.com', Arr::get($body, 'reply_to_email'));
        self::assertSame('Reply User', Arr::get($body, 'reply_to_name'));
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function test_update_by_email_excludes_create_only_fields(): void
    {
        $this->addSuccessResponse();

        $params = (new SenderIdentityBuilder('domainId', 'Test User', 'test@test.com'))
            ->setReplyToEmail('reply@test.com')
            ->setReplyToName('Reply Name')
            ->setAddNote(true)
            ->setPersonalNote('some note');

        $response = $this->senderIdentityRouting->updateByEmail('test@identity.com', $params);

        $body = $this->assertRequest('PUT', '/v1/identities/email/test@identity.com');

        self::assertEquals(200, $response['status_code']);
        self::assertSame('Test User', $body['name']);
        self::assertSame('reply@test.com', $body['reply_to_email']);
        self::assertSame('Reply Name', $body['reply_to_name']);
        $this->assertBodyExcludes(['domain_id', 'email', 'add_note', 'personal_note'], $body);
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function test_update_by_email_requires_valid_email(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Valid email is required.');

        $this->senderIdentityRouting->updateByEmail(
            'not-an-email',
            new SenderIdentityBuilder('domainId', 'Test', 'test@test.com')
        );
    }

    /**
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws MailerSendAssertException
     */
    public function test_resend(): void
    {
        $this->addSuccessResponse();

        $response = $this->senderIdentityRouting->resend('identityId');

        $body = $this->assertRequest('POST', '/v1/identities/identityId/resend');

        self::assertEquals(200, $response['status_code']);
        self::assertEmpty($body);
    }

    /**
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_resend_requires_identity_id(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Sender identity id is required.');

        $this->senderIdentityRouting->resend('');
    }
}
