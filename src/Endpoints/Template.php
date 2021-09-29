<?php

namespace MailerSend\Endpoints;

use Assert\Assertion;
use MailerSend\Common\Constants;
use MailerSend\Helpers\GeneralHelpers;

class Template extends AbstractEndpoint
{
    protected string $endpoint = 'templates';

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     * @throws \JsonException
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
    public function find(string $templateId): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($templateId, 1, 'Template id is required.')
        );

        return $this->httpLayer->get(
            $this->buildUri("$this->endpoint/$templateId")
        );
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     */
    public function delete(string $templateId): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($templateId, 1, 'Template id is required.')
        );

        return $this->httpLayer->delete(
            $this->buildUri("$this->endpoint/$templateId")
        );
    }
}
