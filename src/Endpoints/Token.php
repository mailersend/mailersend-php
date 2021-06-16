<?php

namespace MailerSend\Endpoints;

use Assert\Assertion;
use JsonException;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Builder\TokenParams;
use MailerSend\Helpers\GeneralHelpers;
use Psr\Http\Client\ClientExceptionInterface;

class Token extends AbstractEndpoint
{
    protected string $endpoint = 'token';


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
}
