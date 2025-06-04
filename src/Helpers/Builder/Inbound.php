<?php

namespace MailerSend\Helpers\Builder;

use MailerSend\Contracts\Arrayable;

class Inbound implements Arrayable, \JsonSerializable
{
    protected string $domainId;
    protected string $name;
    protected bool $domainEnabled;
    protected ?string $inboundDomain = null;
    protected $catchFilter = null;
    protected $matchFilter = null;
    protected array $forwards = [];
    protected ?string $catchType = null;
    protected ?string $matchType = null;
    protected ?int $inboundPriority = null;

    public function __construct(string $domainId, string $name, bool $domainEnabled)
    {
        $this->domainId = $domainId;
        $this->name = $name;
        $this->domainEnabled = $domainEnabled;
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

    public function isDomainEnabled(): bool
    {
        return $this->domainEnabled;
    }

    public function setDomainEnabled(bool $domainEnabled): self
    {
        $this->domainEnabled = $domainEnabled;

        return $this;
    }

    public function getInboundDomain(): ?string
    {
        return $this->inboundDomain;
    }

    public function setInboundDomain(?string $inboundDomain): self
    {
        $this->inboundDomain = $inboundDomain;

        return $this;
    }

    public function getCatchFilter()
    {
        return $this->catchFilter;
    }

    /**
     * @param array|CatchFilter|null $catchFilter
     * @return $this
     */
    public function setCatchFilter($catchFilter): self
    {
        $this->catchFilter = $catchFilter;

        return $this;
    }

    public function getMatchFilter()
    {
        return $this->matchFilter;
    }

    /**
     * @param array|MatchFilter|null $matchFilter
     * @return $this
     */
    public function setMatchFilter($matchFilter): self
    {
        $this->matchFilter = $matchFilter;

        return $this;
    }

    public function getForwards(): array
    {
        return $this->forwards;
    }

    public function setForwards(array $forwards): self
    {
        $this->forwards = $forwards;

        return $this;
    }

    public function addForward(Forward $forward): self
    {
        $this->forwards[] = $forward;

        return $this;
    }

    public function getCatchType(): ?string
    {
        return $this->catchType;
    }

    public function setCatchType(string $catchType): self
    {
        $this->catchType = $catchType;

        return $this;
    }

    public function getMatchType(): ?string
    {
        return $this->matchType;
    }

    public function setMatchType(string $matchType): self
    {
        $this->matchType = $matchType;

        return $this;
    }

    public function getInboundPriority(): ?int
    {
        return $this->inboundPriority;
    }

    public function setInboundPriority(?int $inboundPriority): self
    {
        $this->inboundPriority = $inboundPriority;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'domain_id' => $this->getDomainId(),
            'name' => $this->getName(),
            'domain_enabled' => $this->isDomainEnabled(),
            'inbound_domain' => $this->getInboundDomain(),
            'catch_filter' => $this->getCatchFilter(),
            'match_filter' => $this->getMatchFilter(),
            'forwards' => $this->getForwards(),
            'catch_type' => $this->getCatchType(),
            'match_type' => $this->getMatchType(),
            'inbound_priority' => $this->getInboundPriority()
        ];
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
