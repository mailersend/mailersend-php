<?php

namespace MailerSend\Helpers;

use Assert\AssertionFailedException;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Builder\EmailParams;
use MailerSend\Helpers\Builder\SmsParams;
use MailerSend\Helpers\MailerSendAssertion as Assertion;

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

    /**
     * @param EmailParams $params
     * @throws MailerSendAssertException
     */
    public static function validateEmailParams(EmailParams $params): void
    {
        self::assert(fn () => Assertion::notEmpty(array_filter([
            $params->getTemplateId(), $params->getText(), $params->getHtml()
        ], fn ($v) => $v !== null), 'One of template_id, html or text must be supplied'));

        if (!$params->getTemplateId()) {
            self::assert(
                fn () => Assertion::email($params->getFrom()) &&
                    Assertion::minLength($params->getFromName(), 1) &&
                    Assertion::minLength($params->getSubject(), 1) &&
                    Assertion::minCount($params->getRecipients(), 1)
            );
        } else {
            self::assert(fn () => Assertion::minCount($params->getRecipients(), 1));
        }

        self::assert(fn () => Assertion::maxCount($params->getRecipients(), 50, 'Recipients list should not contain more than 50 items.'));

        if ($params->getSubject() !== null) {
            self::assert(fn () => Assertion::maxLength($params->getSubject(), 998, 'Subject may not be greater than 998 characters.'));
        }

        if (count($params->getTags()) > 0) {
            self::assert(fn () => Assertion::maxCount($params->getTags(), 5, 'Tags list should not contain more than 5 items.'));
            foreach ($params->getTags() as $tag) {
                self::assert(fn () => Assertion::maxLength($tag, 191, 'Each tag may not be greater than 191 characters.'));
            }
        }

        if (is_int($params->getSendAt())) {
            $now = time();
            self::assert(fn () => Assertion::greaterOrEqualThan($params->getSendAt(), $now, 'Send at must not be in the past.'));
            self::assert(fn () => Assertion::lessOrEqualThan($params->getSendAt(), $now + 259200, 'Send at may not be more than 72 hours in the future.'));
        }

        if ($params->getInReplyToHeader() !== null) {
            self::assert(fn () => Assertion::maxLength($params->getInReplyToHeader(), 998, 'In reply to may not be greater than 998 characters.'));
        }

        if ($params->getListUnsubscribe() !== null) {
            self::assert(fn () => Assertion::maxLength($params->getListUnsubscribe(), 990, 'List unsubscribe may not be greater than 990 characters.'));
        }

        if (count($params->getCc()) > 0) {
            self::assert(fn () => Assertion::maxCount($params->getCc(), 10));
            foreach ($params->getCc() as $key => $cc) {
                $cc = !is_array($cc) ? $cc->toArray() : $cc;
                self::assert(
                    fn () => Assertion::keyExists($cc, 'email', "The element with index $key in CC array does not contain the email parameter.")
                );
                if (isset($cc['name'])) {
                    self::assert(fn () => Assertion::eq(1, count(explode(';', $cc['name']))));
                    self::assert(fn () => Assertion::eq(1, count(explode(',', $cc['name']))));
                }
            }
        }

        if (count($params->getBcc()) > 0) {
            self::assert(fn () => Assertion::maxCount($params->getBcc(), 10));
            foreach ($params->getBcc() as $key => $bcc) {
                $bcc = !is_array($bcc) ? $bcc->toArray() : $bcc;
                self::assert(
                    fn () => Assertion::keyExists($bcc, 'email', "The element with index $key in BCC array does not contain the email parameter.")
                );
                if (isset($bcc['name'])) {
                    self::assert(fn () => Assertion::eq(1, count(explode(';', $bcc['name']))));
                    self::assert(fn () => Assertion::eq(1, count(explode(',', $bcc['name']))));
                }
            }
        }
    }

    public static function validateSmsParams(SmsParams $params): void
    {
        self::assert(fn () => Assertion::notEmpty($params->getFrom(), 'From phone number is required'));
        self::assert(fn () => Assertion::startsWith($params->getFrom(), '+', 'From phone number must start with +'));
        self::assert(fn () => Assertion::notEmpty($params->getTo(), 'At least one recipient is required'));
        foreach ($params->getTo() as $recipient) {
            self::assert(fn () => Assertion::startsWith($recipient, '+', 'Recipient phone number must start with +'));
        }
        self::assert(fn () => Assertion::minLength($params->getText(), 1, 'Text cannot be empty'));
    }

    public static function mapToArray(array $data, string $object): array
    {
        return array_map(
            fn ($v) => is_object($v) && is_a($v, $object) ? $v->toArray() : $v,
            $data
        );
    }
}
