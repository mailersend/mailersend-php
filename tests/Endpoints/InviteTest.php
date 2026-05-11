<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\Invite;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Arr;
use MailerSend\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
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

        $this->defaultResponse = $this->createStub(ResponseInterface::class);
        $this->defaultResponse->method('getStatusCode')->willReturn(200);
    }

    /**
     * @dataProvider validInviteRoutingListDataProvider
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    #[DataProvider('validInviteRoutingListDataProvider')]
    public function test_get_all(array $params, array $expected): void
    {
        $this->addSuccessResponse();

        $response = $this->invite->getAll(
            Arr::get($params, 'page'),
            Arr::get($params, 'limit'),
        );

        $request = $this->client->getLastRequest();

        parse_str($request->getUri()->getQuery(), $query);

        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/v1/invites', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);

        self::assertEquals(Arr::get($expected, 'page'), Arr::get($query, 'page'));
        self::assertEquals(Arr::get($expected, 'limit'), Arr::get($query, 'limit'));
    }

    /**
     * @dataProvider invalidInviteRoutingListDataProvider
     * @param array $params
     * @param string $message
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    #[DataProvider('invalidInviteRoutingListDataProvider')]
    public function test_get_all_with_errors(array $params, string $message): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($message);

        $this->invite->getAll(
            Arr::get($params, 'page'),
            Arr::get($params, 'limit'),
        );
    }

    /**
     * @throws \JsonException
     * @throws MailerSendAssertException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_find(): void
    {
        $this->addSuccessResponse();

        $response = $this->invite->find('inviteId');

        $this->assertRequest('GET', '/v1/invites/inviteId');

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
        $this->expectExceptionMessage('Invite id is required.');

        $this->invite->find('');
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws MailerSendAssertException
     * @throws \JsonException
     */
    public function test_resend(): void
    {
        $this->addSuccessResponse();

        $response = $this->invite->resend('inviteId');

        $this->assertRequest('POST', '/v1/invites/inviteId/resend');

        self::assertEquals(200, $response['status_code']);
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function test_resend_required_inviteId(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Invite id is required.');

        $this->invite->resend('');
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws MailerSendAssertException
     * @throws \JsonException
     */
    public function test_cancel(): void
    {
        $this->addSuccessResponse(204);

        $response = $this->invite->cancel('inviteId');

        $this->assertRequest('DELETE', '/v1/invites/inviteId');

        self::assertEquals(204, $response['status_code']);
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function test_cancel_required_inviteId(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Invite id is required.');

        $this->invite->cancel('');
    }

    public static function validInviteRoutingListDataProvider(): array
    {
        return [
            'empty request' => [
                'params' => [],
                'expected' => [
                    'page' => null,
                    'limit' => null,
                ],
            ],
            'with page' => [
                [
                    'page' => 1,
                ],
                [
                    'page' => 1,
                    'limit' => null,
                ],
            ],
            'with limit' => [
                [
                    'limit' => 10,
                ],
                [
                    'page' => null,
                    'limit' => 10,
                ],
            ],
            'complete request' => [
                [
                    'page' => 1,
                    'limit' => 10,
                ],
                [
                    'page' => 1,
                    'limit' => 10,
                ],
            ],
        ];
    }

    public static function invalidInviteRoutingListDataProvider(): array
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
}
