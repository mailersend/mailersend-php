<?php

namespace MailerSend\Endpoints;

use Assert\Assertion;
use MailerSend\Helpers\Builder\ActivityParams;
use MailerSend\Helpers\GeneralHelpers;

class Activity extends AbstractEndpoint
{
    /**
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function activityList(string $domainId, ActivityParams $activityParams)
    {
        if ($activityParams->getLimit()) {
            GeneralHelpers::assert(
                fn () => Assertion::range($activityParams->getLimit(), 10, 100, 'Limit is supposed to be between 10 and 100.')
            );
        }

        if ($activityParams->getDateFrom() && $activityParams->getDateTo()) {
            GeneralHelpers::assert(
                fn () => Assertion::greaterThan($activityParams->getDateTo(), $activityParams->getDateFrom())
            );
        }

        if (! empty($activityParams->getEvent())) {
            $possibleEventTypes = ['processed', 'queued', 'sent', 'delivered', 'soft_bounced', 'hard_bounced', 'junk', 'opened', 'clicked', 'unsubscribed', 'spam_complaints'];
            $diff = array_diff($activityParams->getEvent(), $possibleEventTypes);
            GeneralHelpers::assert(
                fn () => Assertion::count($diff, 0, 'The following types are invalid: ' . implode(', ', $diff))
            );
        }


        return $this->httpLayer->get($this->buildUri("activity/$domainId", $activityParams->toArray()));
    }
}
