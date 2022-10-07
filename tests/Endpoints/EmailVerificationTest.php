<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\EmailVerification;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Builder\EmailVerificationParams;
use MailerSend\Tests\TestCase;
use Psr\Http\Message\ResponseInterface;
use Tightenco\Collect\Support\Arr;

class EmailVerificationTest extends TestCase
{
    protected EmailVerification $emailVerification;
    protected ResponseInterface $defaultResponse;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = new Client();

        $this->emailVerification = new EmailVerification(new HttpLayer(self::OPTIONS, $this->client), self::OPTIONS);

        $this->defaultResponse = $this->createMock(ResponseInterface::class);
        $this->defaultResponse->method('getStatusCode')->willReturn(200);
    }

    /**
     * @dataProvider validGetAllDataProvider
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     */
    public function test_get_all(array $payload, array $expected): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $this->client->addResponse($response);

        $response = $this->emailVerification->getAll(
            $payload['page'] ?? null,
            $payload['limit'] ?? null,
        );

        $request = $this->client->getLastRequest();

        parse_str($request->getUri()->getQuery(), $query);

        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/v1/email-verification', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);

        self::assertEquals(Arr::get($expected, 'page'), Arr::get($query, 'page'));
        self::assertEquals(Arr::get($expected, 'limit'), Arr::get($query, 'limit'));
    }

    /**
     * @dataProvider invalidGetAllDataProvider
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_get_all_with_errors(array $payload): void
    {
        $this->expectException(MailerSendAssertException::class);

        $this->emailVerification->getAll(
            $payload['page'] ?? null,
            $payload['limit'] ?? null,
        );
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function test_find_requires_email_verification_id(): void
    {
        $this->expectException(MailerSendAssertException::class);

        $this->emailVerification->find('');
    }

    public function test_create(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $this->client->addResponse($response);

        $response = $this->emailVerification->create(
            (new EmailVerificationParams('file.csv'))
                ->setEmailAddresses(['test@mail.com'])
        );

        $request = $this->client->getLastRequest();
        $request_body = json_decode((string)$request->getBody(), true, 512, JSON_THROW_ON_ERROR);

        self::assertEquals('POST', $request->getMethod());
        self::assertEquals('/v1/email-verification', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);
        self::assertSame('file.csv', Arr::get($request_body, 'name'));
        self::assertEquals(['test@mail.com'], Arr::get($request_body, 'emailAddresses'));
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function test_verify_requires_email_verification_id(): void
    {
        $this->expectException(MailerSendAssertException::class);

        $this->emailVerification->verify('');
    }

    /**
     * @dataProvider validGetResultsDataProvider
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws MailerSendAssertException
     * @throws \JsonException
     */
    public function test_get_results(array $payload, array $expected): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $this->client->addResponse($response);

        $response = $this->emailVerification->getResults(
            'email_verification_id',
            $payload['page'] ?? null,
            $payload['limit'] ?? null,
            $payload['results'] ?? [],
        );

        $request = $this->client->getLastRequest();

        parse_str($request->getUri()->getQuery(), $query);

        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/v1/email-verification/email_verification_id/results', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);

        self::assertEquals(Arr::get($expected, 'page'), Arr::get($query, 'page'));
        self::assertEquals(Arr::get($expected, 'limit'), Arr::get($query, 'limit'));
        self::assertEquals(Arr::get($expected, 'results'), Arr::get($query, 'results'));
    }

    public function test_verify_single_email(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $this->client->addResponse($response);

        $response = $this->emailVerification->verifyEmail('test@mail.com');

        $request = $this->client->getLastRequest();
        $request_body = json_decode((string)$request->getBody(), true, 512, JSON_THROW_ON_ERROR);

        self::assertEquals('POST', $request->getMethod());
        self::assertEquals('/v1/email-verification/verify', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);
        self::assertEquals('test@mail.com', Arr::get($request_body, 'email'));
    }

    public function validGetAllDataProvider(): array
    {
        return [
            'empty request' => [
                'payload' => [],
                'expected' => [
                    'page' => null,
                    'limit' => null,
                ],
            ],
            'with page' => [
                'payload' => [
                    'page' => 1,
                ],
                'expected' => [
                    'page' => 1,
                    'limit' => null,
                ],
            ],
            'with limit' => [
                'payload' => [
                    'limit' => 10,
                ],
                'expected' => [
                    'page' => null,
                    'limit' => 10,
                ],
            ],
            'complete request' => [
                'payload' => [
                    'page' => 1,
                    'limit' => 10,
                ],
                'expected' => [
                    'page' => 1,
                    'limit' => 10,
                ],
            ],
        ];
    }

    public function invalidGetAllDataProvider(): array
    {
        return [
            'with limit under 10' => [
                'payload' => [
                    'limit' => 9,
                ],
            ],
            'with limit over 100' => [
                'payload' => [
                    'limit' => 101,
                ],
            ]
        ];
    }

    public function validGetResultsDataProvider(): array
    {
        return [
            'empty request' => [
                'payload' => [],
                'expected' => [
                    'page' => null,
                    'limit' => null,
                    'results' => null,
                ],
            ],
            'with page' => [
                'payload' => [
                    'page' => 1,
                ],
                'expected' => [
                    'page' => 1,
                    'limit' => null,
                    'results' => null,
                ],
            ],
            'with limit' => [
                'payload' => [
                    'limit' => 10,
                ],
                'expected' => [
                    'page' => null,
                    'limit' => 10,
                    'results' => null,
                ],
            ],
            'with results' => [
                'payload' => [
                    'results' => [EmailVerificationParams::TYPO, EmailVerificationParams::CATCH_ALL],
                ],
                'expected' => [
                    'page' => null,
                    'limit' => null,
                    'results' => 'typo,catch_all',
                ],
            ],
            'complete request' => [
                'payload' => [
                    'page' => 1,
                    'limit' => 10,
                    'results' => [EmailVerificationParams::TYPO, EmailVerificationParams::CATCH_ALL],
                ],
                'expected' => [
                    'page' => 1,
                    'limit' => 10,
                    'results' => 'typo,catch_all',
                ],
            ],
        ];
    }
}
