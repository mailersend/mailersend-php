<?php

namespace MailerSend\Helpers\Builder;

use Assert\Assertion;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\GeneralHelpers;
use Tightenco\Collect\Contracts\Support\Arrayable;

class SmsWebhookParams implements Arrayable, \JsonSerializable
{
    private ?string $url;
    private ?string $name;
    private ?array $events;
    private ?bool $enabled;
    private ?string $smsNumberId;

    public const SMS_SENT = 'sms.sent';
    public const SMS_DELIVERED = 'sms.delivered';
    public const SMS_FAILED = 'sms.failed';

    public const ALL_ACTIVITIES = [
        self::SMS_SENT,
        self::SMS_DELIVERED,
        self::SMS_FAILED,
    ];

    /**
     * SmsWebhookParams constructor.
     * @param string|null $url
     * @param string|null $name
     * @param array|null $events
     * @param string|null $smsNumberId
     * @param bool|null $enabled
     * @throws MailerSendAssertException
     */
    public function __construct(string $url = null, string $name = null, array $events = null, string $smsNumberId = null, ?bool $enabled = null)
    {
        $this->setUrl($url)
            ->setName($name)
            ->setEvents($events)
            ->setEnabled($enabled)
            ->setSmsNumberId($smsNumberId);
    }

    /**
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @param string|null $url
     * @return SmsWebhookParams
     */
    public function setUrl(?string $url): SmsWebhookParams
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     * @return SmsWebhookParams
     */
    public function setName(?string $name): SmsWebhookParams
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return ?array
     */
    public function getEvents(): ?array
    {
        return $this->events;
    }

    /**
     * @param array|null $events
     * @return $this
     * @throws MailerSendAssertException
     */
    public function setEvents(?array $events): SmsWebhookParams
    {
        if ($events) {
            GeneralHelpers::assert(
                fn () => Assertion::allInArray($events, self::ALL_ACTIVITIES, 'One or multiple invalid events.')
            );
        }

        $this->events = $events;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getEnabled(): ?bool
    {
        return $this->enabled;
    }

    /**
     * @param bool|null $enabled
     * @return SmsWebhookParams
     */
    public function setEnabled(?bool $enabled): SmsWebhookParams
    {
        $this->enabled = $enabled;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSmsNumberId(): ?string
    {
        return $this->smsNumberId;
    }

    /**
     * @param string|null $smsNumberId
     * @return SmsWebhookParams
     */
    public function setSmsNumberId(?string $smsNumberId): SmsWebhookParams
    {
        $this->smsNumberId = $smsNumberId;
        return $this;
    }


    public function toArray()
    {
        return [
            'url' => $this->getUrl(),
            'name' => $this->getName(),
            'events' => $this->getEvents(),
            'enabled' => $this->getEnabled(),
            'sms_number_id' => $this->getSmsNumberId(),
        ];
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
