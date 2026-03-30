<?php

namespace MailerSend\Helpers\Builder;

use MailerSend\Contracts\Arrayable;

class DmarcMonitoringParams implements Arrayable, \JsonSerializable
{
    protected string $domainId;

    public function __construct(string $domainId)
    {
        $this->domainId = $domainId;
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

    public function toArray(): array
    {
        return [
            'domain_id' => $this->getDomainId(),
        ];
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
