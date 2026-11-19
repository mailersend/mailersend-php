<?php

namespace MailerSend\Tests;

use Http\Mock\Client;
use MailerSend\Common\HttpLayer;
use MailerSend\Helpers\Arr;
use MailerSend\Endpoints\Email;
use MailerSend\Exceptions\MailerSendException;
use MailerSend\MailerSend;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionClass;

class MailerSendTest extends TestCase
{
    public function test_should_fail_without_api_key(): void
    {
        $this->expectException(MailerSendException::class);

        new MailerSend();
    }

    public function test_should_have_email_endpoint_set(): void
    {
        $sdk = new MailerSend([
            'api_key' => 'test'
        ]);

        self::assertInstanceOf(Email::class, $sdk->email);
    }

    public function test_should_get_api_key_from_env(): void
    {
        putenv('MAILERSEND_API_KEY=test');

        $sdk = new MailerSend();

        $reflection = new ReflectionClass($sdk);
        $property = $reflection->getProperty('options');
        if (PHP_VERSION_ID < 80100) {
            $property->setAccessible(true);
        }

        self::assertEquals('test', Arr::get($property->getValue($sdk), 'api_key'));
    }

    public function test_should_override_api_key_if_provided(): void
    {
        putenv('MAILERSEND_API_KEY=test');

        $sdk = new MailerSend(['api_key' => 'key']);

        $reflection = new ReflectionClass($sdk);
        $property = $reflection->getProperty('options');
        if (PHP_VERSION_ID < 80100) {
            $property->setAccessible(true);
        }

        self::assertEquals('key', Arr::get($property->getValue($sdk), 'api_key'));
    }

    public function test_requests_use_the_default_api_version(): void
    {
        $this->requestWith(['api_key' => 'key']);

        $this->assertRequest('GET', '/2026-10/api-quota');
    }

    /**
     * @dataProvider apiVersions
     */
    #[DataProvider('apiVersions')]
    public function test_requests_use_the_given_api_version(string $apiVersion): void
    {
        $this->requestWith(['api_key' => 'key', 'api_version' => $apiVersion]);

        $this->assertRequest('GET', "/$apiVersion/api-quota");
    }

    public static function apiVersions(): array
    {
        return [
            'v1' => ['v1'],
            'a later dated version' => ['2027-01'],
        ];
    }

    public function test_a_custom_api_path_wins_over_the_api_version(): void
    {
        $this->requestWith(['api_key' => 'key', 'api_version' => '2027-01', 'api_path' => 'api/v1']);

        $this->assertRequest('GET', '/api/v1/api-quota');
    }

    /**
     * @dataProvider invalidApiVersions
     */
    #[DataProvider('invalidApiVersions')]
    public function test_should_fail_with_an_invalid_api_version($apiVersion): void
    {
        $this->expectException(MailerSendException::class);

        new MailerSend(['api_key' => 'key', 'api_version' => $apiVersion]);
    }

    public static function invalidApiVersions(): array
    {
        return [
            'month out of range' => ['2026-13'],
            'missing leading zero' => ['2026-1'],
            'not a version' => ['latest'],
            'not a string' => [202610],
        ];
    }

    private function requestWith(array $options): void
    {
        $this->client = new Client();
        $this->addSuccessResponse();

        $sdk = new MailerSend($options, new HttpLayer($options, $this->client));
        $sdk->apiQuota->get();
    }
}
