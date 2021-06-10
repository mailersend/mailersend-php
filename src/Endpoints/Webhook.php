<?php

namespace MailerSend\Endpoints;

use Assert\Assertion;
use MailerSend\Helpers\Builder\WebhookParams;
use MailerSend\Helpers\GeneralHelpers;

class Webhook extends AbstractEndpoint
{
    protected string $endpoint = 'webhooks';

    /**
     * @param string $domainId
     * @return array
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     */
    public function get(string $domainId): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($domainId, 1, 'Domain id is required.')
        );

        return $this->httpLayer->get(
            $this->buildUri($this->endpoint),
            array_filter([
                'domain_id' => $domainId
            ])
        );
    }


    /**
     * @param string $id
     * @return array
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     */
    public function find(string $id): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($id, 1, 'Webhook id is required.')
        );

        return $this->httpLayer->get(
            $this->buildUri($this->endpoint . '/' . $id),
            []
        );
    }


    /**
     * @param string $id
     * @return array
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     */
    public function delete(string $id): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($id, 1, 'Webhook id is required.')
        );

        return $this->httpLayer->delete(
            $this->buildUri($this->endpoint . '/' . $id),
            []
        );
    }
}
