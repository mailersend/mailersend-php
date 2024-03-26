<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use Illuminate\Support\Arr;
use MailerSend\Common\HttpLayer;
use MailerSend\Common\Roles;
use MailerSend\Endpoints\User;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Builder\UserParams;
use MailerSend\Tests\TestCase;
use Psr\Http\Message\ResponseInterface;

class UserTest extends TestCase
{
    protected User $user;
    protected ResponseInterface $defaultResponse;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = new Client();

        $this->user = new User(new HttpLayer(self::OPTIONS, $this->client), self::OPTIONS);

        $this->defaultResponse = $this->createMock(ResponseInterface::class);
        $this->defaultResponse->method('getStatusCode')->willReturn(200);
    }

    /**
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_get_all(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $this->client->addResponse($response);

        $response = $this->user->getAll();

        $request = $this->client->getLastRequest();

        parse_str($request->getUri()->getQuery(), $query);

        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/v1/users', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);
    }

    /**
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_find_requires_user_id(): void
    {
        $this->expectException(MailerSendAssertException::class);

        $this->user->find('');
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

        $response = $this->user->create(
            (new UserParams('test@user.com', Roles::ADMIN))
        );

        $request = $this->client->getLastRequest();
        $request_body = json_decode((string)$request->getBody(), true, 512, JSON_THROW_ON_ERROR);

        self::assertEquals('POST', $request->getMethod());
        self::assertEquals('/v1/users', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);
        self::assertSame('test@user.com', Arr::get($request_body, 'email'));
        self::assertSame('Admin', Arr::get($request_body, 'role'));
        self::assertNull(Arr::get($request_body, 'requires_periodic_password_change'));
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

        $params = (new UserParams('test@user.com', Roles::ADMIN))
            ->setRequiresPeriodicPasswordChange(true);

        $response = $this->user->update(
            'userId',
            $params,
        );

        $request = $this->client->getLastRequest();
        $request_body = json_decode((string)$request->getBody(), true, 512, JSON_THROW_ON_ERROR);

        self::assertEquals('PUT', $request->getMethod());
        self::assertEquals('/v1/users/userId', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);
        self::assertSame('test@user.com', Arr::get($request_body, 'email'));
        self::assertSame(Roles::ADMIN, Arr::get($request_body, 'role'));
        self::assertTrue(Arr::get($request_body, 'requires_periodic_password_change'));
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

        $response = $this->user->delete('userId');

        $request = $this->client->getLastRequest();

        self::assertEquals('DELETE', $request->getMethod());
        self::assertEquals('/v1/users/userId', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function test_delete_required_userId(): void
    {
        $this->expectException(MailerSendAssertException::class);

        $this->user->delete('');
    }
}
