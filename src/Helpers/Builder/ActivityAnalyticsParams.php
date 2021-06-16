<?php

namespace MailerSend\Helpers\Builder;

class ActivityAnalyticsParams
{
    protected ?string $domain_id = null;
    protected int $date_from;
    protected int $date_to;
    protected ?string $group_by = null;
    protected array $tags = [];
    protected array $event = [];

    public function __construct(int $date_from, int $date_to)
    {
        $this->setDateFrom($date_from);
        $this->setDateTo($date_to);
    }

    public function getDomainId(): ?string
    {
        return $this->domain_id;
    }

    public function setDomainId(?string $domain_id): ActivityAnalyticsParams
    {
        $this->domain_id = $domain_id;
        return $this;
    }

    public function getDateFrom(): int
    {
        return $this->date_from;
    }

    protected function setDateFrom(int $date_from): void
    {
        $this->date_from = $date_from;
    }

    public function getDateTo(): int
    {
        return $this->date_to;
    }

    protected function setDateTo(int $date_to): void
    {
        $this->date_to = $date_to;
    }

    public function getGroupBy(): ?string
    {
        return $this->group_by;
    }

    public function setGroupBy(?string $group_by): ActivityAnalyticsParams
    {
        $this->group_by = $group_by;
        return $this;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function setTags(array $tags): ActivityAnalyticsParams
    {
        $this->tags = $tags;
        return $this;
    }

    public function getEvent(): array
    {
        return $this->event;
    }

    public function setEvent(array $event): ActivityAnalyticsParams
    {
        $this->event = $event;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'domain_id' => $this->getDomainId(),
            'date_from' => $this->getDateFrom(),
            'date_to' => $this->getDateTo(),
            'group_by' => $this->getGroupBy(),
            'tags' => $this->getTags(),
            'event' => $this->getEvent(),
        ];
    }
}
