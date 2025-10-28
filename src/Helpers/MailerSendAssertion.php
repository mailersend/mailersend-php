<?php

namespace MailerSend\Helpers;

use Assert\Assertion;
use Assert\AssertionFailedException;

class MailerSendAssertion extends Assertion
{
    /**
     * Assert that value is a valid email address (supports Unicode/IDN)
     *
     * @param mixed $value
     * @param string|callable|null $message
     * @param string|null $propertyPath
     * @return bool
     * @throws AssertionFailedException
     */
    public static function email($value, $message = null, string $propertyPath = null): bool
    {
        if (filter_var($value, FILTER_VALIDATE_EMAIL, FILTER_FLAG_EMAIL_UNICODE) === false) {
            $message = sprintf(
                static::generateMessage($message ?: 'Value "%s" was expected to be a valid e-mail address.'),
                static::stringify($value)
            );

            throw static::createException($value, $message, static::INVALID_EMAIL, $propertyPath);
        }

        return true;
    }
}
