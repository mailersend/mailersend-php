<?php

namespace MailerSend\Helpers\Builder;

use Tightenco\Collect\Contracts\Support\Arrayable;

class EmailVerificationParams implements Arrayable, \JsonSerializable
{
    protected string $name;
    protected array $emailAddresses = [];

    public const VALID = 'valid';
    public const CATCH_ALL = 'catch_all';
    public const MAILBOX_FULL = 'mailbox_full';
    public const ROLE_BASED = 'role_based';
    public const UNKNOWN = 'unknown';
    public const SYNTAX_ERROR = 'syntax_error';
    public const TYPO = 'typo';
    public const MAILBOX_NOT_FOUND = 'mailbox_not_found';
    public const DISPOSABLE = 'disposable';
    public const MAILBOX_BLOCKED = 'mailbox_blocked';
    public const FAILED = 'failed';

    public const POSSIBLE_RESULTS = [
        self::VALID,
        self::CATCH_ALL,
        self::MAILBOX_FULL,
        self::ROLE_BASED,
        self::UNKNOWN,
        self::SYNTAX_ERROR,
        self::TYPO,
        self::MAILBOX_NOT_FOUND,
        self::DISPOSABLE,
        self::MAILBOX_BLOCKED,
        self::FAILED,
    ];

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getEmailAddresses(): array
    {
        return $this->emailAddresses;
    }

    public function setEmailAddresses(array $emailAddresses): self
    {
        $this->emailAddresses = $emailAddresses;

        return $this;
    }

    public function addEmailAddress(string $emailAddress): self
    {
        $this->emailAddresses[] = $emailAddress;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->getName(),
            'emailAddresses' => $this->emailAddresses,
        ];
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
