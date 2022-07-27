<?php

namespace MailerSend\Endpoints;

use Assert\Assertion;
use MailerSend\Common\Constants;
use MailerSend\Helpers\Builder\SmsRecipientParams;
use MailerSend\Helpers\GeneralHelpers;

class SmsRecipient extends AbstractEndpoint
{
    protected string $endpoint = 'sms-recipients';

    public const DEFAULT_LIMIT = 25;
    public const MAX_LIMIT = 100;
    public const MIN_LIMIT = 10;

    /**
     * @param SmsRecipientParams $smsRecipientParams
     * @return array
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function getAll(SmsRecipientParams $smsRecipientParams): array
    {
        if ($limit = $smsRecipientParams->getLimit()) {
            GeneralHelpers::assert(
                fn () => Assertion::min($limit, self::MIN_LIMIT, 'Minimum limit is ' . self::MIN_LIMIT . '.') &&
                    Assertion::max($limit, self::MAX_LIMIT, 'Maximum limit is ' . self::MAX_LIMIT . '.')
            );
        }

        if ($smsNumberId = $smsRecipientParams->getSmsNumberId()) {
            GeneralHelpers::assert(
                fn () => Assertion::minLength($smsNumberId, 1, 'SMS number id cannot be empty.')
            );
        }

        if ($status = $smsRecipientParams->getStatus()) {
            GeneralHelpers::assert(
                fn () => Assertion::inArray($status, Constants::POSSIBLE_SMS_RECIPIENT_STATUSES),
            );
        }

        return $this->httpLayer->get($this->url($this->endpoint, $smsRecipientParams->toArray()));
    }

    /**
     * @param string $smsRecipientId
     * @return array
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function find(string $smsRecipientId): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($smsRecipientId, 1, 'SMS recipient id is required.')
        );

        return $this->httpLayer->get($this->url($this->endpoint . '/' . $smsRecipientId));
    }

    /**
     * @param string $smsRecipientId
     * @param string $status
     * @return array
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function update(string $smsRecipientId, string $status): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($smsRecipientId, 1, 'SMS number id cannot be empty.') &&
                Assertion::inArray($status, Constants::POSSIBLE_SMS_RECIPIENT_STATUSES)
        );

        return $this->httpLayer->put(
            $this->url($this->endpoint . '/' . $smsRecipientId),
            [
                'status' => $status,
            ]
        );
    }
}
