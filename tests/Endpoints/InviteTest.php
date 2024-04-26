<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use Illuminate\Support\Arr;
use MailerSend\Common\HttpLayer;
use MailerSend\Common\Roles;
use MailerSend\Endpoints\Invite;
use MailerSend\Endpoints\User;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Builder\UserParams;
use MailerSend\Tests\TestCase;
use Psr\Http\Message\ResponseInterface;

class InviteTest extends TestCase
{
    protected Invite $invite;
    protected ResponseInterface $defaultResponse;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = new Client();

        $this->invite = new Invite(new HttpLayer(self::OPTIONS, $this->client), self::OPTIONS);

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

        $response = $this->invite->getAll();

        $request = $this->client->getLastRequest();

        parse_str($request->getUri()->getQuery(), $query);

        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/v1/invites', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);
    }

    /**
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_find_requires_invite_id(): void
    {
        $this->expectException(MailerSendAssertException::class);

        $this->invite->find('');
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws MailerSendAssertException
     * @throws \JsonException
     */
    public function test_resend(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $this->client->addResponse($response);

        $response = $this->invite->resend('inviteId');

        $request = $this->client->getLastRequest();

        self::assertEquals('POST', $request->getMethod());
        self::assertEquals('/v1/invites/inviteId/resend', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function test_resend_required_inviteId(): void
    {
        $this->expectException(MailerSendAssertException::class);

        $this->invite->resend('');
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws MailerSendAssertException
     * @throws \JsonException
     */
    public function test_cancel(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $this->client->addResponse($response);

        $response = $this->invite->cancel('inviteId');

        $request = $this->client->getLastRequest();

        self::assertEquals('DELETE', $request->getMethod());
        self::assertEquals('/v1/invites/inviteId/cancel', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function test_cancel_required_inviteId(): void
    {
        $this->expectException(MailerSendAssertException::class);

        $this->invite->cancel('');
    }
}
