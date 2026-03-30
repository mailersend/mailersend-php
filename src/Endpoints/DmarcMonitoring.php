<?php

namespace MailerSend\Endpoints;

use Assert\Assertion;
use MailerSend\Common\Constants;
use MailerSend\Helpers\Builder\DmarcMonitoringParams;
use MailerSend\Helpers\Builder\DmarcMonitoringUpdateParams;
use MailerSend\Helpers\GeneralHelpers;

class DmarcMonitoring extends AbstractEndpoint
{
    protected string $endpoint = 'dmarc-monitoring';

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     * @throws \JsonException
     */
    public function getAll(?int $page = null, ?int $limit = Constants::DEFAULT_LIMIT): array
    {
        if ($limit) {
            GeneralHelpers::assert(
                fn () => Assertion::range(
                    $limit,
                    Constants::MIN_LIMIT,
                    Constants::MAX_LIMIT,
                    'Limit is supposed to be between ' . Constants::MIN_LIMIT . ' and ' . Constants::MAX_LIMIT . '.'
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
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     * @throws \JsonException
     */
    public function create(DmarcMonitoringParams $params): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($params->getDomainId(), 1, 'Domain id is required.')
        );

        return $this->httpLayer->post(
            $this->buildUri($this->endpoint),
            $params->toArray()
        );
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     * @throws \JsonException
     */
    public function update(string $monitorId, DmarcMonitoringUpdateParams $params): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($monitorId, 1, 'Monitor id is required.')
        );

        GeneralHelpers::assert(
            fn () => Assertion::minLength($params->getWantedDmarcRecord(), 1, 'Wanted DMARC record is required.')
        );

        return $this->httpLayer->put(
            $this->buildUri("$this->endpoint/$monitorId"),
            $params->toArray()
        );
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     * @throws \JsonException
     */
    public function delete(string $monitorId): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($monitorId, 1, 'Monitor id is required.')
        );

        return $this->httpLayer->delete(
            $this->buildUri("$this->endpoint/$monitorId")
        );
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     * @throws \JsonException
     */
    public function getAggregatedReports(string $monitorId, ?int $page = null, ?int $limit = Constants::DEFAULT_LIMIT): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($monitorId, 1, 'Monitor id is required.')
        );

        if ($limit) {
            GeneralHelpers::assert(
                fn () => Assertion::range(
                    $limit,
                    Constants::MIN_LIMIT,
                    Constants::MAX_LIMIT,
                    'Limit is supposed to be between ' . Constants::MIN_LIMIT . ' and ' . Constants::MAX_LIMIT . '.'
                )
            );
        }

        return $this->httpLayer->get(
            $this->buildUri("$this->endpoint/$monitorId/report", [
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
    public function getIpReports(string $monitorId, string $ip, ?int $page = null, ?int $limit = Constants::DEFAULT_LIMIT): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($monitorId, 1, 'Monitor id is required.')
        );

        GeneralHelpers::assert(
            fn () => Assertion::minLength($ip, 1, 'IP address is required.')
        );

        if ($limit) {
            GeneralHelpers::assert(
                fn () => Assertion::range(
                    $limit,
                    Constants::MIN_LIMIT,
                    Constants::MAX_LIMIT,
                    'Limit is supposed to be between ' . Constants::MIN_LIMIT . ' and ' . Constants::MAX_LIMIT . '.'
                )
            );
        }

        return $this->httpLayer->get(
            $this->buildUri("$this->endpoint/$monitorId/report/$ip", [
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
    public function getReportSources(string $monitorId): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($monitorId, 1, 'Monitor id is required.')
        );

        return $this->httpLayer->get(
            $this->buildUri("$this->endpoint/$monitorId/report-sources")
        );
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     * @throws \JsonException
     */
    public function markIpAsFavorite(string $monitorId, string $ip): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($monitorId, 1, 'Monitor id is required.')
        );

        GeneralHelpers::assert(
            fn () => Assertion::minLength($ip, 1, 'IP address is required.')
        );

        return $this->httpLayer->put(
            $this->buildUri("$this->endpoint/$monitorId/favorite/$ip"),
            []
        );
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     * @throws \JsonException
     */
    public function removeIpFromFavorites(string $monitorId, string $ip): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($monitorId, 1, 'Monitor id is required.')
        );

        GeneralHelpers::assert(
            fn () => Assertion::minLength($ip, 1, 'IP address is required.')
        );

        return $this->httpLayer->delete(
            $this->buildUri("$this->endpoint/$monitorId/favorite/$ip")
        );
    }
}
