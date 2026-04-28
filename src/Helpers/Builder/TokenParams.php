<?php

namespace MailerSend\Helpers\Builder;

use Assert\Assertion;
use MailerSend\Contracts\Arrayable;
use JsonSerializable;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\GeneralHelpers;

class TokenParams implements Arrayable, JsonSerializable
{
    private string $name;
    private string $domainId;
    private array $scopes;

    public const EMAIL_FULL = 'email_full';
    public const DOMAINS_READ = 'domains_read';
    public const DOMAINS_FULL = 'domains_full';
    public const ACTIVITY_READ = 'activity_read';
    public const ACTIVITY_FULL = 'activity_full';
    public const ANALYTICS_READ = 'analytics_read';
    public const ANALYTICS_FULL = 'analytics_full';
    public const TOKENS_FULL = 'tokens_full';
    public const WEBHOOKS_FULL = 'webhooks_full';
    public const TEMPLATES_FULL = 'templates_full';
    public const SUPPRESSIONS_READ = 'suppressions_read';
    public const SUPPRESSIONS_FULL = 'suppressions_full';
    public const SMS_READ = 'sms_read';
    public const SMS_FULL = 'sms_full';
    public const WHATSAPP_FULL = 'whatsapp_full';
    public const EMAIL_VERIFICATION_READ = 'email_verification_read';
    public const EMAIL_VERIFICATION_FULL = 'email_verification_full';
    public const INBOUNDS_FULL = 'inbounds_full';
    public const RECIPIENTS_READ = 'recipients_read';
    public const RECIPIENTS_FULL = 'recipients_full';
    public const SENDER_IDENTITY_READ = 'sender_identity_read';
    public const SENDER_IDENTITY_FULL = 'sender_identity_full';
    public const USERS_READ = 'users_read';
    public const USERS_FULL = 'users_full';
    public const IFTTT = 'ifttt';
    public const SMTP_USERS_READ = 'smtp_users_read';
    public const SMTP_USERS_FULL = 'smtp_users_full';
    public const DMARC_MONITORING_READ = 'dmarc_monitoring_read';
    public const DMARC_MONITORING_FULL = 'dmarc_monitoring_full';
    public const BLOCKLIST_MONITORING_READ = 'blocklist_monitoring_read';
    public const BLOCKLIST_MONITORING_FULL = 'blocklist_monitoring_full';

    public const ALL_SCOPES = [
        self::EMAIL_FULL,
        self::DOMAINS_READ, self::DOMAINS_FULL,
        self::ACTIVITY_READ, self::ACTIVITY_FULL,
        self::ANALYTICS_READ, self::ANALYTICS_FULL,
        self::TOKENS_FULL,
        self::WEBHOOKS_FULL,
        self::TEMPLATES_FULL,
        self::SUPPRESSIONS_READ, self::SUPPRESSIONS_FULL,
        self::SMS_READ, self::SMS_FULL,
        self::WHATSAPP_FULL,
        self::EMAIL_VERIFICATION_READ, self::EMAIL_VERIFICATION_FULL,
        self::INBOUNDS_FULL,
        self::RECIPIENTS_READ, self::RECIPIENTS_FULL,
        self::SENDER_IDENTITY_READ, self::SENDER_IDENTITY_FULL,
        self::USERS_READ, self::USERS_FULL,
        self::IFTTT,
        self::SMTP_USERS_READ, self::SMTP_USERS_FULL,
        self::DMARC_MONITORING_READ, self::DMARC_MONITORING_FULL,
        self::BLOCKLIST_MONITORING_READ, self::BLOCKLIST_MONITORING_FULL,
    ];

    public const STATUS_PAUSE = 'pause';
    public const STATUS_UNPAUSE = 'unpause';
    public const STATUS_ALL = [ self::STATUS_PAUSE, self::STATUS_UNPAUSE ];


    /**
     * TokenParams constructor.
     * @param string $name
     * @param string $domainId
     * @param array $scopes
     * @throws MailerSendAssertException
     */
    public function __construct(string $name, string $domainId, array $scopes)
    {
        $this->setName($name)
            ->setDomainId($domainId)
            ->setScopes($scopes);
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return TokenParams
     */
    public function setName(string $name): TokenParams
    {
        $this->name = $name;

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
     * @return TokenParams
     */
    public function setDomainId(string $domainId): TokenParams
    {
        $this->domainId = $domainId;

        return $this;
    }

    /**
     * @return array
     */
    public function getScopes(): array
    {
        return $this->scopes;
    }

    /**
     * @param array $scopes
     * @return TokenParams
     * @throws MailerSendAssertException
     */
    public function setScopes(array $scopes): TokenParams
    {
        GeneralHelpers::assert(
            fn () =>  Assertion::allInArray($scopes, self::ALL_SCOPES, 'Some scopes are not valid.')
        );

        $this->scopes = $scopes;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->getName(),
            'domain_id' => $this->getDomainId(),
            'scopes' => $this->getScopes(),
        ]);
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
