<?php

namespace MailerSend\Helpers;

use Assert\AssertionFailedException;
use MailerSend\Exceptions\MailerSendAssertException;

class GeneralHelpers
{
    /**
     * @throws MailerSendAssertException
     * @noinspection PhpRedundantCatchClauseInspection
     */
    public static function assert(callable $assertions): void
    {
        try {
            $assertions();
        } catch (AssertionFailedException $e) {
            throw new MailerSendAssertException($e->getMessage());
        }
    }
}
