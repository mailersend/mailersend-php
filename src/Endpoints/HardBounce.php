<?php

namespace MailerSend\Endpoints;

use Assert\Assertion;
use MailerSend\Common\HttpLayer;
use MailerSend\Helpers\GeneralHelpers;

class HardBounce extends Suppression
{
    public function __construct(HttpLayer $httpLayer, array $options)
    {
        $endpoint = 'suppressions/hard-bounces';
        parent::__construct($httpLayer, $options, $endpoint);
    }

    /**
     * @throws \JsonException
     * @throws \MailerSend\Exceptions\MailerSendAssertException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function create(string $domainId, array $recipients): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($domainId, 1, 'Domain id is required.')
            && Assertion::notEmpty($recipients, 'Recipients is required.')
        );

        return $this->httpLayer->post(
            $this->buildUri($this->endpoint),
            [
                'domain_id' => $domainId,
                'recipients' => $recipients,
            ]
        );
    }
}
