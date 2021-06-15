<?php

namespace MailerSend\Endpoints;

use Assert\Assertion;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\GeneralHelpers;

class Message extends AbstractEndpoint
{
    protected string $endpoint = 'messages';

    public const DEFAULT_LIMIT = 25;
    public const MAX_LIMIT = 100;
    public const MIN_LIMIT = 10;


    /**
     * @param int|null $limit
     * @param int|null $page
     * @return array
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function get(?int $limit = self::DEFAULT_LIMIT, ?int $page = null): array
    {
        if ($limit) {
            GeneralHelpers::assert(
                fn () => Assertion::min($limit, self::MIN_LIMIT, 'Minimum limit is ' . self::MIN_LIMIT . '.') &&
                    Assertion::max($limit, self::MAX_LIMIT, 'Maximum limit is ' . self::MAX_LIMIT . '.')
            );
        }

        return $this->httpLayer->get(
            $this->buildUri($this->endpoint),
            array_filter([
                'limit' => $limit,
                'page' => $page
            ])
        );
    }


    /**
     * @param string $id
     * @return array
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function find(string $id): array
    {
        return $this->httpLayer->get($this->buildUri($this->endpoint . '/' . $id));
    }
}
