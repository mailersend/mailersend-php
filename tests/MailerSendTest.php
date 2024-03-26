<?php

namespace MailerSend\Tests;

use Illuminate\Support\Arr;
use MailerSend\Endpoints\Email;
use MailerSend\Exceptions\MailerSendException;
use MailerSend\MailerSend;
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
        $property->setAccessible(true);

        self::assertEquals('test', Arr::get($property->getValue($sdk), 'api_key'));
    }

    public function test_should_override_api_key_if_provided(): void
    {
        putenv('MAILERSEND_API_KEY=test');

        $sdk = new MailerSend(['api_key' => 'key']);

        $reflection = new ReflectionClass($sdk);
        $property = $reflection->getProperty('options');
        $property->setAccessible(true);

        self::assertEquals('key', Arr::get($property->getValue($sdk), 'api_key'));
    }
}
