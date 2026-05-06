<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use MailerSend\Common\Constants;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\EmailVerification;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Builder\EmailVerificationParams;
use MailerSend\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Http\Message\ResponseInterface;
use MailerSend\Helpers\Arr;

class EmailVerificationTest extends TestCase
{
    protected EmailVerification $emailVerification;
    protected ResponseInterface $defaultResponse;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = new Client();

        $this->emailVerification = new EmailVerification(new HttpLayer(self::OPTIONS, $this->client), self::OPTIONS);

        $this->defaultResponse = $this->createStub(ResponseInterface::class);
        $this->defaultResponse->method('getStatusCode')->willReturn(200);
    }

    /**
     * @dataProvider validGetAllDataProvider
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     */
    #[DataProvider('validGetAllDataProvider')]
    public function test_get_all(array $payload, array $expected): void
    {
        $this->addSuccessResponse();

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
    #[DataProvider('invalidGetAllDataProvider')]
    public function test_get_all_rejects_invalid_params(array $payload, string $message): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($message);

        $this->emailVerification->getAll(
            $payload['page'] ?? null,
            $payload['limit'] ?? null,
        );
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     * @throws MailerSendAssertException
     */
    public function test_find_uses_correct_method_and_path(): void
    {
        $this->addSuccessResponse();
        $this->emailVerification->find('ev-id');
        $this->assertRequest('GET', '/v1/email-verification/ev-id');
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     * @throws MailerSendAssertException
     */
    public function test_find(): void
    {
        $this->addSuccessResponse();

        $response = $this->emailVerification->find('email_verification_id');

        $this->assertRequest('GET', '/v1/email-verification/email_verification_id');

        self::assertEquals(200, $response['status_code']);
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     * @throws MailerSendAssertException
     */
    public function test_find_with_optional_params(): void
    {
        $this->addSuccessResponse();

        $this->emailVerification->find('email_verification_id', true, 2, 25);

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);

        $this->assertQueryParams(['detailed' => '1', 'page' => '2', 'limit' => '25'], $query);
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function test_find_requires_email_verification_id(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Email Verification id is required.');

        $this->emailVerification->find('');
    }

    public function test_create(): void
    {
        $this->addSuccessResponse();

        $response = $this->emailVerification->create(
            (new EmailVerificationParams('file.csv'))
                ->setEmailAddresses(['test@mail.com'])
        );

        $body = $this->assertRequest('POST', '/v1/email-verification');

        self::assertEquals(200, $response['status_code']);
        self::assertSame('file.csv', Arr::get($body, 'name'));
        self::assertEquals(['test@mail.com'], Arr::get($body, 'emails'));
    }

    public function test_create_with_list_id(): void
    {
        $this->addSuccessResponse();

        $response = $this->emailVerification->create(
            (new EmailVerificationParams())
                ->setListId('list-abc-123')
        );

        $body = $this->assertRequest('POST', '/v1/email-verification');

        self::assertEquals(200, $response['status_code']);
        self::assertSame('list-abc-123', Arr::get($body, 'list_id'));
    }

    public function test_create_with_verify_flag(): void
    {
        $this->addSuccessResponse();

        $response = $this->emailVerification->create(
            (new EmailVerificationParams('file.csv'))
                ->setEmailAddresses(['test@mail.com'])
                ->setVerify(true)
        );

        $body = $this->assertRequest('POST', '/v1/email-verification');

        self::assertEquals(200, $response['status_code']);
        self::assertTrue(Arr::get($body, 'verify'));
    }

    public function test_create_requires_name_when_list_id_not_set(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Email Verification name is required.');

        $this->emailVerification->create(new EmailVerificationParams(''));
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     * @throws MailerSendAssertException
     */
    public function test_verify(): void
    {
        $this->addSuccessResponse();

        $response = $this->emailVerification->verify('email_verification_id');

        $this->assertRequest('GET', '/v1/email-verification/email_verification_id/verify');

        self::assertEquals(200, $response['status_code']);
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function test_verify_requires_email_verification_id(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Email Verification id is required.');

        $this->emailVerification->verify('');
    }

    /**
     * @dataProvider validGetResultsDataProvider
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws MailerSendAssertException
     * @throws \JsonException
     */
    #[DataProvider('validGetResultsDataProvider')]
    public function test_get_results(array $payload, array $expected): void
    {
        $this->addSuccessResponse();

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

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function test_get_results_requires_email_verification_id(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Email Verification id is required.');

        $this->emailVerification->getResults('');
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function test_get_results_rejects_invalid_result_value(): void
    {
        $this->expectException(MailerSendAssertException::class);

        $this->emailVerification->getResults('email_verification_id', null, null, ['invalid_result']);
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     * @throws MailerSendAssertException
     */
    public function test_get_results_sends_results_filter_param(): void
    {
        $this->addSuccessResponse();
        $this->emailVerification->getResults('ev-id', null, null, ['valid', 'catch_all']);
        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);
        self::assertContains('valid', $query['results'] ?? []);
        self::assertContains('catch_all', $query['results'] ?? []);
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     * @throws MailerSendAssertException
     */
    public function test_get_results_excludes_results_param_when_not_set(): void
    {
        $this->addSuccessResponse();
        $this->emailVerification->getResults('ev-id');
        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);
        self::assertArrayNotHasKey('results', $query);
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     * @throws MailerSendAssertException
     */
    public function test_verify_single_email(): void
    {
        $this->addSuccessResponse();

        $response = $this->emailVerification->verifyEmail('test@mail.com');

        $body = $this->assertRequest('POST', '/v1/email-verification/verify');

        self::assertEquals(200, $response['status_code']);
        self::assertEquals('test@mail.com', Arr::get($body, 'email'));
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     * @throws MailerSendAssertException
     */
    public function test_verify_single_email_sends_email_in_body(): void
    {
        $this->addSuccessResponse();
        $this->emailVerification->verifyEmail('test@example.com');
        $body = $this->assertRequest('POST', '/v1/email-verification/verify');
        $this->assertBodyContains(['email' => 'test@example.com'], $body);
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function test_verify_single_email_requires_email(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Email address is required.');

        $this->emailVerification->verifyEmail('');
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     * @throws MailerSendAssertException
     */
    public function test_verify_async(): void
    {
        $this->addSuccessResponse();

        $response = $this->emailVerification->verifyAsync('test@mail.com');

        $body = $this->assertRequest('POST', '/v1/email-verification/verify-async');

        self::assertEquals(200, $response['status_code']);
        self::assertEquals('test@mail.com', Arr::get($body, 'email'));
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     * @throws MailerSendAssertException
     */
    public function test_verify_async_sends_email_in_body(): void
    {
        $this->addSuccessResponse();
        $this->emailVerification->verifyAsync('test@example.com');
        $body = $this->assertRequest('POST', '/v1/email-verification/verify-async');
        $this->assertBodyContains(['email' => 'test@example.com'], $body);
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function test_verify_async_requires_email(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Email address is required.');

        $this->emailVerification->verifyAsync('');
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     * @throws MailerSendAssertException
     */
    public function test_get_verify_async_result(): void
    {
        $this->addSuccessResponse();

        $response = $this->emailVerification->getVerifyAsyncResult('async-result-id');

        $this->assertRequest('GET', '/v1/email-verification/verify-async/async-result-id');

        self::assertEquals(200, $response['status_code']);
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function test_get_verify_async_result_requires_id(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Single email verification id is required.');

        $this->emailVerification->getVerifyAsyncResult('');
    }

    public static function validGetAllDataProvider(): array
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

    public static function invalidGetAllDataProvider(): array
    {
        return [
            'limit below minimum' => [
                'payload' => [
                    'limit' => 9,
                ],
                'message' => 'Limit is supposed to be between ' . Constants::MIN_LIMIT . ' and ' . Constants::MAX_LIMIT . '.',
            ],
            'limit above maximum' => [
                'payload' => [
                    'limit' => 101,
                ],
                'message' => 'Limit is supposed to be between ' . Constants::MIN_LIMIT . ' and ' . Constants::MAX_LIMIT . '.',
            ],
        ];
    }

    public static function validGetResultsDataProvider(): array
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
                    'results' => [EmailVerificationParams::TYPO, EmailVerificationParams::CATCH_ALL],
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
                    'results' => [EmailVerificationParams::TYPO, EmailVerificationParams::CATCH_ALL],
                ],
            ],
        ];
    }
}
