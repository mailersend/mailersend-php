<?php

namespace MailerSend\Endpoints;

use Assert\Assertion;
use JsonException;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Builder\WebhookParams;
use MailerSend\Helpers\GeneralHelpers;
use Psr\Http\Client\ClientExceptionInterface;

class Webhook extends AbstractEndpoint
{
    protected string $endpoint = 'webhooks';


    /**
     * @param WebhookParams $webhookParams
     * @return array
     * @throws MailerSendAssertException
     */
    public function create(WebhookParams $webhookParams): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::url($webhookParams->getUrl(), 'Invalid URL.') &&
                Assertion::minLength($webhookParams->getName(), 1, 'Webhook name is required.') &&
                Assertion::maxLength($webhookParams->getName(), 191, 'Webhook name cannot be longer than 191 character.') &&
                Assertion::minCount($webhookParams->getEvents(), 1, 'Webhook events are required.') &&
                Assertion::minLength($webhookParams->getDomainId(), 1, 'Webhook domain id is required.')
        );

        return $this->httpLayer->post(
            $this->buildUri($this->endpoint),
            array_filter($webhookParams->toArray())
        );
    }

    /**
     * @param string $id
     * @param string $url
     * @param string $name
     * @param array $events
     * @param bool|null $enabled
     * @param int|null $version
     * @return array
     * @throws ClientExceptionInterface
     * @throws JsonException
     * @throws MailerSendAssertException
     */
    public function update(
        string $id,
        string $url,
        string $name,
        array $events,
        ?bool $enabled = null,
        ?int $version = null
    ): array {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($id, 1, 'Webhook id is required.') &&
                Assertion::url($url, 'Invalid URL.') &&
                Assertion::minLength($name, 1, 'Webhook name is required.') &&
                Assertion::minCount($events, 1, 'Webhook events are required.') &&
                Assertion::allInArray($events, WebhookParams::ALL_ACTIVITIES, 'One or multiple invalid events.')
        );

        return $this->httpLayer->put(
            $this->buildUri($this->endpoint . '/' . $id),
            array_filter([
                'url' => $url,
                'name' => $name,
                'events' => $events,
                'enabled' => $enabled,
                'version' => $version,
            ], fn ($v) => $v !== null)
        );
    }

    /**
     * @param string $domainId
     * @param int|null $limit
     * @return array
     * @throws JsonException
     * @throws MailerSendAssertException
     * @throws ClientExceptionInterface
     */
    public function get(string $domainId, ?int $limit = null): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($domainId, 1, 'Domain id is required.')
        );

        return $this->httpLayer->get(
            $this->buildUri($this->endpoint),
            array_filter([
                'domain_id' => $domainId,
                'limit' => $limit,
            ])
        );
    }


    /**
     * @param string $id
     * @return array
     * @throws JsonException
     * @throws ClientExceptionInterface
     * @throws MailerSendAssertException
     */
    public function find(string $id): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($id, 1, 'Webhook id is required.')
        );

        return $this->httpLayer->get($this->buildUri($this->endpoint . '/' . $id));
    }


    /**
     * @param string $id
     * @return array
     * @throws JsonException
     * @throws ClientExceptionInterface
     * @throws MailerSendAssertException
     */
    public function delete(string $id): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($id, 1, 'Webhook id is required.')
        );

        return $this->httpLayer->delete($this->buildUri($this->endpoint . '/' . $id));
    }
}
