<?php

namespace MailerSend\Helpers\Builder;

use Illuminate\Contracts\Support\Arrayable;

class SmtpUserParams implements Arrayable, \JsonSerializable
{
    protected string $name;
    protected ?bool $enabled = true;

    /**
     * @param string $name
     * @param bool $enabled
     */
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
        return [
            'name' => $this->getName(),
            'enabled' => $this->getEnabled(),
        ];
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
