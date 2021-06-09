<?php

namespace MailerSend\Endpoints;

use Assert\Assertion;
use MailerSend\Helpers\Builder\DomainParams;
use MailerSend\Helpers\GeneralHelpers;

class Domain extends AbstractEndpoint
{
    protected string $base_endpoint = 'domains';

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     * @throws \JsonException
     */
    public function domainList(DomainParams $domainParams): array
    {
        if ($domainParams->getLimit()) {
            GeneralHelpers::assert(
                fn () => Assertion::range($domainParams->getLimit(), 10, 100, 'Limit is supposed to be between 10 and 100.')
            );
        }

        return $this->httpLayer->get(
            $this->buildUri($this->base_endpoint, $domainParams->toArray())
        );
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function domain(string $domainId): array
    {
        return $this->httpLayer->get(
            $this->buildUri($this->base_endpoint . "/$domainId")
        );
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function deleteDomain(string $domainId): array
    {
        return $this->httpLayer->delete(
            $this->buildUri($this->base_endpoint . "/$domainId")
        );
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     * @throws \JsonException
     */
    public function recipients(string $domainId, DomainParams $domainParams): array
    {
        if ($domainParams->getLimit()) {
            GeneralHelpers::assert(
                fn () => Assertion::range($domainParams->getLimit(), 10, 100, 'Limit is supposed to be between 10 and 100.')
            );
        }

        return $this->httpLayer->get(
            $this->buildUri($this->base_endpoint . "/$domainId/recipients", $domainParams->toArray())
        );
    }
}
