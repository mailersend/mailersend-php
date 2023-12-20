<?php

namespace MailerSend\Endpoints;

use MailerSend\Helpers\Builder\Attachment;
use MailerSend\Helpers\Builder\EmailParams;
use MailerSend\Helpers\Builder\Personalization;
use MailerSend\Helpers\Builder\Recipient;
use MailerSend\Helpers\Builder\Variable;
use MailerSend\Helpers\GeneralHelpers;

class Email extends AbstractEndpoint
{
    protected string $endpoint = 'email';

    /**
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function send(EmailParams $params): array
    {
        GeneralHelpers::validateEmailParams($params);

        $recipients_mapped = GeneralHelpers::mapToArray($params->getRecipients(), Recipient::class);
        $cc_mapped = GeneralHelpers::mapToArray($params->getCc(), Recipient::class);
        $bcc_mapped = GeneralHelpers::mapToArray($params->getBcc(), Recipient::class);
        $attachments_mapped = GeneralHelpers::mapToArray($params->getAttachments(), Attachment::class);
        $variables_mapped = GeneralHelpers::mapToArray($params->getVariables(), Variable::class);
        $personalization_mapped = GeneralHelpers::mapToArray($params->getPersonalization(), Personalization::class);

        return $this->httpLayer->post(
            $this->buildUri($this->endpoint),
            array_filter(
                [
                    'from' => [
                        'email' => $params->getFrom(),
                        'name' => $params->getFromName(),
                        ],
                    'reply_to' => [
                        'email' => $params->getReplyTo(),
                        'name' => $params->getReplyToName(),
                        ],
                    'to' => $recipients_mapped,
                    'cc' => $cc_mapped,
                    'bcc' => $bcc_mapped,
                    'subject' => $params->getSubject(),
                    'template_id' => $params->getTemplateId(),
                    'text' => $params->getText(),
                    'html' => $params->getHtml(),
                    'tags' => $params->getTags(),
                    'attachments' => $attachments_mapped,
                    'variables' => $variables_mapped,
                    'personalization' => $personalization_mapped,
                    'send_at' => $params->getSendAt(),
                    'precedence_bulk' => $params->getPrecedenceBulkHeader(),
                    'in_reply_to' => $params->getInReplyToHeader(),
                    'settings' => [
                        'track_clicks' => $params->trackClicks(),
                        'track_opens' => $params->trackOpens(),
                        'track_content' => $params->trackContent(),
                    ],
                    'headers' => $params->getHeaders(),
                ],
                fn ($v) => is_array($v) ? array_filter($v, fn ($v) => $v !== null) : $v !== null
            )
        );
    }
}
