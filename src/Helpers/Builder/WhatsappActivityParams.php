<?php

namespace MailerSend\Helpers\Builder;

class WhatsappActivityParams
{
    protected ?string $whatsapp_item_id = null;
    protected ?string $whatsapp_message_id = null;
    protected ?string $recipient = null;
    protected array $type = [];
    /** @var int|string|null */
    protected $date_from = null;
    /** @var int|string|null */
    protected $date_to = null;
    protected ?int $page = null;
    protected ?int $limit = null;

    public function getWhatsappItemId(): ?string
    {
        return $this->whatsapp_item_id;
    }

    public function setWhatsappItemId(?string $whatsapp_item_id): WhatsappActivityParams
    {
        $this->whatsapp_item_id = $whatsapp_item_id;
        return $this;
    }

    public function getWhatsappMessageId(): ?string
    {
        return $this->whatsapp_message_id;
    }

    public function setWhatsappMessageId(?string $whatsapp_message_id): WhatsappActivityParams
    {
        $this->whatsapp_message_id = $whatsapp_message_id;
        return $this;
    }

    public function getRecipient(): ?string
    {
        return $this->recipient;
    }

    public function setRecipient(?string $recipient): WhatsappActivityParams
    {
        $this->recipient = $recipient;
        return $this;
    }

    public function getType(): array
    {
        return $this->type;
    }

    public function setType(array $type): WhatsappActivityParams
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
    public function setDateFrom($date_from): WhatsappActivityParams
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
    public function setDateTo($date_to): WhatsappActivityParams
    {
        $this->date_to = $date_to;
        return $this;
    }

    public function getPage(): ?int
    {
        return $this->page;
    }

    public function setPage(?int $page): WhatsappActivityParams
    {
        $this->page = $page;
        return $this;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }

    public function setLimit(?int $limit): WhatsappActivityParams
    {
        $this->limit = $limit;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'whatsapp_item_id' => $this->getWhatsappItemId(),
            'whatsapp_message_id' => $this->getWhatsappMessageId(),
            'recipient' => $this->getRecipient(),
            'type' => $this->getType(),
            'date_from' => $this->getDateFrom(),
            'date_to' => $this->getDateTo(),
            'page' => $this->getPage(),
            'limit' => $this->getLimit(),
        ];
    }
}
