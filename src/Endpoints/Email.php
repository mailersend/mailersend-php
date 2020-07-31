<?php

namespace MailerSend\Endpoints;

use MailerSend\Exceptions\MailerSendApiHttpLayerException;
use Psr\Http\Client\ClientExceptionInterface;

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
    ) {
        $this->httpLayer->post();

        try {
            $response = $this->httpLayer->httpClient->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            throw new MailerSendApiHttpLayerException();
        }

        return $response;
    }
}