<?php

namespace MailerSend\Helpers;

use Assert\Assertion;
use Assert\AssertionFailedException;
use Illuminate\Support\Collection;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Builder\EmailParams;
use MailerSend\Helpers\Builder\SmsParams;

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
        return (new Collection($data))->map(fn ($v) => is_object($v) && is_a(
            $v,
            $object
        ) ? $v->toArray() : $v)->toArray();
    }
}
