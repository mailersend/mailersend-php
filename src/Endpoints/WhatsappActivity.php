<?php

namespace MailerSend\Endpoints;

use Assert\Assertion;
use MailerSend\Common\Constants;
use MailerSend\Helpers\Builder\WhatsappActivityParams;
use MailerSend\Helpers\GeneralHelpers;

class WhatsappActivity extends AbstractEndpoint
{
    protected string $endpoint = 'whatsapp/activities';

    /**
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function getAll(WhatsappActivityParams $whatsappActivityParams): array
    {
        if ($whatsappActivityParams->getLimit() !== null) {
            GeneralHelpers::assert(
                fn () => Assertion::range(
                    $whatsappActivityParams->getLimit(),
                    Constants::WHATSAPP_MIN_LIMIT,
                    Constants::MAX_LIMIT,
                    'Limit is supposed to be between ' . Constants::WHATSAPP_MIN_LIMIT . ' and ' . Constants::MAX_LIMIT . '.'
                )
            );
        }

        if ($whatsappActivityParams->getPage() !== null) {
            GeneralHelpers::assert(
                fn () => Assertion::range(
                    $whatsappActivityParams->getPage(),
                    1,
                    Constants::WHATSAPP_MAX_PAGE,
                    'Page is supposed to be between 1 and ' . Constants::WHATSAPP_MAX_PAGE . '.'
                )
            );
        }

        if (is_numeric($whatsappActivityParams->getDateFrom()) && is_numeric($whatsappActivityParams->getDateTo())) {
            GeneralHelpers::assert(
                fn () => Assertion::greaterThan(
                    $whatsappActivityParams->getDateTo(),
                    $whatsappActivityParams->getDateFrom(),
                    'Date to must be greater than date from.'
                )
            );
        }

        if (!empty($whatsappActivityParams->getType())) {
            $diff = array_diff($whatsappActivityParams->getType(), Constants::POSSIBLE_WHATSAPP_ACTIVITY_TYPES);
            GeneralHelpers::assert(
                fn () => Assertion::count($diff, 0, 'The following types are invalid: ' . implode(', ', $diff))
            );
        }

        return $this->httpLayer->get($this->url($this->endpoint, $whatsappActivityParams->toArray()));
    }
}
