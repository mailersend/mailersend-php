<?php

namespace MailerSend\Endpoints;

use Assert\Assertion;
use MailerSend\Helpers\GeneralHelpers;
use MailerSend\Helpers\Builder\UserParams;

class User extends AbstractEndpoint
{
    protected string $endpoint = 'users';

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     */
    public function getAll(): array
    {
        return $this->httpLayer->get(
            $this->buildUri($this->endpoint)
        );
    }


    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     */
    public function find(string $userId): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($userId, 1, 'User id is required.')
        );

        return $this->httpLayer->get(
            $this->buildUri("$this->endpoint/$userId")
        );
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function create(UserParams $params): array
    {
        return $this->httpLayer->post(
            $this->buildUri($this->endpoint),
            $params->toArray(),
        );
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function update(string $userId, UserParams $params): array
    {
        return $this->httpLayer->put(
            $this->buildUri("$this->endpoint/$userId"),
            $params->toArray(),
        );
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     */
    public function delete(string $userId): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($userId, 1, 'User id is required.')
        );

        return $this->httpLayer->delete(
            $this->buildUri("$this->endpoint/$userId")
        );
    }
}
