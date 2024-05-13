<?php

namespace MailerSend\Endpoints;

use Assert\Assertion;
use JsonException;
use MailerSend\Common\Constants;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Builder\TokenParams;
use MailerSend\Helpers\GeneralHelpers;
use Psr\Http\Client\ClientExceptionInterface;

class Token extends AbstractEndpoint
{
    protected string $endpoint = 'token';

    /**
     * @return array
     * @throws ClientExceptionInterface
     * @throws JsonException
     */
    public function getAll(?int $page = null, ?int $limit = Constants::DEFAULT_LIMIT): array
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
                'page' => $page,
                'limit' => $limit,
            ])
        );
    }


    /**
     * @param TokenParams $tokenParams
     * @return array
     * @throws JsonException
     * @throws MailerSendAssertException
     * @throws ClientExceptionInterface
     */
    public function create(TokenParams $tokenParams): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($tokenParams->getName(), 1, 'Token name is required.') &&
                Assertion::minLength($tokenParams->getDomainId(), 1, 'Token domain id is required.') &&
                Assertion::minCount($tokenParams->getScopes(), 1, 'Token scopes are required.')
        );

        return $this->httpLayer->post(
            $this->buildUri($this->endpoint),
            array_filter(
                [
                    'name' => $tokenParams->getName(),
                    'domain_id' => $tokenParams->getDomainId(),
                    'scopes' => $tokenParams->getScopes(),
                ],
            ),
        );
    }

    /**
     * @param string $id
     * @param string $status
     * @return array
     * @throws ClientExceptionInterface
     * @throws JsonException
     * @throws MailerSendAssertException
     */
    public function update(string $id, string $status): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::notEmpty($id, 'Token id is required.') &&
                Assertion::inArray($status, TokenParams::STATUS_ALL)
        );

        return $this->httpLayer->put(
            $this->buildUri($this->endpoint . '/' . $id . '/settings'),
            array_filter(
                [
                    'status' => $status,
                ],
            ),
        );
    }

    /**
     * @param string $id
     * @param string $name
     * @return array
     * @throws ClientExceptionInterface
     * @throws JsonException
     * @throws MailerSendAssertException
     */
    public function changeName(string $id, string $name): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::notEmpty($id, 'Token id is required.') &&
                Assertion::notEmpty($name, 'Token name is required.')
        );

        return $this->httpLayer->put(
            $this->buildUri($this->endpoint . '/' . $id . ''),
            array_filter(
                [
                    'name' => $name,
                ],
            ),
        );
    }

    /**
     * @param string $id
     * @return array
     * @throws ClientExceptionInterface
     * @throws JsonException
     * @throws MailerSendAssertException
     */
    public function delete(string $id): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::notEmpty($id, 'Token id is required.')
        );

        return $this->httpLayer->delete(
            $this->buildUri($this->endpoint . '/' . $id),
            []
        );
    }

    /**
     * @param string $id
     * @return array
     * @throws ClientExceptionInterface
     * @throws JsonException
     * @throws MailerSendAssertException
     */
    public function find(string $id): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::notEmpty($id, 'Token id is required.')
        );

        return $this->httpLayer->get(
            $this->buildUri($this->endpoint . '/' . $id),
            []
        );
    }
}
