<?php

namespace MailerSend\Endpoints;

use Assert\Assertion;
use MailerSend\Common\Constants;
use MailerSend\Helpers\Builder\EmailVerificationParams;
use MailerSend\Helpers\GeneralHelpers;

class EmailVerification extends AbstractEndpoint
{
    protected string $endpoint = 'email-verification';

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
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     * @throws \JsonException
     */
    public function find(string $emailVerificationId): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($emailVerificationId, 1, 'Email Verification id is required.')
        );

        return $this->httpLayer->get(
            $this->buildUri("$this->endpoint/$emailVerificationId")
        );
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     */
    public function create(EmailVerificationParams $params): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($params->getName(), 1, 'Email Verification name is required.')
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
    public function verify(string $emailVerificationId): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($emailVerificationId, 1, 'Email Verification id is required.')
        );

        return $this->httpLayer->get(
            $this->buildUri("$this->endpoint/$emailVerificationId/verify")
        );
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     * @throws \JsonException
     */
    public function getResults(
        string $emailVerificationId,
        ?int $page = null,
        ?int $limit = Constants::DEFAULT_LIMIT,
        array $results = []
    ): array {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($emailVerificationId, 1, 'Email Verification id is required.')
        );

        if (!empty($results)) {
            GeneralHelpers::assert(
                fn () => Assertion::allInArray($results, EmailVerificationParams::POSSIBLE_RESULTS)
            );
        }

        return $this->httpLayer->get(
            $this->buildUri("$this->endpoint/$emailVerificationId/results", [
                'page' => $page,
                'limit' => $limit,
                'results' => $results,
            ])
        );
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     */
    public function verifyEmail(string $email): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($email, 1, 'Email address is required.')
        );

        return $this->httpLayer->post(
            $this->buildUri("{$this->endpoint}/verify"),
            ['email' => $email]
        );
    }
}
