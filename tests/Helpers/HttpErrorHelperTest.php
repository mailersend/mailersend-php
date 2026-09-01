<?php

namespace MailerSend\Tests\Helpers;

use Http\Mock\Client;
use MailerSend\Common\HttpLayer;
use MailerSend\Exceptions\MailerSendHttpException;
use MailerSend\Exceptions\MailerSendRateLimitException;
use MailerSend\Exceptions\MailerSendValidationException;
use MailerSend\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class HttpErrorHelperTest extends TestCase
{
    protected HttpLayer $httpLayer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = new Client();
        $this->httpLayer = new HttpLayer(['api_key' => 'test'], $this->client);
    }

    public function test_422_throws_validation_exception(): void
    {
        $this->expectException(MailerSendValidationException::class);
        $this->expectExceptionMessage('Validation Error');

        $this->addErrorResponse(422, 'Validation Error');

        $this->httpLayer->get('https://api.mailersend.com/v1/test');
    }

    public function test_422_exception_carries_message(): void
    {
        $this->addErrorResponse(422, 'Validation Error');

        try {
            $this->httpLayer->get('https://api.mailersend.com/v1/test');
            self::fail('Expected MailerSendValidationException');
        } catch (MailerSendValidationException $e) {
            self::assertEquals('Validation Error', $e->getMessage());
            self::assertEquals(422, $e->getStatusCode());
        }
    }

    public function test_429_throws_rate_limit_exception(): void
    {
        $this->expectException(MailerSendRateLimitException::class);

        $this->addErrorResponse(429, 'Too Many Attempts');

        $this->httpLayer->get('https://api.mailersend.com/v1/test');
    }

    /**
     * @dataProvider otherErrorStatusCodesProvider
     */
    #[DataProvider('otherErrorStatusCodesProvider')]
    public function test_other_error_codes_throw_http_exception(int $statusCode): void
    {
        $this->expectException(MailerSendHttpException::class);

        $this->addErrorResponse($statusCode, 'Error');

        $this->httpLayer->get('https://api.mailersend.com/v1/test');
    }

    public static function otherErrorStatusCodesProvider(): array
    {
        return [
            '400 Bad Request'           => [400],
            '401 Unauthorized'          => [401],
            '403 Forbidden'             => [403],
            '404 Not Found'             => [404],
            '500 Internal Server Error' => [500],
            '503 Service Unavailable'   => [503],
        ];
    }

    /**
     * @dataProvider successStatusCodesProvider
     */
    #[DataProvider('successStatusCodesProvider')]
    public function test_success_codes_do_not_throw(int $statusCode): void
    {
        $this->addSuccessResponse($statusCode);

        // No exception expected — method must return without throwing.
        $this->httpLayer->get('https://api.mailersend.com/v1/test');

        self::assertTrue(true); // confirm execution reached this point
    }

    public static function successStatusCodesProvider(): array
    {
        return [
            '200 OK'         => [200],
            '201 Created'    => [201],
            '204 No Content' => [204],
        ];
    }
}
