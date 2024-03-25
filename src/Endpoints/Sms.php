<?php

namespace MailerSend\Endpoints;

use MailerSend\Helpers\Builder\SmsParams;
use MailerSend\Helpers\Builder\SmsPersonalization;
use MailerSend\Helpers\GeneralHelpers;

class Sms extends AbstractEndpoint
{
    protected string $endpoint = 'sms';

    /**
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function send(SmsParams $params): array
    {
        GeneralHelpers::validateSmsParams($params);

        $personalization_mapped = GeneralHelpers::mapToArray($params->getPersonalization(), SmsPersonalization::class);

        return $this->httpLayer->post(
            $this->url($this->endpoint),
            array_filter(
                [
                    'from' => $params->getFrom(),
                    'to' => $params->getTo(),
                    'text' => $params->getText(),
                    'personalization' => $personalization_mapped,
                ],
                fn ($v) => is_array($v) ? array_filter($v, fn ($v) => $v !== null) : $v !== null
            )
        );
    }
}
