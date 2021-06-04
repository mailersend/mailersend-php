<?php

namespace MailerSend\Tests;

use MailerSend\Endpoints\Email;
use MailerSend\Exceptions\MailerSendException;
use MailerSend\MailerSend;

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
}
