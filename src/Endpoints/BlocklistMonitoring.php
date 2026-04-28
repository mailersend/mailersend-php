<?php

namespace MailerSend\Endpoints;

use Assert\Assertion;
use MailerSend\Common\Constants;
use MailerSend\Helpers\Builder\BlocklistMonitoringParams;
use MailerSend\Helpers\Builder\BlocklistMonitoringUpdateParams;
use MailerSend\Helpers\GeneralHelpers;

class BlocklistMonitoring extends AbstractEndpoint
{
    protected string $endpoint = 'blocklist-monitoring';

    public const POSSIBLE_SORT_BY = ['name', 'address', 'created_at', 'updated_at', 'blocklisted'];
    public const POSSIBLE_ORDER = ['asc', 'desc'];

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
    public function find(string $monitorId): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($monitorId, 1, 'Monitor id is required.')
        );

        return $this->httpLayer->get(
            $this->buildUri("$this->endpoint/$monitorId")
        );
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     * @throws \JsonException
     */
    public function create(BlocklistMonitoringParams $params): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($params->getAddress(), 1, 'Address is required.')
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
    public function update(string $monitorId, BlocklistMonitoringUpdateParams $params): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($monitorId, 1, 'Monitor id is required.')
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
}
