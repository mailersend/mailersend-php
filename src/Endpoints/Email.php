<?php

namespace MailerSend\Endpoints;

class Email extends AbstractEndpoint
{
    protected string $endpoint = 'email';

    public function send(
        string $from,
        string $from_name,
        array $to,
        string $subject,
        string $html = null,
        string $text = null
    ): array {
        return $this->httpLayer->post($this->buildUri(), [
            'from' => [
                'email' => $from,
                'name' => $from_name
            ],
            'to' => $to,
            'subject' => $subject,
            'text' => $text,
            'html' => $html,
        ]);
    }
}