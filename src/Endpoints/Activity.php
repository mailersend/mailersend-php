<?php

namespace MailerSend\Endpoints;

use Assert\Assertion;
use MailerSend\Common\Constants;
use MailerSend\Helpers\Builder\ActivityParams;
use MailerSend\Helpers\GeneralHelpers;

class Activity extends AbstractEndpoint
{
    protected string $endpoint = 'activity';

    /**
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function getAll(string $domainId, ActivityParams $activityParams): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($domainId, 1, 'Domain id is required.')
        );

        if ($activityParams->getLimit()) {
            GeneralHelpers::assert(
                fn () => Assertion::range(
                    $activityParams->getLimit(),
                    Constants::MIN_LIMIT,
                    Constants::MAX_LIMIT,
                    'Limit is supposed to be between' . Constants::MIN_LIMIT . ' and ' . Constants::MAX_LIMIT . '.'
                )
            );
        }

        if ($activityParams->getDateFrom() && $activityParams->getDateTo()) {
            GeneralHelpers::assert(
                fn () => Assertion::greaterThan($activityParams->getDateTo(), $activityParams->getDateFrom())
            );
        }

        if (!empty($activityParams->getEvent())) {
            $diff = array_diff($activityParams->getEvent(), Constants::POSSIBLE_EVENT_TYPES);
            GeneralHelpers::assert(
                fn () => Assertion::count($diff, 0, 'The following types are invalid: ' . implode(', ', $diff))
            );
        }


        return $this->httpLayer->get($this->url("$this->endpoint/$domainId", $activityParams->toArray()));
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     */
    public function find(string $activityId): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($activityId, 1, 'Activity id is required.')
        );

        return $this->httpLayer->get(
            $this->buildUri("activities/$activityId")
        );
    }
}
