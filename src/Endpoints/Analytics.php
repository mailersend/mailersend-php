<?php

namespace MailerSend\Endpoints;

use Assert\Assertion;
use MailerSend\Common\Constants;
use MailerSend\Helpers\Builder\ActivityAnalyticsParams;
use MailerSend\Helpers\Builder\OpensAnalyticsParams;
use MailerSend\Helpers\GeneralHelpers;

class Analytics extends AbstractEndpoint
{
    protected string $endpoint = 'analytics';

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     * @throws \JsonException
     */
    public function activityDataByDate(ActivityAnalyticsParams $activityAnalyticsParams): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::notEmpty(
                array_filter(
                    [ $activityAnalyticsParams->getEvent()],
                    fn ($v) => $v !== null && $v !== []
                ),
                'The event[] is a required parameter.'
            )
        );

        GeneralHelpers::assert(
            fn () => Assertion::greaterThan(
                $activityAnalyticsParams->getDateTo(),
                $activityAnalyticsParams->getDateFrom(),
                'The parameter date_to must be greater than date_from.'
            )
        );
        $diff = array_diff($activityAnalyticsParams->getEvent(), Constants::POSSIBLE_EVENT_TYPES);
        GeneralHelpers::assert(
            fn () => Assertion::count($diff, 0, 'The following types are invalid: ' . implode(', ', $diff))
        );

        if ($activityAnalyticsParams->getGroupBy()) {
            GeneralHelpers::assert(
                fn () => Assertion::inArray($activityAnalyticsParams->getGroupBy(), Constants::POSSIBLE_GROUP_BY_OPTIONS),
            );
        }

        return $this->httpLayer->get(
            $this->url("$this->endpoint/date", $activityAnalyticsParams->toArray())
        );
    }

    public function opensByCountry(OpensAnalyticsParams $opensAnalyticsParams): array
    {
        return $this->callOpensEndpoint("$this->endpoint/country", $opensAnalyticsParams);
    }

    public function opensByUserAgentName(OpensAnalyticsParams $opensAnalyticsParams): array
    {
        return $this->callOpensEndpoint("$this->endpoint/ua-name", $opensAnalyticsParams);
    }

    public function opensByReadingEnvironment(OpensAnalyticsParams $opensAnalyticsParams): array
    {
        return $this->callOpensEndpoint("$this->endpoint/ua-type", $opensAnalyticsParams);
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     * @throws \JsonException
     */
    protected function callOpensEndpoint(string $path, OpensAnalyticsParams $opensAnalyticsParams): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::greaterThan(
                $opensAnalyticsParams->getDateTo(),
                $opensAnalyticsParams->getDateFrom(),
                'The parameter date_to must be greater than date_from.'
            )
        );

        return $this->httpLayer->get(
            $this->url($path, $opensAnalyticsParams->toArray())
        );
    }
}
