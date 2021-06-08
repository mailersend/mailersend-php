<?php

namespace MailerSend\Endpoints;

use Assert\Assertion;
use MailerSend\Helpers\Builder\Attachment;
use MailerSend\Helpers\Builder\EmailParams;
use MailerSend\Helpers\Builder\Recipient;
use MailerSend\Helpers\Builder\Variable;
use MailerSend\Helpers\GeneralHelpers;
use Tightenco\Collect\Support\Collection;

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
        GeneralHelpers::assert(fn () => Assertion::notEmpty(array_filter([
            $params->getTemplateId(), $params->getHtml(), $params->getText()
        ], fn ($v) => $v !== null), 'One of template_id, html or text must be supplied'));

        if (!$params->getTemplateId()) {
            GeneralHelpers::assert(
                fn () => Assertion::email($params->getFrom()) &&
                Assertion::minLength($params->getFromName(), 1) &&
                Assertion::minLength($params->getSubject(), 1) &&
                Assertion::minCount($params->getRecipients(), 1)
            );
        } else {
            GeneralHelpers::assert(fn () => Assertion::minCount($params->getRecipients(), 1));
        }

        $recipients_mapped = (new Collection($params->getRecipients()))->map(fn ($v) => is_object($v) && is_a(
            $v,
            Recipient::class
        ) ? $v->toArray() : $v)->toArray();
        $attachments_mapped = (new Collection($params->getAttachments()))->map(fn ($v) => is_object($v) && is_a(
            $v,
            Attachment::class
        ) ? $v->toArray() : $v)->toArray();
        $variables_mapped = (new Collection($params->getVariables()))->map(fn ($v) => is_object($v) && is_a(
            $v,
            Variable::class
        ) ? $v->toArray() : $v)->toArray();
        $personalization_mapped = (new Collection($params->getPersonalization()))->map(fn ($v) => is_object($v) && is_a(
            $v,
            \MailerSend\Helpers\Builder\Personalization::class
        ) ? $v->toArray() : $v)->toArray();

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
                  'subject' => $params->getSubject(),
                  'template_id' => $params->getTemplateId(),
                  'text' => $params->getText(),
                  'html' => $params->getHtml(),
                  'tags' => $params->getTags(),
                  'attachments' => $attachments_mapped,
                  'variables' => $variables_mapped,
                  'personalization' => $personalization_mapped
                ],
                fn ($v) => is_array($v) ? array_filter($v, fn ($v) => $v !== null) : $v !== null
            )
        );
    }
}
