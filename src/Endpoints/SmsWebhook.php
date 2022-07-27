<?php

namespace MailerSend\Endpoints;

use Assert\Assertion;
use JsonException;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Builder\SmsWebhookParams;
use MailerSend\Helpers\GeneralHelpers;
use Psr\Http\Client\ClientExceptionInterface;

class SmsWebhook extends AbstractEndpoint
{
    protected string $endpoint = 'sms-webhooks';

    /**
     * @param SmsWebhookParams $smsWebhookParams
     * @return array
     * @throws MailerSendAssertException
     */
    public function create(SmsWebhookParams $smsWebhookParams): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::url($smsWebhookParams->getUrl(), 'Invalid URL.') &&
                Assertion::minLength($smsWebhookParams->getName(), 1, 'Webhook name is required.') &&
                Assertion::maxLength($smsWebhookParams->getName(), 191, 'Webhook name cannot be longer than 191 character.') &&
                Assertion::minCount($smsWebhookParams->getEvents(), 1, 'Webhook events are required.') &&
                Assertion::minLength($smsWebhookParams->getSmsNumberId(), 1, 'SMS number id is required.')
        );

        return $this->httpLayer->post(
            $this->url($this->endpoint),
            array_filter($smsWebhookParams->toArray(), function ($value) {
                return !is_null($value);
            })
        );
    }

    /**
     * @param string $smsWebhookId
     * @param string $url
     * @param string $name
     * @param array $events
     * @param bool|null $enabled
     * @return array
     * @throws ClientExceptionInterface
     * @throws JsonException
     * @throws MailerSendAssertException
     */
    public function update(string $smsWebhookId, SmsWebhookParams $smsWebhookParams): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($smsWebhookId, 1, 'SMS webhook id is required.')
        );

        return $this->httpLayer->put(
            $this->url($this->endpoint . '/' . $smsWebhookId),
            array_filter($smsWebhookParams->toArray(), function ($value) {
                return !is_null($value);
            })
        );
    }

    /**
     * @param string $smsNumberId
     * @return array
     * @throws JsonException
     * @throws MailerSendAssertException
     * @throws ClientExceptionInterface
     */
    public function get(string $smsNumberId): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($smsNumberId, 1, 'SMS number id is required.')
        );

        return $this->httpLayer->get(
            $this->url($this->endpoint),
            [
                'sms_number_id' => $smsNumberId
            ]
        );
    }


    /**
     * @param string $smsWebhookId
     * @return array
     * @throws JsonException
     * @throws ClientExceptionInterface
     * @throws MailerSendAssertException
     */
    public function find(string $smsWebhookId): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($smsWebhookId, 1, 'SMS webhook id is required.')
        );

        return $this->httpLayer->get($this->url($this->endpoint . '/' . $smsWebhookId));
    }


    /**
     * @param string $smsWebhookId
     * @return array
     * @throws JsonException
     * @throws ClientExceptionInterface
     * @throws MailerSendAssertException
     */
    public function delete(string $smsWebhookId): array
    {
        GeneralHelpers::assert(
            fn () => Assertion::minLength($smsWebhookId, 1, 'SMS webhook id is required.')
        );

        return $this->httpLayer->delete($this->url($this->endpoint . '/' . $smsWebhookId));
    }
}
