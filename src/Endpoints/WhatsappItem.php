<?php

namespace MailerSend\Endpoints;

use Assert\Assertion;
use MailerSend\Common\Constants;
use MailerSend\Helpers\Builder\WhatsappItemParams;
use MailerSend\Helpers\GeneralHelpers;

class WhatsappItem extends AbstractEndpoint
{
    protected string $endpoint = 'whatsapp/items';

    /**
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function getAll(WhatsappItemParams $whatsappItemParams): array
    {
        if ($whatsappItemParams->getWhatsappAccountId() !== null) {
            GeneralHelpers::assert(
                fn () => Assertion::minLength(
                    $whatsappItemParams->getWhatsappAccountId(),
                    1,
                    'WhatsApp account id cannot be empty.'
                )
            );
        }

        if ($whatsappItemParams->getLimit() !== null) {
            GeneralHelpers::assert(
                fn () => Assertion::range(
                    $whatsappItemParams->getLimit(),
                    Constants::WHATSAPP_MIN_LIMIT,
                    Constants::MAX_LIMIT,
                    'Limit is supposed to be between ' . Constants::WHATSAPP_MIN_LIMIT . ' and ' . Constants::MAX_LIMIT . '.'
                )
            );
        }

        if ($whatsappItemParams->getPage() !== null) {
            GeneralHelpers::assert(
                fn () => Assertion::range(
                    $whatsappItemParams->getPage(),
                    1,
                    Constants::WHATSAPP_MAX_PAGE,
                    'Page is supposed to be between 1 and ' . Constants::WHATSAPP_MAX_PAGE . '.'
                )
            );
        }

        if (is_numeric($whatsappItemParams->getDateFrom()) && is_numeric($whatsappItemParams->getDateTo())) {
            GeneralHelpers::assert(
                fn () => Assertion::greaterThan(
                    $whatsappItemParams->getDateTo(),
                    $whatsappItemParams->getDateFrom(),
                    'Date to must be greater than date from.'
                )
            );
        }

        if (!empty($whatsappItemParams->getStatus())) {
            $diff = array_diff($whatsappItemParams->getStatus(), Constants::POSSIBLE_WHATSAPP_ITEM_STATUSES);
            GeneralHelpers::assert(
                fn () => Assertion::count($diff, 0, 'The following statuses are invalid: ' . implode(', ', $diff))
            );
        }

        return $this->httpLayer->get($this->url($this->endpoint, $whatsappItemParams->toArray()));
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     */
    public function find(string $whatsappItemId): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($whatsappItemId, 1, 'WhatsApp item id is required.')
        );

        return $this->httpLayer->get(
            $this->url("$this->endpoint/$whatsappItemId")
        );
    }
}
