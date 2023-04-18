<?php

namespace MailerSend\Endpoints;

class ApiQuota extends AbstractEndpoint
{
    protected string $endpoint = 'api-quota';

    public function get(): array
    {
        return $this->httpLayer->get(
            $this->buildUri($this->endpoint)
        );
    }
}
