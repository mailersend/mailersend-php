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

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getReturnPathSubdomain(): ?string
    {
        return $this->returnPathSubdomain;
    }

    public function setReturnPathSubdomain(?string $returnPathSubdomain): void
    {
        $this->returnPathSubdomain = $returnPathSubdomain;
    }

    public function getCustomTrackingSubdomain(): ?string
    {
        return $this->customTrackingSubdomain;
    }

    public function setCustomTrackingSubdomain(?string $customTrackingSubdomain): void
    {
        $this->customTrackingSubdomain = $customTrackingSubdomain;
    }

    public function getInboundRoutingSubdomain(): ?string
    {
        return $this->inboundRoutingSubdomain;
    }

    public function setInboundRoutingSubdomain(?string $inboundRoutingSubdomain): void
    {
        $this->inboundRoutingSubdomain = $inboundRoutingSubdomain;
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

    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
