<?php

namespace MailerSend\Endpoints;

use Assert\Assertion;
use MailerSend\Common\Constants;
use MailerSend\Helpers\Builder\Inbound as InboundBuilder;
use MailerSend\Helpers\GeneralHelpers;

class Inbound extends AbstractEndpoint
{
    protected string $endpoint = 'inbound';

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     */
    public function getAll(?string $domainId = null, ?int $page = null, ?int $limit = Constants::DEFAULT_LIMIT): array
    {
        if ($limit) {
            GeneralHelpers::assert(
                fn () => Assertion::range(
                    $limit,
                    Constants::MIN_LIMIT,
                    Constants::MAX_LIMIT,
                    'Limit is supposed to be between ' . Constants::MIN_LIMIT . ' and ' . Constants::MAX_LIMIT .  '.'
                )
            );
        }

        return $this->httpLayer->get(
            $this->buildUri($this->endpoint, [
                'domain_id' => $domainId,
                'page' => $page,
                'limit' => $limit,
            ])
        );
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     */
    public function find(string $inboundId): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($inboundId, 1, 'Inbound id is required.')
        );

        return $this->httpLayer->get(
            $this->buildUri("$this->endpoint/$inboundId")
        );
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function create(InboundBuilder $params): array
    {
        return $this->httpLayer->post(
            $this->buildUri($this->endpoint),
            $params->toArray(),
        );
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function update(string $inboundId, InboundBuilder $params): array
    {
        return $this->httpLayer->put(
            $this->buildUri("$this->endpoint/$inboundId"),
            $params->toArray(),
        );
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     */
    public function delete(string $inboundId): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($inboundId, 1, 'Inbound id is required.')
        );

        return $this->httpLayer->delete(
            $this->buildUri("$this->endpoint/$inboundId")
        );
    }
}
