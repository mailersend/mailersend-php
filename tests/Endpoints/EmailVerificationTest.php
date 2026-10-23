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
                ->setEmailAddresses(['test@mail.com'])
        );

        $body = $this->assertRequest('POST', '/v1/email-verification');

        self::assertEquals(200, $response['status_code']);
        self::assertSame('list-abc-123', Arr::get($body, 'list_id'));
        self::assertSame(['test@mail.com'], Arr::get($body, 'emails'));
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

    /**
     * @dataProvider invalidCreateParamsProvider
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    #[DataProvider('invalidCreateParamsProvider')]
    public function test_create_rejects_invalid_params(EmailVerificationParams $params, string $message): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($message);

        $this->emailVerification->create($params);
    }

    public static function invalidCreateParamsProvider(): array
    {
        return [
            'missing name without list id' => [
                (new EmailVerificationParams(''))->setEmailAddresses(['test@mail.com']),
                'Email Verification name is required.',
            ],
            'name exceeds 191 chars' => [
                (new EmailVerificationParams(str_repeat('a', 192)))->setEmailAddresses(['test@mail.com']),
                'Email Verification name may not exceed 191 characters.',
            ],
            'empty emails without list id' => [
                new EmailVerificationParams('file.csv'),
                'Emails list must contain at least 1 item.',
            ],
            'empty emails with list id' => [
                (new EmailVerificationParams())->setListId('list-abc-123'),
                'Emails list must contain at least 1 item.',
            ],
            'more than 10000 emails' => [
                (new EmailVerificationParams('file.csv'))->setEmailAddresses(array_fill(0, 10001, 'test@mail.com')),
                'Emails list may not contain more than 10000 items.',
            ],
            'email address exceeds 320 chars' => [
                (new EmailVerificationParams('file.csv'))->setEmailAddresses([str_repeat('a', 312) . '@mail.com']),
                'Email address may not exceed 320 characters.',
            ],
        ];
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
     * @dataProvider invalidGetResultsParamsProvider
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    #[DataProvider('invalidGetResultsParamsProvider')]
    public function test_get_results_rejects_invalid_params(string $id, ?int $limit, array $results, string $message): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($message);

        $this->emailVerification->getResults($id, null, $limit, $results);
    }

    public static function invalidGetResultsParamsProvider(): array
    {
        return [
            'empty id' => ['', null, [], 'Email Verification id is required.'],
            'limit below minimum' => ['email_verification_id', 9, [], 'Limit is supposed to be between ' . Constants::MIN_LIMIT . ' and 500.'],
            'limit above maximum' => ['email_verification_id', 501, [], 'Limit is supposed to be between ' . Constants::MIN_LIMIT . ' and 500.'],
            'unknown result value' => [
                'email_verification_id',
                null,
                ['invalid_result'],
                'Value "invalid_result" is not an element of the valid values: valid, catch_all, mailbox_full, role_based, unknown, syntax_error, typo, mailbox_not_found, disposable, mailbox_blocked, failed, queued',
            ],
        ];
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
     * @dataProvider invalidSingleEmailProvider
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    #[DataProvider('invalidSingleEmailProvider')]
    public function test_verify_single_email_rejects_invalid_email(string $email, string $message): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($message);

        $this->emailVerification->verifyEmail($email);
    }

    /**
     * @dataProvider invalidSingleEmailProvider
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    #[DataProvider('invalidSingleEmailProvider')]
    public function test_verify_async_rejects_invalid_email(string $email, string $message): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($message);

        $this->emailVerification->verifyAsync($email);
    }

    public static function invalidSingleEmailProvider(): array
    {
        return [
            'empty email' => ['', 'Email address is required.'],
            'email exceeds 320 chars' => [str_repeat('a', 312) . '@mail.com', 'Email address may not exceed 320 characters.'],
        ];
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
            'with queued result' => [
                'payload' => [
                    'results' => [EmailVerificationParams::QUEUED],
                ],
                'expected' => [
                    'page' => null,
                    'limit' => null,
                    'results' => [EmailVerificationParams::QUEUED],
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
