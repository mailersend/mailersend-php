<?php

namespace MailerSend\Endpoints;

use Assert\Assertion;
use MailerSend\Common\Constants;
use MailerSend\Helpers\GeneralHelpers;

class ScheduleMessages extends AbstractEndpoint
{
    protected string $endpoint = 'message-schedules';

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     */
    public function getAll(?string $domainId = null, ?string $status = null, ?int $page = null, ?int $limit = Constants::DEFAULT_LIMIT): array
    {
        if ($status) {
            GeneralHelpers::assert(
                fn () => Assertion::inArray(
                    $status,
                    Constants::SCHEDULED_MESSAGES_STATUSES,
                    'The status provided is invalid.'
                )
            );
        }

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
                'status' => $status,
                'page' => $page,
                'limit' => $limit,
            ])
        );
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     * @throws \JsonException
     */
    public function find(string $messageId): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($messageId, 1, 'Message id is required.')
        );

        return $this->httpLayer->get(
            $this->buildUri("$this->endpoint/$messageId")
        );
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     * @throws \JsonException
     */
    public function delete(string $messageId): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($messageId, 1, 'Message id is required.')
        );

        return $this->httpLayer->delete(
            $this->buildUri("$this->endpoint/$messageId"),
        );
    }
}
