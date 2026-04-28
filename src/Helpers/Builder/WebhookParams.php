<?php

namespace MailerSend\Helpers\Builder;

use Assert\Assertion;
use MailerSend\Contracts\Arrayable;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\GeneralHelpers;

class WebhookParams implements Arrayable, \JsonSerializable
{
    private string $url;
    private string $name;
    private array $events;
    private ?bool $enabled;
    private string $domainId;
    private ?int $version = null;
    private ?bool $editable = null;

    public const ACTIVITY_SENT = 'activity.sent';
    public const ACTIVITY_DELIVERED = 'activity.delivered';
    public const ACTIVITY_SOFT_BOUNCED = 'activity.soft_bounced';
    public const ACTIVITY_HARD_BOUNCED = 'activity.hard_bounced';
    public const ACTIVITY_OPENED = 'activity.opened';
    public const ACTIVITY_OPENED_UNIQUE = 'activity.opened_unique';
    public const ACTIVITY_CLICKED = 'activity.clicked';
    public const ACTIVITY_CLICKED_UNIQUE = 'activity.clicked_unique';
    public const ACTIVITY_UNSUBSCRIBED = 'activity.unsubscribed';
    public const ACTIVITY_SPAM_COMPLAINT = 'activity.spam_complaint';
    public const ACTIVITY_SURVEY_OPENED = 'activity.survey_opened';
    public const ACTIVITY_SURVEY_SUBMITTED = 'activity.survey_submitted';
    public const ACTIVITY_IDENTITY_VERIFIED = 'sender_identity.verified';
    public const ACTIVITY_MAINTENANCE_START = 'maintenance.start';
    public const ACTIVITY_MAINTENANCE_END = 'maintenance.end';
    public const ACTIVITY_INBOUND_FORWARD_FAILED = 'inbound_forward.failed';
    public const ACTIVITY_EMAIL_SINGLE_VERIFIED = 'email_single.verified';
    public const ACTIVITY_EMAIL_LIST_VERIFIED = 'email_list.verified';
    public const ACTIVITY_BULK_EMAIL_COMPLETED = 'bulk_email.completed';
    public const ACTIVITY_RECIPIENT_ON_HOLD_ADDED = 'recipient.on_hold_added';
    public const ACTIVITY_RECIPIENT_ON_HOLD_REMOVED = 'recipient.on_hold_removed';

    public const ALL_ACTIVITIES = [
        self::ACTIVITY_SENT, self::ACTIVITY_DELIVERED,
        self::ACTIVITY_SOFT_BOUNCED, self::ACTIVITY_HARD_BOUNCED,
        self::ACTIVITY_OPENED, self::ACTIVITY_OPENED_UNIQUE,
        self::ACTIVITY_CLICKED, self::ACTIVITY_CLICKED_UNIQUE,
        self::ACTIVITY_UNSUBSCRIBED, self::ACTIVITY_SPAM_COMPLAINT,
        self::ACTIVITY_SURVEY_OPENED, self::ACTIVITY_SURVEY_SUBMITTED,
        self::ACTIVITY_IDENTITY_VERIFIED, self::ACTIVITY_MAINTENANCE_START,
        self::ACTIVITY_MAINTENANCE_END, self::ACTIVITY_INBOUND_FORWARD_FAILED,
        self::ACTIVITY_EMAIL_SINGLE_VERIFIED, self::ACTIVITY_EMAIL_LIST_VERIFIED,
        self::ACTIVITY_BULK_EMAIL_COMPLETED, self::ACTIVITY_RECIPIENT_ON_HOLD_ADDED,
        self::ACTIVITY_RECIPIENT_ON_HOLD_REMOVED,
    ];

    /**
     * WebhookParams constructor.
     * @param string $url
     * @param string $name
     * @param array $events
     * @param string $domainId
     * @param bool|null $enabled
     * @param int|null $version
     * @param bool|null $editable
     * @throws MailerSendAssertException
     */
    public function __construct(string $url, string $name, array $events, string $domainId, ?bool $enabled = null, ?int $version = null, ?bool $editable = null)
    {
        $this->setUrl($url)
            ->setName($name)
            ->setEvents($events)
            ->setEnabled($enabled)
            ->setDomainId($domainId)
            ->setVersion($version)
            ->setEditable($editable);
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

    /**
     * @return int|null
     */
    public function getVersion(): ?int
    {
        return $this->version;
    }

    /**
     * @param int|null $version
     * @return WebhookParams
     */
    public function setVersion(?int $version): WebhookParams
    {
        $this->version = $version;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getEditable(): ?bool
    {
        return $this->editable;
    }

    /**
     * @param bool|null $editable
     * @return WebhookParams
     */
    public function setEditable(?bool $editable): WebhookParams
    {
        $this->editable = $editable;
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
            'version' => $this->getVersion(),
            'editable' => $this->getEditable(),
        ];
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
