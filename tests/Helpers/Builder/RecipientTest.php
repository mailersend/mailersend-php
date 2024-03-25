<?php

namespace MailerSend\Tests\Helpers\Builder;

use Illuminate\Support\Arr;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Builder\Recipient;
use MailerSend\Tests\TestCase;

class RecipientTest extends TestCase
{
    public function test_properly_sets_recipient_params(): void
    {
        $recipient = (new Recipient('email@mailersend.com', 'Recipient'))->toArray();

        self::assertEquals('email@mailersend.com', Arr::get($recipient, 'email'));
        self::assertEquals('Recipient', Arr::get($recipient, 'name'));
    }

    public function test_recipient_validates_email(): void
    {
        $this->expectException(MailerSendAssertException::class);

        (new Recipient('emailmailersend.com', 'Recipient'));
    }
}
