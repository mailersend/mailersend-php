<?php

namespace MailerSend\Helpers\Builder;

use Assert\Assertion;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\GeneralHelpers;
use Tightenco\Collect\Contracts\Support\Arrayable;

class WebhookParams implements Arrayable, \JsonSerializable
{
    private string $url;
    private string $name;
    private array $events;
    private ?bool $enabled;
    private string $domainId;

    public const ACTIVITY_SENT = 'activity.sent';
    public const ACTIVITY_DELIVERED = 'activity.delivered';
    public const ACTIVITY_SOFT_BOUNCED = 'activity.soft_bounced';
    public const ACTIVITY_HARD_BOUNCED = 'activity.hard_bounced';
    public const ACTIVITY_OPENED = 'activity.opened';
    public const ACTIVITY_CLICKED = 'activity.clicked';
    public const ACTIVITY_UNSUBSCRIBED = 'activity.unsubscribed';
    public const ACTIVITY_SPAM_COMPLAINT = 'activity.spam_complaint';

    public const ALL_ACTIVITIES = [
        self::ACTIVITY_SENT, self::ACTIVITY_DELIVERED,
        self::ACTIVITY_SOFT_BOUNCED, self::ACTIVITY_HARD_BOUNCED,
        self::ACTIVITY_OPENED, self::ACTIVITY_CLICKED,
        self::ACTIVITY_UNSUBSCRIBED, self::ACTIVITY_SPAM_COMPLAINT,
    ];

    /**
     * WebhookParams constructor.
     * @param string $url
     * @param string $name
     * @param array $events
     * @param string $domainId
     * @param bool|null $enabled
     * @throws MailerSendAssertException
     */
    public function __construct(string $url, string $name, array $events, string $domainId, ?bool $enabled = null)
    {
        $this->setUrl($url)
            ->setName($name)
            ->setEvents($events)
            ->setEnabled($enabled)
            ->setDomainId($domainId);
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return WebhookParams
     */
    public function setUrl(string $url): WebhookParams
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return WebhookParams
     */
    public function setName(string $name): WebhookParams
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return array
     */
    public function getEvents(): array
    {
        return $this->events;
    }

    /**
     * @param array $events
     * @return $this
     * @throws MailerSendAssertException
     */
    public function setEvents(array $events): WebhookParams
    {
        GeneralHelpers::assert(
            fn () => Assertion::allInArray($events, self::ALL_ACTIVITIES, 'One or multiple invalid events.')
        );

        $this->events = $events;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getEnabled(): ?string
    {
        return $this->enabled;
    }

    /**
     * @param bool|null $enabled
     * @return WebhookParams
     */
    public function setEnabled(?bool $enabled): WebhookParams
    {
        $this->enabled = $enabled;
        return $this;
    }

    /**
     * @return string
     */
    public function getDomainId(): string
    {
        return $this->domainId;
    }

    /**
     * @param string $domainId
     * @return WebhookParams
     */
    public function setDomainId(string $domainId): WebhookParams
    {
        $this->domainId = $domainId;
        return $this;
    }


    public function toArray()
    {
        return [
            'url' => $this->getUrl(),
            'name' => $this->getName(),
            'events' => $this->getEvents(),
            'enabled' => $this->getEnabled(),
            'domain_id' => $this->getDomainId(),
        ];
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
