<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use MailerSend\Helpers\Arr;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\Token;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Builder\TokenParams;
use MailerSend\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Http\Message\ResponseInterface;

class TokenTest extends TestCase
{
    protected Token $token;
    protected ResponseInterface $defaultResponse;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = new Client();

        $this->token = new Token(new HttpLayer(self::OPTIONS, $this->client), self::OPTIONS);
        $this->defaultResponse = $this->createStub(ResponseInterface::class);
        $this->defaultResponse->method('getStatusCode')->willReturn(200);
    }

    public function test_get_all_sends_correct_method_and_path(): void
    {
        $this->addSuccessResponse();

        $this->token->getAll();

        $request = $this->client->getLastRequest();
        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/v1/token', $request->getUri()->getPath());
    }

    public function test_get_all_returns_status_code(): void
    {
        $this->addSuccessResponse();

        $response = $this->token->getAll();

        self::assertEquals(200, $response['status_code']);
    }

    public function test_get_all_without_page_omits_page_from_query(): void
    {
        $this->addSuccessResponse();

        $this->token->getAll();

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);

        self::assertArrayNotHasKey('page', $query);
    }

    public function test_get_all_with_page(): void
    {
        $this->addSuccessResponse();

        $this->token->getAll(2);

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);

        $this->assertQueryParams(['page' => '2'], $query);
    }

    public function test_get_all_with_limit(): void
    {
        $this->addSuccessResponse();

        $this->token->getAll(null, 50);

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);

        $this->assertQueryParams(['limit' => '50'], $query);
    }

    public function test_get_all_with_page_and_limit(): void
    {
        $this->addSuccessResponse();

        $this->token->getAll(3, 20);

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);

        $this->assertQueryParams(['page' => '3', 'limit' => '20'], $query);
    }

    /**
     * @dataProvider invalidGetAllLimitProvider
     */
    #[DataProvider('invalidGetAllLimitProvider')]
    public function test_get_all_rejects_invalid_limit(int $limit, string $exceptionMessage): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $this->token->getAll(null, $limit);
    }

    public static function invalidGetAllLimitProvider(): array
    {
        return [
            'limit below minimum' => [9, 'Limit is supposed to be between 10 and 100.'],
            'limit above maximum' => [101, 'Limit is supposed to be between 10 and 100.'],
        ];
    }

    public function test_find_token(): void
    {
        $this->addSuccessResponse();

        $response = $this->token->find('random_id');

        $this->assertRequest('GET', '/v1/token/random_id');
        self::assertEquals(200, $response['status_code']);
    }

    public function test_find_token_requires_id(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Token id is required.');

        $this->token->find('');
    }

    // -------------------------------------------------------------------------
    // create
    // -------------------------------------------------------------------------

    public function test_create_token(): void
    {
        $this->addSuccessResponse();

        $response = $this->token->create($this->validTokenParams());

        $body = $this->assertRequest('POST', '/v1/token');

        self::assertEquals(200, $response['status_code']);
        self::assertSame($this->validTokenParams()->getName(), Arr::get($body, 'name'));
        self::assertSame($this->validTokenParams()->getDomainId(), Arr::get($body, 'domain_id'));
        self::assertSame($this->validTokenParams()->getScopes(), Arr::get($body, 'scopes'));
    }

    public function test_create_token_without_domain_id_excludes_it_from_body(): void
    {
        $this->addSuccessResponse();

        $response = $this->token->create(
            new TokenParams('token name', null, TokenParams::ALL_SCOPES)
        );

        $body = $this->assertRequest('POST', '/v1/token');

        self::assertEquals(200, $response['status_code']);
        $this->assertBodyExcludes(['domain_id'], $body);
    }

    /**
     * @dataProvider invalidCreateParamsProvider
     */
    #[DataProvider('invalidCreateParamsProvider')]
    public function test_create_rejects_invalid_params(TokenParams $params, string $exceptionMessage): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $this->token->create($params);
    }

    public static function invalidCreateParamsProvider(): array
    {
        return [
            'empty name' => [
                new TokenParams('', 'token_domain', TokenParams::ALL_SCOPES),
                'Token name is required.',
            ],
            'empty scopes' => [
                new TokenParams('token name', 'domainId', []),
                'Token scopes are required.',
            ],
        ];
    }

    public function test_create_token_with_invalid_scope_throws_error(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Some scopes are not valid.');

        new TokenParams('token name', 'domainId', ['invalid_scope']);
    }

    // -------------------------------------------------------------------------
    // update
    // -------------------------------------------------------------------------

    public function test_update_token_with_pause_status(): void
    {
        $this->addSuccessResponse();

        $response = $this->token->update('random_id', TokenParams::STATUS_PAUSE);

        $body = $this->assertRequest('PUT', '/v1/token/random_id');

        self::assertEquals(200, $response['status_code']);
        self::assertSame(TokenParams::STATUS_PAUSE, Arr::get($body, 'status'));
        $this->assertBodyExcludes(['name'], $body);
    }

    public function test_update_token_with_unpause_status(): void
    {
        $this->addSuccessResponse();

        $response = $this->token->update('random_id', TokenParams::STATUS_UNPAUSE);

        $body = $this->assertRequest('PUT', '/v1/token/random_id');

        self::assertEquals(200, $response['status_code']);
        self::assertSame(TokenParams::STATUS_UNPAUSE, Arr::get($body, 'status'));
        $this->assertBodyExcludes(['name'], $body);
    }

    public function test_update_token_with_name_and_status(): void
    {
        $this->addSuccessResponse();

        $response = $this->token->update('random_id', TokenParams::STATUS_PAUSE, 'new_name');

        $body = $this->assertRequest('PUT', '/v1/token/random_id');

        self::assertEquals(200, $response['status_code']);
        self::assertSame(TokenParams::STATUS_PAUSE, Arr::get($body, 'status'));
        self::assertSame('new_name', Arr::get($body, 'name'));
    }

    public function test_update_token_with_only_name_excludes_status_from_body(): void
    {
        $this->addSuccessResponse();

        $response = $this->token->update('random_id', null, 'only_name');

        $body = $this->assertRequest('PUT', '/v1/token/random_id');

        self::assertEquals(200, $response['status_code']);
        self::assertSame('only_name', Arr::get($body, 'name'));
        $this->assertBodyExcludes(['status'], $body);
    }

    public function test_update_token_requires_id(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Token id is required.');

        $this->token->update('', TokenParams::STATUS_PAUSE);
    }

    public function test_update_token_with_invalid_status_throws_error(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Value "invalid_status" is not an element of the valid values');

        $this->token->update('random_id', 'invalid_status');
    }

    // -------------------------------------------------------------------------
    // changeName
    // -------------------------------------------------------------------------

    public function test_change_name(): void
    {
        $this->addSuccessResponse();

        $response = $this->token->changeName('random_id', 'changed_name');

        $body = $this->assertRequest('PUT', '/v1/token/random_id');

        self::assertEquals(200, $response['status_code']);
        self::assertSame('changed_name', Arr::get($body, 'name'));
    }

    public function test_change_name_requires_id(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Token id is required.');

        $this->token->changeName('', 'new_name');
    }

    public function test_change_name_requires_name(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Token name is required.');

        $this->token->changeName('random_id', '');
    }

    // -------------------------------------------------------------------------
    // delete
    // -------------------------------------------------------------------------

    public function test_delete_token(): void
    {
        $this->addSuccessResponse();

        $response = $this->token->delete('random_id');

        $this->assertRequest('DELETE', '/v1/token/random_id');
        self::assertEquals(200, $response['status_code']);
    }

    public function test_delete_token_requires_id(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Token id is required.');

        $this->token->delete('');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function validTokenParams(): TokenParams
    {
        return new TokenParams('name', 'domainId', TokenParams::ALL_SCOPES);
    }
}
