<?php

namespace MailerSend\Helpers\Builder;

class SmsActivityParams
{
    protected ?string $sms_number_id = null;
    protected ?int $page = null;
    protected ?int $limit = null;
    /** @var int|string|null */
    protected $date_from = null;
    /** @var int|string|null */
    protected $date_to = null;
    protected array $status = [];

    public function getSmsNumberId(): ?string
    {
        return $this->sms_number_id;
    }

    public function setSmsNumberId(?string $sms_number_id): SmsActivityParams
    {
        $this->sms_number_id = $sms_number_id;
        return $this;
    }

    public function getPage(): ?int
    {
        return $this->page;
    }

    public function setPage(?int $page): SmsActivityParams
    {
        $this->page = $page;
        return $this;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }

    public function setLimit(?int $limit): SmsActivityParams
    {
        $this->limit = $limit;
        return $this;
    }

    /** @return int|string|null */
    public function getDateFrom()
    {
        return $this->date_from;
    }

    /**
     * @param int|string|null $date_from Unix timestamp, ISO 8601 or Y-m-d H:i:s, e.g. 1443651141, '2015-09-30T22:12:21Z' or '2015-09-30 22:12:21'
     */
    public function setDateFrom($date_from): SmsActivityParams
    {
        $this->date_from = $date_from;
        return $this;
    }

    /** @return int|string|null */
    public function getDateTo()
    {
        return $this->date_to;
    }

    /**
     * @param int|string|null $date_to Unix timestamp, ISO 8601 or Y-m-d H:i:s, e.g. 1443661141, '2015-09-30T23:59:59Z' or '2015-09-30 23:59:59'
     */
    public function setDateTo($date_to): SmsActivityParams
    {
        $this->date_to = $date_to;
        return $this;
    }

    public function getStatus(): array
    {
        return $this->status;
    }

    public function setStatus(array $status): SmsActivityParams
    {
        $this->status = $status;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'sms_number_id' => $this->getSmsNumberId(),
            'page' => $this->getPage(),
            'limit' => $this->getLimit(),
            'date_from' => $this->getDateFrom(),
            'date_to' => $this->getDateTo(),
            'status' => $this->getStatus(),
        ];
    }
}
