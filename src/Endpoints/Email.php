<?php

namespace MailerSend\Endpoints;

use Assert\Assertion;
use MailerSend\Helpers\Builder\Attachment;
use MailerSend\Helpers\Builder\Recipient;
use MailerSend\Helpers\Builder\Variable;
use MailerSend\Helpers\GeneralHelpers;

class Email extends AbstractEndpoint
{
    protected string $endpoint = 'email';

    /**
     * @param  string  $from
     * @param  string  $from_name
     * @param  array  $recipients  Either an array of format [ name => email ] or [ email ] of recipients
     * @param  string  $subject
     * @param  string|null  $html
     * @param  string|null  $text
     * @param  string|null  $template_id
     * @param  array  $tags  array of strings
     * @param  array  $variables  array of arrays or \MailerSend\Helpers\Variables helper instances
     * @param  array  $attachments  array of arrays or \MailerSend\Helpers\Attachment helper instances
     * @return array
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \Assert\AssertionFailedException
     */
    public function send(
        string $from,
        string $from_name,
        array $recipients,
        string $subject,
        ?string $html = null,
        ?string $text = null,
        ?string $template_id = null,
        array $tags = [],
        array $variables = [],
        array $attachments = []
    ): array {
        GeneralHelpers::assert(fn() => Assertion::email($from) &&
            Assertion::minCount($recipients, 1) &&
            Assertion::minLength($from_name, 1) &&
            Assertion::minLength($subject, 1) &&
            Assertion::notEmpty(array_filter([$template_id, $html, $text], fn($v) => $v !== null),
                'One of template_id, html or text must be supplied')
        );

        $recipients_mapped = collect($recipients)->map(fn($v) => is_object($v) && is_a($v,
            Recipient::class) ? $v->toArray() : $v)->toArray();
        $attachments_mapped = collect($attachments)->map(fn($v) => is_object($v) && is_a($v,
            Attachment::class) ? $v->toArray() : $v)->toArray();
        $variables_mapped = collect($variables)->map(fn($v) => is_object($v) && is_a($v,
            Variable::class) ? $v->toArray() : $v)->toArray();

        return $this->httpLayer->post(
            $this->buildUri($this->endpoint),
            [
                'from' => [
                    'email' => $from,
                    'name' => $from_name
                ],
                'to' => $recipients_mapped,
                'subject' => $subject,
                'template_id' => $template_id,
                'text' => $text,
                'html' => $html,
                'tags' => $tags,
                'attachments' => $attachments_mapped,
                'variables' => $variables_mapped
            ]
        );
    }
}