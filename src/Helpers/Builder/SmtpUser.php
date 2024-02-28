<?php

namespace MailerSend\Helpers\Builder;

use Tightenco\Collect\Contracts\Support\Arrayable;

class SmtpUser implements Arrayable, \JsonSerializable
{
    protected string $domainId;
    protected string $name;
    protected ?bool $enabled = true;

    /**
     * @param string $domainId
     * @param string $name
     * @param bool $enabled
     */
    public function __construct(string $domainId, string $name)
    {
        $this->domainId = $domainId;
        $this->name = $name;
    }

    public function getDomainId(): string
    {
        return $this->domainId;
    }

    public function setDomainId(string $domainId): self
    {
        $this->domainId = $domainId;
        return $this;
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
            'domain_id' => $this->getDomainId(),
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
