<?php

namespace MailerSend\Endpoints;

use Assert\Assertion;
use MailerSend\Common\Constants;
use MailerSend\Common\Roles;
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
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     */
    public function create(UserParams $params): array
    {
        if ($params->getRole() === Roles::CUSTOM_USER) {
            GeneralHelpers::assert(
                fn () => Assertion::notEmpty($params->getPermissions(), 'Permissions are required for Custom User role.')
            );
        }

        return $this->httpLayer->post(
            $this->buildUri($this->endpoint),
            $params->toArray(),
        );
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     */
    public function update(string $userId, UserParams $params): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($userId, 1, 'User id is required.')
        );

        if ($params->getRole() === Roles::CUSTOM_USER) {
            GeneralHelpers::assert(
                fn () => Assertion::notEmpty($params->getPermissions(), 'Permissions are required for Custom User role.')
            );
        }

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
