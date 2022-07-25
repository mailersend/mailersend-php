<?php

namespace MailerSend\Helpers\Builder;

class SmsActivityParams
{
    protected ?string $sms_number_id = null;
    protected ?int $page = null;
    protected ?int $limit = null;
    protected ?int $date_from = null;
    protected ?int $date_to = null;
    protected array $status = [];

    public function getSmsNumberid(): ?string
    {
        return $this->sms_number_id;
    }

    public function setSmsNumberid(?string $sms_number_id): SmsActivityParams
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

    public function getDateFrom(): ?int
    {
        return $this->date_from;
    }

    public function setDateFrom(?int $date_from): SmsActivityParams
    {
        $this->date_from = $date_from;
        return $this;
    }

    public function getDateTo(): ?int
    {
        return $this->date_to;
    }

    public function setDateTo(?int $date_to): SmsActivityParams
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
            'sms_number_id' => $this->getSmsNumberid(),
            'page' => $this->getPage(),
            'limit' => $this->getLimit(),
            'date_from' => $this->getDateFrom(),
            'date_to' => $this->getDateTo(),
            'status' => $this->getStatus(),
        ];
    }
}
