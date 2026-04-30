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

    public const POSSIBLE_SORT_BY = ['created_at', 'updated_at', 'dmarc_valid', 'spf_status'];
    public const POSSIBLE_ORDER = ['asc', 'desc'];
    public const POSSIBLE_REPORT_SOURCE_STATUS = ['accepted', 'rejected', 'quarantined'];

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     * @throws \JsonException
     */
    public function getAll(
        ?int $page = null,
        ?int $limit = Constants::DEFAULT_LIMIT,
        ?string $query = null,
        ?string $sortBy = null,
        ?string $order = null
    ): array {
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

        if ($sortBy) {
            GeneralHelpers::assert(
                fn () => Assertion::inArray(
                    $sortBy,
                    self::POSSIBLE_SORT_BY,
                    'sort_by must be one of: ' . implode(', ', self::POSSIBLE_SORT_BY) . '.'
                )
            );
        }

        if ($order) {
            GeneralHelpers::assert(
                fn () => Assertion::inArray($order, self::POSSIBLE_ORDER, 'order must be asc or desc.')
            );
        }

        return $this->httpLayer->get(
            $this->buildUri($this->endpoint, array_filter([
                'page' => $page,
                'limit' => $limit,
                'query' => $query,
                'sort_by' => $sortBy,
                'order' => $order,
            ], fn ($v) => $v !== null))
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
    public function getAggregatedReports(
        string $monitorId,
        ?int $page = null,
        ?int $limit = Constants::DEFAULT_LIMIT,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?string $search = null,
        ?string $category = null,
        ?string $reportSource = null
    ): array {
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
            $this->buildUri("$this->endpoint/$monitorId/report", array_filter([
                'page' => $page,
                'limit' => $limit,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'search' => $search,
                'category' => $category,
                'report_source' => $reportSource,
            ], fn ($v) => $v !== null))
        );
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     * @throws \JsonException
     */
    public function getIpReports(
        string $monitorId,
        string $ip,
        ?int $page = null,
        ?int $limit = Constants::DEFAULT_LIMIT,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?string $search = null,
        ?string $category = null,
        ?string $reportSource = null
    ): array {
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
            $this->buildUri("$this->endpoint/$monitorId/report/$ip", array_filter([
                'page' => $page,
                'limit' => $limit,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'search' => $search,
                'category' => $category,
                'report_source' => $reportSource,
            ], fn ($v) => $v !== null))
        );
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     * @throws \JsonException
     */
    public function getReportSources(
        string $monitorId,
        string $dateFrom,
        string $dateTo,
        ?string $status = null
    ): array {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($monitorId, 1, 'Monitor id is required.')
        );

        GeneralHelpers::assert(
            fn () => Assertion::minLength($dateFrom, 1, 'date_from is required.')
        );

        GeneralHelpers::assert(
            fn () => Assertion::minLength($dateTo, 1, 'date_to is required.')
        );

        if ($status) {
            GeneralHelpers::assert(
                fn () => Assertion::inArray(
                    $status,
                    self::POSSIBLE_REPORT_SOURCE_STATUS,
                    'status must be one of: ' . implode(', ', self::POSSIBLE_REPORT_SOURCE_STATUS) . '.'
                )
            );
        }

        return $this->httpLayer->get(
            $this->buildUri("$this->endpoint/$monitorId/report-sources", array_filter([
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'status' => $status,
            ], fn ($v) => $v !== null))
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
