<?php

namespace MailerSend\Endpoints;

use Assert\Assertion;
use MailerSend\Helpers\Builder\ActivityAnalyticsParams;
use MailerSend\Helpers\GeneralHelpers;

class Analytics extends AbstractEndpoint
{
    public function activityDataByDate(ActivityAnalyticsParams $activityAnalyticsParams): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::notEmpty(
                array_filter(
                    [ $activityAnalyticsParams->getEvent()],
                    fn ($v) => $v !== null && $v !== []
                ),
                'The date_from, date_to and event[] are required parameters'
            )
        );

        GeneralHelpers::assert(
            fn () => Assertion::greaterThan(
                $activityAnalyticsParams->getDateTo(),
                $activityAnalyticsParams->getDateFrom(),
                'The parameter date_to must be greater than date_from.'
            )
        );

        $possibleEventTypes = ['processed', 'queued', 'sent', 'delivered', 'soft_bounced', 'hard_bounced', 'junk', 'opened', 'clicked', 'unsubscribed', 'spam_complaints'];
        $diff = array_diff($activityAnalyticsParams->getEvent(), $possibleEventTypes);
        GeneralHelpers::assert(
            fn () => Assertion::count($diff, 0, 'The following types are invalid: ' . implode(', ', $diff))
        );

        if ($activityAnalyticsParams->getGroupBy()) {
            $possibleOptions = ['days', 'weeks', 'months', 'years'];
            GeneralHelpers::assert(
                fn () => Assertion::inArray($activityAnalyticsParams->getGroupBy(), $possibleOptions),
            );
        }

        return $this->httpLayer->get(
            $this->buildUri('analytics/date', $activityAnalyticsParams->toArray())
        );
    }
}
