<?php

namespace MailerSend\Endpoints;

use Assert\Assertion;
use MailerSend\Helpers\Builder\Attachment;
use MailerSend\Helpers\Builder\Personalization;
use MailerSend\Helpers\Builder\Recipient;
use MailerSend\Helpers\Builder\Variable;
use MailerSend\Helpers\GeneralHelpers;

class BulkEmail extends AbstractEndpoint
{
    protected string $endpoint = 'bulk-email';

    /**
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function send(array $bulkParams): array
    {
        GeneralHelpers::assert(fn () => Assertion::minCount($bulkParams, 1, 'Bulk params should contain at least 1 email'));

        $requestData = [];

        foreach ($bulkParams as $params) {
            GeneralHelpers::validateEmailParams($params);

            $recipients_mapped = GeneralHelpers::mapToArray($params->getRecipients(), Recipient::class);
            $cc_mapped = GeneralHelpers::mapToArray($params->getCc(), Recipient::class);
            $bcc_mapped = GeneralHelpers::mapToArray($params->getBcc(), Recipient::class);
            $attachments_mapped = GeneralHelpers::mapToArray($params->getAttachments(), Attachment::class);
            $variables_mapped = GeneralHelpers::mapToArray($params->getVariables(), Variable::class);
            $personalization_mapped = GeneralHelpers::mapToArray($params->getPersonalization(), Personalization::class);

            $requestData[] = array_filter(
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
                ],
                fn ($v) => is_array($v) ? array_filter($v, fn ($v) => $v !== null) : $v !== null
            );
        }

        return $this->httpLayer->post(
            $this->buildUri($this->endpoint),
            $requestData
        );
    }

    /**
     * @param string $bulkEmailId
     * @return array
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function getStatus(string $bulkEmailId): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($bulkEmailId, 1, 'Bulk email id is required.')
        );

        return $this->httpLayer->get(
            $this->buildUri("$this->endpoint/$bulkEmailId")
        );
    }
}
