<?php

namespace MailerSend\Helpers\Builder;

use Assert\Assertion;
use JsonSerializable;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\GeneralHelpers;
use Tightenco\Collect\Contracts\Support\Arrayable;

class TokenParams implements Arrayable, JsonSerializable
{
    private string $name;
    private string $domainId;
    private array $scopes;
    private bool $restricted;

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

    public const ALL_SCOPES = [
        self::EMAIL_FULL,
        self::DOMAINS_READ, self::DOMAINS_FULL,
        self::ACTIVITY_READ, self::ACTIVITY_FULL,
        self::ANALYTICS_READ, self::ANALYTICS_FULL,
        self::TOKENS_FULL,
        self::WEBHOOKS_FULL,
        self::TEMPLATES_FULL,
    ];

    public const STATUS_PAUSE = 'pause';
    public const STATUS_UNPAUSE = 'unpause';
    public const STATUS_ALL = [ self::STATUS_PAUSE, self::STATUS_UNPAUSE ];


    /**
     * TokenParams constructor.
     * @param string $name
     * @param mixed $domainId string or array
     * @param array $scopes
     * @param bool $restricted optional
     * @throws MailerSendAssertException
     */
    public function __construct(string $name, mixed $domainId, array $scopes, bool $restricted = false)
    {
        $this->setName($name)
            ->setDomainId($domainId)
            ->setScopes($scopes)
            ->setRestricted($restricted);
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
     * @param mixed $domainId string or array
     * @return TokenParams
     */
    public function setDomainId(mixed $domainId): TokenParams
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
     * @return bool
     */
    public function getRestricted(): bool
    {
        return $this->restricted;
    }

    /**
     * @param bool $restricted
     * @return TokenParams
     */
    public function setRestricted(bool $restricted): TokenParams
    {
        $this->restricted = $restricted;

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
            'restricted' => $this->getRestricted(),
        ]);
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
