<?php

namespace MailerSend\Endpoints;

use Assert\Assertion;
use MailerSend\Common\Constants;
use MailerSend\Helpers\GeneralHelpers;

class Invite extends AbstractEndpoint
{
    protected string $endpoint = 'invites';

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     */
    public function getAll(?int $page = null, ?int $limit = Constants::DEFAULT_LIMIT): array
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
            $this->buildUri("$this->endpoint/$inviteId")
        );
    }
}
