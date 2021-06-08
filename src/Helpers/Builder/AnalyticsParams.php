<?php

namespace MailerSend\Helpers\Builder;

class AnalyticsParams
{
    protected ?string $domainId;
    protected int $date_from;
    protected int $date_to;
    protected ?string $group_by;
    protected array $tags = [];
    protected array $event = [];

    public function __construct(int $date_from, int $date_to)
    {
        $this->date_from = $date_from;
        $this->date_to = $date_to;
    }

    public function getDomainId(): ?string
    {
        return $this->domainId;
    }

    public function setDomainId(?string $domainId): AnalyticsParams
    {
        $this->domainId = $domainId;
        return $this;
    }

    public function getDateFrom(): int
    {
        return $this->date_from;
    }

    public function setDateFrom(int $date_from): AnalyticsParams
    {
        $this->date_from = $date_from;
        return $this;
    }

    public function getDateTo(): int
    {
        return $this->date_to;
    }

    public function setDateTo(int $date_to): AnalyticsParams
    {
        $this->date_to = $date_to;
        return $this;
    }

    public function getGroupBy(): ?string
    {
        return $this->group_by;
    }

    public function setGroupBy(?string $group_by): AnalyticsParams
    {
        $this->group_by = $group_by;
        return $this;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function setTags(array $tags): AnalyticsParams
    {
        $this->tags = $tags;
        return $this;
    }

    public function getEvent(): array
    {
        return $this->event;
    }

    public function setEvent(array $event): AnalyticsParams
    {
        $this->event = $event;
        return $this;
    }
}
