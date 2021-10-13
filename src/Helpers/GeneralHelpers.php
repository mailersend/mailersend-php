<?php

namespace MailerSend\Helpers;

use Assert\Assertion;
use Assert\AssertionFailedException;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Builder\EmailParams;
use Tightenco\Collect\Support\Collection;

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
            $params->getTemplateId(), $params->getText()
        ], fn ($v) => $v !== null), 'One of template_id or text must be supplied'));

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
                $cc = ! is_array($cc) ? $cc->toArray() : $cc;
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
                $bcc = ! is_array($bcc) ? $bcc->toArray() : $bcc;
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

    public static function mapToArray(array $data, string $object): array
    {
        return (new Collection($data))->map(fn ($v) => is_object($v) && is_a(
            $v,
            $object
        ) ? $v->toArray() : $v)->toArray();
    }
}
