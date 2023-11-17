<?php

namespace MailerSend\Endpoints;

use Assert\Assertion;
use MailerSend\Common\Constants;
use MailerSend\Common\HttpLayer;
use MailerSend\Helpers\Builder\SuppressionParams;
use MailerSend\Helpers\GeneralHelpers;

class Suppression extends AbstractEndpoint
{
    protected string $endpoint;

    public function __construct(HttpLayer $httpLayer, array $options, string $endpoint)
    {
        $this->endpoint = $endpoint;
        parent::__construct($httpLayer, $options);
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     */
    public function getAll(?string $domainId = null, ?int $page = null, ?int $limit = Constants::DEFAULT_LIMIT): array
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
                'domain_id' => $domainId,
                'page' => $page,
                'limit' => $limit,
            ])
        );
    }

    /**
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function create(SuppressionParams $params): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::notEmpty($params->getRecipients(), 'Recipients is required.')
        );

        return $this->httpLayer->post(
            $this->buildUri($this->endpoint),
            $params->toArray(),
        );
    }

    /**
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function delete(?array $ids = null, bool $all = false, ?string $domainId = null): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::notEmpty(
                array_filter([$ids, $all], fn ($v) => $v !== null && !empty($v)),
                'Either ids or all must be provided.'
            )
        );

        return $this->httpLayer->delete(
            $this->buildUri($this->endpoint),
            array_filter([
                'domain_id' => $domainId,
                'ids' => $ids,
                'all' => $all,
            ], fn ($e) => !is_null($e))
        );
    }
}
