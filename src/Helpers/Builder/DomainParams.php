<?php

namespace MailerSend\Helpers\Builder;

use Tightenco\Collect\Contracts\Support\Arrayable;

class DomainParams implements Arrayable, \JsonSerializable
{
    protected string $name;
    protected ?string $returnPathSubdomain = null;
    protected ?string $customTrackingSubdomain = null;
    protected ?string $inboundRoutingSubdomain = null;

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

    public function getReturnPathSubdomain(): ?string
    {
        return $this->returnPathSubdomain;
    }

    public function setReturnPathSubdomain(?string $returnPathSubdomain): self
    {
        $this->returnPathSubdomain = $returnPathSubdomain;

        return $this;
    }

    public function getCustomTrackingSubdomain(): ?string
    {
        return $this->customTrackingSubdomain;
    }

    public function setCustomTrackingSubdomain(?string $customTrackingSubdomain): self
    {
        $this->customTrackingSubdomain = $customTrackingSubdomain;

        return $this;
    }

    public function getInboundRoutingSubdomain(): ?string
    {
        return $this->inboundRoutingSubdomain;
    }

    public function setInboundRoutingSubdomain(?string $inboundRoutingSubdomain): self
    {
        $this->inboundRoutingSubdomain = $inboundRoutingSubdomain;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->getName(),
            'return_path_subdomain' => $this->getReturnPathSubdomain(),
            'custom_tracking_subdomain' => $this->getCustomTrackingSubdomain(),
            'inbound_routing_subdomain' => $this->getInboundRoutingSubdomain(),
        ];
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
