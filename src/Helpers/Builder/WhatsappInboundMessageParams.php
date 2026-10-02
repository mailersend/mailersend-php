<?php

namespace MailerSend\Helpers\Builder;

class WhatsappInboundMessageParams
{
    protected ?string $whatsapp_account_id = null;
    protected array $type = [];
    protected ?int $date_from = null;
    protected ?int $date_to = null;
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

    public function getType(): array
    {
        return $this->type;
    }

    public function setType(array $type): WhatsappInboundMessageParams
    {
        $this->type = $type;
        return $this;
    }

    public function getDateFrom(): ?int
    {
        return $this->date_from;
    }

    public function setDateFrom(?int $date_from): WhatsappInboundMessageParams
    {
        $this->date_from = $date_from;
        return $this;
    }

    public function getDateTo(): ?int
    {
        return $this->date_to;
    }

    public function setDateTo(?int $date_to): WhatsappInboundMessageParams
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
            'type' => $this->getType(),
            'date_from' => $this->getDateFrom(),
            'date_to' => $this->getDateTo(),
            'page' => $this->getPage(),
            'limit' => $this->getLimit(),
        ];
    }
}
