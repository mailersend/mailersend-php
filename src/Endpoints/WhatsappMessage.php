<?php

namespace MailerSend\Endpoints;

use Assert\Assertion;
use MailerSend\Common\Constants;
use MailerSend\Helpers\Builder\WhatsappMessageParams;
use MailerSend\Helpers\GeneralHelpers;

class WhatsappMessage extends AbstractEndpoint
{
    protected string $endpoint = 'whatsapp/messages';

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     * @throws \JsonException
     */
    public function getAll(WhatsappMessageParams $whatsappMessageParams): array
    {
        if ($whatsappMessageParams->getWhatsappAccountId() !== null) {
            GeneralHelpers::assert(
                fn () => Assertion::minLength(
                    $whatsappMessageParams->getWhatsappAccountId(),
                    1,
                    'WhatsApp account id cannot be empty.'
                )
            );
        }

        if ($whatsappMessageParams->getLimit() !== null) {
            GeneralHelpers::assert(
                fn () => Assertion::range(
                    $whatsappMessageParams->getLimit(),
                    Constants::WHATSAPP_MIN_LIMIT,
                    Constants::MAX_LIMIT,
                    'Limit is supposed to be between ' . Constants::WHATSAPP_MIN_LIMIT . ' and ' . Constants::MAX_LIMIT . '.'
                )
            );
        }

        if ($whatsappMessageParams->getPage() !== null) {
            GeneralHelpers::assert(
                fn () => Assertion::range(
                    $whatsappMessageParams->getPage(),
                    1,
                    Constants::WHATSAPP_MAX_PAGE,
                    'Page is supposed to be between 1 and ' . Constants::WHATSAPP_MAX_PAGE . '.'
                )
            );
        }

        if (is_numeric($whatsappMessageParams->getDateFrom()) && is_numeric($whatsappMessageParams->getDateTo())) {
            GeneralHelpers::assert(
                fn () => Assertion::greaterThan(
                    $whatsappMessageParams->getDateTo(),
                    $whatsappMessageParams->getDateFrom(),
                    'Date to must be greater than date from.'
                )
            );
        }

        return $this->httpLayer->get($this->url($this->endpoint, $whatsappMessageParams->toArray()));
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     */
    public function find(string $whatsappMessageId): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($whatsappMessageId, 1, 'WhatsApp message id is required.')
        );

        return $this->httpLayer->get(
            $this->url("$this->endpoint/$whatsappMessageId")
        );
    }
}
