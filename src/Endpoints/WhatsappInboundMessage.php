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

        if ($whatsappInboundMessageParams->getLimit()) {
            GeneralHelpers::assert(
                fn () => Assertion::range(
                    $whatsappInboundMessageParams->getLimit(),
                    Constants::MIN_LIMIT,
                    Constants::MAX_LIMIT,
                    'Limit is supposed to be between ' . Constants::MIN_LIMIT . ' and ' . Constants::MAX_LIMIT . '.'
                )
            );
        }

        if ($whatsappInboundMessageParams->getDateFrom() && $whatsappInboundMessageParams->getDateTo()) {
            GeneralHelpers::assert(
                fn () => Assertion::greaterThan(
                    $whatsappInboundMessageParams->getDateTo(),
                    $whatsappInboundMessageParams->getDateFrom()
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
