<?php

namespace MailerSend\Tests\Helpers\Builder;

use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Builder\Recipient;
use MailerSend\Tests\TestCase;

class RecipientTest extends TestCase
{
    public function test_rejects_invalid_email(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Value "emailmailersend.com" was expected to be a valid e-mail address.');

        new Recipient('emailmailersend.com', 'Recipient');
    }
}
