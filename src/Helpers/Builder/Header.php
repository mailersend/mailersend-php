<?php

namespace MailerSend\Helpers\Builder;

use Assert\Assertion;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\GeneralHelpers;
use Tightenco\Collect\Contracts\Support\Arrayable;

class Header implements Arrayable, \JsonSerializable
{
    protected string $name;
    protected string $value;

    /**
     * @throws MailerSendAssertException
     */
    public function __construct(string $name, string $value)
    {
        $this->setName($name);
        $this->setValue($value);
    }

    /**
     * @throws MailerSendAssertException
     */
    public function setName(string $name): void
    {
        GeneralHelpers::assert(static function () use ($name) {
            Assertion::notEmpty($name);
            Assertion::string($name);
        });

        $this->name = $name;
    }

    /**
     * @throws MailerSendAssertException
     */
    public function setValue(string $value): void
    {
        GeneralHelpers::assert(static function () use ($value) {
            Assertion::notEmpty($value);
            Assertion::string($value);
        });

        $this->value = $value;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'value' => $this->value,
        ];
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
