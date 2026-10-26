<?php

namespace MailerSend\Endpoints;

use Assert\Assertion;
use MailerSend\Common\Constants;
use MailerSend\Helpers\Builder\WhatsappTemplateParams;
use MailerSend\Helpers\GeneralHelpers;

class WhatsappTemplate extends AbstractEndpoint
{
    protected string $endpoint = 'whatsapp/templates';

    /**
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function getAll(WhatsappTemplateParams $whatsappTemplateParams): array
    {
        if ($whatsappTemplateParams->getWhatsappAccountId() !== null) {
            GeneralHelpers::assert(
                fn () => Assertion::minLength(
                    $whatsappTemplateParams->getWhatsappAccountId(),
                    1,
                    'WhatsApp account id cannot be empty.'
                )
            );
        }

        if ($whatsappTemplateParams->getLimit() !== null) {
            GeneralHelpers::assert(
                fn () => Assertion::range(
                    $whatsappTemplateParams->getLimit(),
                    Constants::WHATSAPP_MIN_LIMIT,
                    Constants::MAX_LIMIT,
                    'Limit is supposed to be between ' . Constants::WHATSAPP_MIN_LIMIT . ' and ' . Constants::MAX_LIMIT . '.'
                )
            );
        }

        if ($whatsappTemplateParams->getPage() !== null) {
            GeneralHelpers::assert(
                fn () => Assertion::range(
                    $whatsappTemplateParams->getPage(),
                    1,
                    Constants::WHATSAPP_MAX_PAGE,
                    'Page is supposed to be between 1 and ' . Constants::WHATSAPP_MAX_PAGE . '.'
                )
            );
        }

        if ($status = $whatsappTemplateParams->getStatus()) {
            GeneralHelpers::assert(
                fn () => Assertion::inArray($status, Constants::POSSIBLE_WHATSAPP_TEMPLATE_STATUSES),
            );
        }

        if ($category = $whatsappTemplateParams->getCategory()) {
            GeneralHelpers::assert(
                fn () => Assertion::inArray($category, Constants::POSSIBLE_WHATSAPP_TEMPLATE_CATEGORIES),
            );
        }

        return $this->httpLayer->get($this->url($this->endpoint, $whatsappTemplateParams->toArray()));
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     */
    public function find(string $whatsappTemplateId): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($whatsappTemplateId, 1, 'WhatsApp template id is required.')
        );

        return $this->httpLayer->get(
            $this->url("$this->endpoint/$whatsappTemplateId")
        );
    }
}
