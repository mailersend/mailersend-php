<?php

namespace MailerSend\Endpoints;

use Assert\Assertion;
use MailerSend\Common\Constants;
use MailerSend\Common\HttpLayer;
use MailerSend\Helpers\GeneralHelpers;

abstract class Suppression extends AbstractEndpoint
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
    public function getAll(string $domainId, ?int $limit = Constants::DEFAULT_LIMIT): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($domainId, 1, 'Domain id is required.')
        );

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
                'limit' => $limit,
            ])
        );
    }

    abstract public function create(string $domainId, array $recipients);

    /**
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function delete(?array $ids = null, bool $all = false): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::notEmpty(
                array_filter([$ids, $all], fn ($v) => $v !== null && ! empty($v)),
                'Either ids or all must be provided.'
            )
        );

        return $this->httpLayer->delete(
            $this->buildUri($this->endpoint),
            [
                'ids' => $ids,
                'all' => $all,
            ]
        );
    }
}
