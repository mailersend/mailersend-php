<?php

namespace MailerSend\Endpoints;

class Account extends AbstractEndpoint
{
    protected string $endpoint = 'account';

    public function get(): array
    {
        return $this->httpLayer->get(
            $this->buildUri($this->endpoint)
        );
    }
}
