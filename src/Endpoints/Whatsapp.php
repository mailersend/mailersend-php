<?php

namespace MailerSend\Endpoints;

use MailerSend\Helpers\Builder\WhatsappParams;
use MailerSend\Helpers\Builder\WhatsappPersonalization;
use MailerSend\Helpers\GeneralHelpers;

class Whatsapp extends AbstractEndpoint
{
    protected string $endpoint = 'whatsapp';

    /**
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function send(WhatsappParams $params): array
    {
        GeneralHelpers::validateWhatsappParams($params);

        $personalization_mapped = GeneralHelpers::mapToArray($params->getPersonalization(), WhatsappPersonalization::class);

        return $this->httpLayer->post(
            $this->url($this->endpoint . '/send'),
            array_filter(
                [
                    'from' => $params->getFrom(),
                    'to' => $params->getTo(),
                    'template_id' => $params->getTemplateId(),
                    'personalization' => $personalization_mapped ?: null,
                ],
                fn ($v) => $v !== null
            )
        );
    }
}