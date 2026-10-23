<?php

namespace MailerSend\Helpers\Builder;

use MailerSend\Contracts\Arrayable;

class SmtpUserParams implements Arrayable, \JsonSerializable
{
    protected ?string $name = null;
    protected ?bool $enabled = null;

    /**
     * @param string|null $name
     */
    public function __construct(?string $name = null)
    {
        $this->name = $name;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(?bool $enabled): self
    {
        $this->enabled = $enabled;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return array_filter([
            'name' => $this->getName(),
            'enabled' => $this->getEnabled(),
        ], fn ($value) => $value !== null);
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
