<?php

namespace MailerSend\Endpoints;

use Assert\Assertion;
use MailerSend\Common\Constants;
use MailerSend\Helpers\Builder\SmtpUserParams;
use MailerSend\Helpers\GeneralHelpers;

class SmtpUser extends AbstractEndpoint
{
    protected string $endpoint = 'domains';

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     */
    public function getAll(string $domainId = null, ?int $limit = Constants::DEFAULT_LIMIT): array
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
            $this->buildUri("$this->endpoint/$domainId/smtp-users", ['limit' => $limit])
        );
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     */
    public function find(string $domainId, string $smtpUserId): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($domainId, 1, 'Domain id is required.')
        );

        GeneralHelpers::assert(
            fn () => Assertion::minLength($smtpUserId, 1, 'Smtp user id is required.')
        );

        return $this->httpLayer->get(
            $this->buildUri("$this->endpoint/$domainId/smtp-users/$smtpUserId")
        );
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function create(string $domainId, SmtpUserParams $params): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($domainId, 1, 'Domain id is required.')
        );

        return $this->httpLayer->post(
            $this->buildUri("$this->endpoint/$domainId/smtp-users"),
            $params->toArray(),
        );
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function update(string $domainId, string $smtpUserId, SmtpUserParams $params): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($domainId, 1, 'Domain id is required.')
        );

        GeneralHelpers::assert(
            fn () => Assertion::minLength($smtpUserId, 1, 'Smtp user id is required.')
        );

        return $this->httpLayer->put(
            $this->buildUri("$this->endpoint/$domainId/smtp-users/$smtpUserId"),
            $params->toArray(),
        );
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     */
    public function delete(string $domainId, string $smtpUserId): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($smtpUserId, 1, 'Smtp user id is required.')
        );

        GeneralHelpers::assert(
            fn () => Assertion::minLength($domainId, 1, 'Domain id is required.')
        );

        return $this->httpLayer->delete(
            $this->buildUri("$this->endpoint/$domainId/smtp-users/$smtpUserId")
        );
    }

}
