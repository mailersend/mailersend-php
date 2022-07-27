<?php

namespace MailerSend\Endpoints;

use Assert\Assertion;
use MailerSend\Helpers\Builder\Attachment;
use MailerSend\Helpers\Builder\EmailParams;
use MailerSend\Helpers\Builder\Personalization;
use MailerSend\Helpers\Builder\Recipient;
use MailerSend\Helpers\Builder\SmsParams;
use MailerSend\Helpers\Builder\SmsPersonalization;
use MailerSend\Helpers\Builder\Variable;
use MailerSend\Helpers\GeneralHelpers;
use Tightenco\Collect\Support\Collection;

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
