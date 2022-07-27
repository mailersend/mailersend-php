<?php

namespace MailerSend\Endpoints;

use Assert\Assertion;
use MailerSend\Common\Constants;
use MailerSend\Helpers\Builder\SmsInbound as SmsInboundBuilder;
use MailerSend\Helpers\GeneralHelpers;

class SmsInbound extends AbstractEndpoint
{
    protected string $endpoint = 'sms-inbounds';

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     */
    public function getAll(?string $smsNumberId = null, ?bool $enabled = null, ?int $page = null, ?int $limit = Constants::DEFAULT_LIMIT): array
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
            $this->url($this->endpoint, [
                'sms_number_id' => $smsNumberId,
                'enabled' => $enabled,
                'page' => $page,
                'limit' => $limit,
            ])
        );
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     */
    public function find(string $smsInboundId): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($smsInboundId, 1, 'SMS inbound id is required.')
        );

        return $this->httpLayer->get(
            $this->url("$this->endpoint/$smsInboundId")
        );
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function create(SmsInboundBuilder $params): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($params->getSmsNumberId(), 1, 'SMS number id is required.') &&
                Assertion::minLength($params->getName(), 1, 'SMS inbound name is required') &&
                Assertion::url($params->getForwardUrl(), 'Invalid URL.')
        );

        return $this->httpLayer->post(
            $this->url($this->endpoint),
            array_filter($params->toArray(), function ($value) {
                return !is_null($value);
            }),
        );
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function update(string $smsInboundId, SmsInboundBuilder $params): array
    {
        return $this->httpLayer->put(
            $this->url("$this->endpoint/$smsInboundId"),
            array_filter($params->toArray(), function ($value) {
                return !is_null($value);
            }),
        );
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     */
    public function delete(string $smsInboundId): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($smsInboundId, 1, 'SMS inbound id is required.')
        );

        return $this->httpLayer->delete(
            $this->url("$this->endpoint/$smsInboundId")
        );
    }
}
