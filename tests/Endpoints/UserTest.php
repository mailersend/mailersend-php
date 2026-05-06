<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use MailerSend\Common\HttpLayer;
use MailerSend\Common\Roles;
use MailerSend\Endpoints\User;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Builder\UserParams;
use MailerSend\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Http\Message\ResponseInterface;
use MailerSend\Helpers\Arr;

class UserTest extends TestCase
{
    protected User $user;
    protected ResponseInterface $defaultResponse;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = new Client();

        $this->user = new User(new HttpLayer(self::OPTIONS, $this->client), self::OPTIONS);

        $this->defaultResponse = $this->createStub(ResponseInterface::class);
        $this->defaultResponse->method('getStatusCode')->willReturn(200);
    }

    /**
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_get_all(): void
    {
        $this->addSuccessResponse();

        $response = $this->user->getAll();

        $body = $this->assertRequest('GET', '/v1/users');

        self::assertEquals(200, $response['status_code']);
    }

    /**
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_get_all_with_page(): void
    {
        $this->addSuccessResponse();

        $this->user->getAll(2);

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);
        $this->assertQueryParams(['page' => '2'], $query);
    }

    public function test_get_all_with_limit(): void
    {
        $this->addSuccessResponse();

        $this->user->getAll(null, 15);

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);
        $this->assertQueryParams(['limit' => '15'], $query);
    }

    /**
     * @dataProvider invalidGetAllLimitProvider
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    #[DataProvider('invalidGetAllLimitProvider')]
    public function test_get_all_rejects_invalid_limit(int $limit, string $exceptionMessage): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $this->user->getAll(null, $limit);
    }

    public static function invalidGetAllLimitProvider(): array
    {
        return [
            'limit below minimum' => [9, 'Limit is supposed to be between 10 and 100.'],
            'limit above maximum' => [101, 'Limit is supposed to be between 10 and 100.'],
        ];
    }

    /**
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_find(): void
    {
        $this->addSuccessResponse();

        $response = $this->user->find('userId');

        $this->assertRequest('GET', '/v1/users/userId');

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
        $this->expectExceptionMessage('User id is required.');

        $this->user->find('');
    }

    /**
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_create(): void
    {
        $this->addSuccessResponse();

        $response = $this->user->create(
            (new UserParams('test@user.com', Roles::ADMIN))
        );

        $body = $this->assertRequest('POST', '/v1/users');

        self::assertEquals(200, $response['status_code']);
        self::assertSame('test@user.com', Arr::get($body, 'email'));
        self::assertSame(Roles::ADMIN, Arr::get($body, 'role'));
        // requires_periodic_password_change defaults to false, which is stripped by array_filter
        $this->assertBodyExcludes(['requires_periodic_password_change'], $body);
    }

    /**
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_create_with_all_params(): void
    {
        $this->addSuccessResponse();

        $params = (new UserParams('test@user.com', Roles::CUSTOM_USER))
            ->setPermissions(['permission_a', 'permission_b'])
            ->setTemplates(['template_id_1'])
            ->setDomains(['domain_id_1'])
            ->setRequiresPeriodicPasswordChange(true);

        $response = $this->user->create($params);

        $body = $this->assertRequest('POST', '/v1/users');

        self::assertEquals(200, $response['status_code']);
        self::assertSame('test@user.com', Arr::get($body, 'email'));
        self::assertSame(Roles::CUSTOM_USER, Arr::get($body, 'role'));
        self::assertSame(['permission_a', 'permission_b'], Arr::get($body, 'permissions'));
        self::assertSame(['template_id_1'], Arr::get($body, 'templates'));
        self::assertSame(['domain_id_1'], Arr::get($body, 'domains'));
        self::assertTrue(Arr::get($body, 'requires_periodic_password_change'));
    }

    /**
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_create_excludes_empty_optional_fields(): void
    {
        $this->addSuccessResponse();

        $response = $this->user->create(new UserParams('test@user.com', Roles::ADMIN));

        $body = $this->assertRequest('POST', '/v1/users');

        // Empty arrays and false are stripped by array_filter in UserParams::toArray()
        $this->assertBodyExcludes(['permissions', 'templates', 'domains', 'requires_periodic_password_change'], $body);
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function test_update(): void
    {
        $this->addSuccessResponse();

        $params = (new UserParams('test@user.com', Roles::ADMIN))
            ->setRequiresPeriodicPasswordChange(true);

        $response = $this->user->update('userId', $params);

        $body = $this->assertRequest('PUT', '/v1/users/userId');

        self::assertEquals(200, $response['status_code']);
        self::assertSame('test@user.com', Arr::get($body, 'email'));
        self::assertSame(Roles::ADMIN, Arr::get($body, 'role'));
        self::assertTrue(Arr::get($body, 'requires_periodic_password_change'));
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function test_update_excludes_fields_not_set(): void
    {
        $this->addSuccessResponse();

        $params = new UserParams('test@user.com', Roles::MANAGER);

        $response = $this->user->update('userId', $params);

        $body = $this->assertRequest('PUT', '/v1/users/userId');

        self::assertEquals(200, $response['status_code']);
        $this->assertBodyExcludes(['permissions', 'templates', 'domains', 'requires_periodic_password_change'], $body);
    }

    /**
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_update_with_all_params(): void
    {
        $this->addSuccessResponse();

        $params = (new UserParams('test@user.com', Roles::CUSTOM_USER))
            ->setPermissions(['permission_a', 'permission_b'])
            ->setTemplates(['template_id_1'])
            ->setDomains(['domain_id_1'])
            ->setRequiresPeriodicPasswordChange(true);

        $response = $this->user->update('userId', $params);

        $body = $this->assertRequest('PUT', '/v1/users/userId');

        self::assertEquals(200, $response['status_code']);
        $this->assertBodyContains([
            'email' => 'test@user.com',
            'role' => Roles::CUSTOM_USER,
            'permissions' => ['permission_a', 'permission_b'],
            'templates' => ['template_id_1'],
            'domains' => ['domain_id_1'],
            'requires_periodic_password_change' => true,
        ], $body);
    }

    /**
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_update_requires_user_id(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('User id is required.');
        $this->user->update('', new UserParams('test@user.com', Roles::ADMIN));
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function test_delete(): void
    {
        $this->addSuccessResponse();

        $response = $this->user->delete('userId');

        $this->assertRequest('DELETE', '/v1/users/userId');

        self::assertEquals(200, $response['status_code']);
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function test_delete_requires_user_id(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('User id is required.');

        $this->user->delete('');
    }
}
