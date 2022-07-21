<?php

namespace MailerSend\Endpoints;

use Assert\Assertion;
use MailerSend\Common\Constants;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Builder\DomainParams;
use MailerSend\Helpers\Builder\DomainSettingsParams;
use MailerSend\Helpers\Builder\WebhookParams;
use MailerSend\Helpers\GeneralHelpers;

class SmsNumber extends AbstractEndpoint
{
    protected string $endpoint = 'sms-numbers';

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     * @throws \JsonException
     */
    public function getAll(?int $page = null, ?int $limit = Constants::DEFAULT_LIMIT, ?bool $paused = null): array
    {
        if ($limit) {
            GeneralHelpers::assert(
                fn () => Assertion::range(
                    $limit,
                    Constants::MIN_LIMIT,
                    Constants::MAX_LIMIT,
                    'Limit is supposed to be between ' . Constants::MIN_LIMIT . ' and ' . Constants::MAX_LIMIT .  '.'
                )
            );
        }

        return $this->httpLayer->get(
            $this->buildUri($this->endpoint, [
                'page' => $page,
                'limit' => $limit,
                'paused' => $paused,
            ])
        );
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     */
    public function find(string $smsNumberId): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($smsNumberId, 1, 'SMS number id is required.')
        );

        return $this->httpLayer->get(
            $this->buildUri("$this->endpoint/$smsNumberId")
        );
    }

    public function update(string $smsNumberId, bool $paused): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($smsNumberId, 1, 'SMS number id is required.')
        );

        return $this->httpLayer->put(
            $this->buildUri($this->endpoint . '/' . $smsNumberId),
            [
                'paused' => $paused,
            ]
        );
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     */
    public function delete(string $smsNumberId): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($smsNumberId, 1, 'Sms number id is required.')
        );

        return $this->httpLayer->delete(
            $this->buildUri("$this->endpoint/$smsNumberId")
        );
    }
}
