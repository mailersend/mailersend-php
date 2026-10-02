<?php

namespace MailerSend\Endpoints;

use Assert\Assertion;
use MailerSend\Common\Constants;
use MailerSend\Helpers\Builder\WhatsappRecipientParams;
use MailerSend\Helpers\GeneralHelpers;

class WhatsappRecipient extends AbstractEndpoint
{
    protected string $endpoint = 'whatsapp/recipients';

    /**
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function getAll(WhatsappRecipientParams $whatsappRecipientParams): array
    {
        if ($limit = $whatsappRecipientParams->getLimit()) {
            GeneralHelpers::assert(
                fn () => Assertion::range(
                    $limit,
                    Constants::MIN_LIMIT,
                    Constants::MAX_LIMIT,
                    'Limit is supposed to be between ' . Constants::MIN_LIMIT . ' and ' . Constants::MAX_LIMIT . '.'
                )
            );
        }

        if ($status = $whatsappRecipientParams->getStatus()) {
            GeneralHelpers::assert(
                fn () => Assertion::inArray($status, Constants::POSSIBLE_WHATSAPP_RECIPIENT_STATUSES),
            );
        }

        return $this->httpLayer->get($this->url($this->endpoint, $whatsappRecipientParams->toArray()));
    }

    /**
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function find(string $whatsappRecipientId): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($whatsappRecipientId, 1, 'WhatsApp recipient id is required.')
        );

        return $this->httpLayer->get($this->url("$this->endpoint/$whatsappRecipientId"));
    }
}
