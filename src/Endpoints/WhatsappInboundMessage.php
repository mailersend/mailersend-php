<?php

namespace MailerSend\Endpoints;

use Assert\Assertion;
use MailerSend\Common\Constants;
use MailerSend\Helpers\Builder\WhatsappInboundMessageParams;
use MailerSend\Helpers\GeneralHelpers;

class WhatsappInboundMessage extends AbstractEndpoint
{
    protected string $endpoint = 'whatsapp/inbound-messages';

    /**
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function getAll(WhatsappInboundMessageParams $whatsappInboundMessageParams): array
    {
        if ($whatsappInboundMessageParams->getWhatsappAccountId() !== null) {
            GeneralHelpers::assert(
                fn () => Assertion::minLength(
                    $whatsappInboundMessageParams->getWhatsappAccountId(),
                    1,
                    'WhatsApp account id cannot be empty.'
                )
            );
        }

        if ($whatsappInboundMessageParams->getLimit() !== null) {
            GeneralHelpers::assert(
                fn () => Assertion::range(
                    $whatsappInboundMessageParams->getLimit(),
                    Constants::WHATSAPP_MIN_LIMIT,
                    Constants::MAX_LIMIT,
                    'Limit is supposed to be between ' . Constants::WHATSAPP_MIN_LIMIT . ' and ' . Constants::MAX_LIMIT . '.'
                )
            );
        }

        if ($whatsappInboundMessageParams->getPage() !== null) {
            GeneralHelpers::assert(
                fn () => Assertion::range(
                    $whatsappInboundMessageParams->getPage(),
                    1,
                    Constants::WHATSAPP_MAX_PAGE,
                    'Page is supposed to be between 1 and ' . Constants::WHATSAPP_MAX_PAGE . '.'
                )
            );
        }

        if (is_numeric($whatsappInboundMessageParams->getDateFrom()) && is_numeric($whatsappInboundMessageParams->getDateTo())) {
            GeneralHelpers::assert(
                fn () => Assertion::greaterThan(
                    $whatsappInboundMessageParams->getDateTo(),
                    $whatsappInboundMessageParams->getDateFrom(),
                    'Date to must be greater than date from.'
                )
            );
        }

        if (!empty($whatsappInboundMessageParams->getType())) {
            $diff = array_diff($whatsappInboundMessageParams->getType(), Constants::POSSIBLE_WHATSAPP_INBOUND_MESSAGE_TYPES);
            GeneralHelpers::assert(
                fn () => Assertion::count($diff, 0, 'The following types are invalid: ' . implode(', ', $diff))
            );
        }

        return $this->httpLayer->get($this->url($this->endpoint, $whatsappInboundMessageParams->toArray()));
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     */
    public function find(string $whatsappInboundMessageId): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($whatsappInboundMessageId, 1, 'WhatsApp inbound message id is required.')
        );

        return $this->httpLayer->get(
            $this->url("$this->endpoint/$whatsappInboundMessageId")
        );
    }
}
