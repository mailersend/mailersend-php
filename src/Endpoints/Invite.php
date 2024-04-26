<?php

namespace MailerSend\Endpoints;

use Assert\Assertion;
use MailerSend\Helpers\GeneralHelpers;

class Invite extends AbstractEndpoint
{
    protected string $endpoint = 'invites';

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     */
    public function getAll(): array
    {
        return $this->httpLayer->get(
            $this->buildUri($this->endpoint)
        );
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     */
    public function find(string $inviteId): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($inviteId, 1, 'Invite id is required.')
        );

        return $this->httpLayer->get(
            $this->buildUri("$this->endpoint/$inviteId")
        );
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function resend(string $inviteId): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($inviteId, 1, 'Invite id is required.')
        );

        return $this->httpLayer->post(
            $this->buildUri("$this->endpoint/$inviteId/resend"),
            []
        );
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     */
    public function cancel(string $inviteId): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($inviteId, 1, 'Invite id is required.')
        );

        return $this->httpLayer->delete(
            $this->buildUri("$this->endpoint/$inviteId/cancel")
        );
    }
}
