<?php

namespace MailerSend\Helpers\Builder;

class WhatsappInboundMessageParams
{
    protected ?string $whatsapp_account_id = null;
    protected ?string $from = null;
    protected array $type = [];
    /** @var int|string|null */
    protected $date_from = null;
    /** @var int|string|null */
    protected $date_to = null;
    protected ?int $page = null;
    protected ?int $limit = null;

    public function getWhatsappAccountId(): ?string
    {
        return $this->whatsapp_account_id;
    }

    public function setWhatsappAccountId(?string $whatsapp_account_id): WhatsappInboundMessageParams
    {
        $this->whatsapp_account_id = $whatsapp_account_id;
        return $this;
    }

    public function getFrom(): ?string
    {
        return $this->from;
    }

    public function setFrom(?string $from): WhatsappInboundMessageParams
    {
        $this->from = $from;
        return $this;
    }

    public function getType(): array
    {
        return $this->type;
    }

    public function setType(array $type): WhatsappInboundMessageParams
    {
        $this->type = $type;
        return $this;
    }

    /** @return int|string|null */
    public function getDateFrom()
    {
        return $this->date_from;
    }

    /**
     * @param int|string|null $date_from Unix timestamp or datetime string, e.g. 1791936000, '2026-10-14T00:00:00Z' or '2026-10-14 00:00:00'
     */
    public function setDateFrom($date_from): WhatsappInboundMessageParams
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
     * @param int|string|null $date_to Unix timestamp or datetime string, e.g. 1792022400, '2026-10-15T00:00:00Z' or '2026-10-15 00:00:00'
     */
    public function setDateTo($date_to): WhatsappInboundMessageParams
    {
        $this->date_to = $date_to;
        return $this;
    }

    public function getPage(): ?int
    {
        return $this->page;
    }

    public function setPage(?int $page): WhatsappInboundMessageParams
    {
        $this->page = $page;
        return $this;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }

    public function setLimit(?int $limit): WhatsappInboundMessageParams
    {
        $this->limit = $limit;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'whatsapp_account_id' => $this->getWhatsappAccountId(),
            'from' => $this->getFrom(),
            'type' => $this->getType(),
            'date_from' => $this->getDateFrom(),
            'date_to' => $this->getDateTo(),
            'page' => $this->getPage(),
            'limit' => $this->getLimit(),
        ];
    }
}
