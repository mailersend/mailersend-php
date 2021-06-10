<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\Token;
use MailerSend\Helpers\Builder\TokenParams;
use MailerSend\Tests\TestCase;
use Psr\Http\Message\ResponseInterface;
use Tightenco\Collect\Support\Arr;

class TokenTest extends TestCase
{
    protected Token $token;
    protected ResponseInterface $defaultResponse;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = new Client();

        $this->token = new Token(new HttpLayer(self::OPTIONS, $this->client), self::OPTIONS);
        $this->defaultResponse = $this->createMock(ResponseInterface::class);
        $this->defaultResponse->method('getStatusCode')->willReturn(200);
    }

    public function test_create_token_with_empty_name_throws_errors(): void
    {
        $this->expectExceptionMessage('Token name is required.');

        $this->token->create(
            new TokenParams('', 'token_domain', TokenParams::ALL_SCOPES)
        );
    }

    public function test_create_token_with_empty_domainId_throws_errors(): void
    {
        $this->expectExceptionMessage('Token domain id is required.');

        $this->token->create(
            new TokenParams('token name', '', TokenParams::ALL_SCOPES)
        );
    }

    public function test_create_token_with_empty_scopes_throws_errors(): void
    {
        $this->expectExceptionMessage('Token scopes are required.');

        $this->token->create(
            new TokenParams('token name', 'domainId', [])
        );
    }

    public function test_create_token_with_invalid_scope_throws_errors(): void
    {
        $this->expectExceptionMessage('Some scopes are not valid.');

        $this->token->create(
            new TokenParams('', '', [ 'invalid_scope' ])
        );
    }

    public function test_create_token()
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $this->client->addResponse($response);

        $response = $this->token->create(
            $this->validTokenParams()
        );

        $request = $this->client->getLastRequest();
        $request_body = json_decode((string) $request->getBody(), true);

        self::assertEquals('POST', $request->getMethod());
        self::assertEquals('/v1/token', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);

        self::assertSame($this->validTokenParams()->getName(), Arr::get($request_body, 'name'));
        self::assertSame($this->validTokenParams()->getDomainId(), Arr::get($request_body, 'domain_id'));
        self::assertSame($this->validTokenParams()->getScopes(), Arr::get($request_body, 'scopes'));
    }

    public function test_pause_token()
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $this->client->addResponse($response);

        $response = $this->token->update(
            'random_id',
            TokenParams::STATUS_PAUSE
        );

        $request = $this->client->getLastRequest();
        $request_body = json_decode((string) $request->getBody(), true);

        self::assertEquals('PUT', $request->getMethod());
        self::assertEquals('/v1/token/random_id/settings', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);

        self::assertSame(TokenParams::STATUS_PAUSE, Arr::get($request_body, 'status'));
    }

    public function test_unpause_token()
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $this->client->addResponse($response);

        $response = $this->token->update(
            'random_id',
            TokenParams::STATUS_UNPAUSE
        );

        $request = $this->client->getLastRequest();
        $request_body = json_decode((string) $request->getBody(), true);

        self::assertEquals('PUT', $request->getMethod());
        self::assertEquals('/v1/token/random_id/settings', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);

        self::assertSame(TokenParams::STATUS_UNPAUSE, Arr::get($request_body, 'status'));
    }

    public function test_delete_token()
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $this->client->addResponse($response);

        $response = $this->token->delete('random_id');

        $request = $this->client->getLastRequest();

        self::assertEquals('DELETE', $request->getMethod());
        self::assertEquals('/v1/token/random_id', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);
    }

    private function validTokenParams(): TokenParams
    {
        return new TokenParams('name', 'domainId', TokenParams::ALL_SCOPES);
    }
}
