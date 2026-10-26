<?php

namespace MailerSend\Helpers\Builder;

class WhatsappItemParams
{
    protected ?string $recipient = null;
    protected ?string $whatsapp_message_id = null;
    protected array $status = [];
    protected ?string $whatsapp_account_id = null;
    protected ?string $from = null;
    protected ?string $template_id = null;
    /** @var int|string|null */
    protected $date_from = null;
    /** @var int|string|null */
    protected $date_to = null;
    protected ?int $page = null;
    protected ?int $limit = null;

    public function getRecipient(): ?string
    {
        return $this->recipient;
    }

    public function setRecipient(?string $recipient): WhatsappItemParams
    {
        $this->recipient = $recipient;
        return $this;
    }

    public function getWhatsappMessageId(): ?string
    {
        return $this->whatsapp_message_id;
    }

    public function setWhatsappMessageId(?string $whatsapp_message_id): WhatsappItemParams
    {
        $this->whatsapp_message_id = $whatsapp_message_id;
        return $this;
    }

    public function getStatus(): array
    {
        return $this->status;
    }

    public function setStatus(array $status): WhatsappItemParams
    {
        $this->status = $status;
        return $this;
    }

    public function getWhatsappAccountId(): ?string
    {
        return $this->whatsapp_account_id;
    }

    public function setWhatsappAccountId(?string $whatsapp_account_id): WhatsappItemParams
    {
        $this->whatsapp_account_id = $whatsapp_account_id;
        return $this;
    }

    public function getFrom(): ?string
    {
        return $this->from;
    }

    public function setFrom(?string $from): WhatsappItemParams
    {
        $this->from = $from;
        return $this;
    }

    public function getTemplateId(): ?string
    {
        return $this->template_id;
    }

    public function setTemplateId(?string $template_id): WhatsappItemParams
    {
        $this->template_id = $template_id;
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
    public function setDateFrom($date_from): WhatsappItemParams
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
    public function setDateTo($date_to): WhatsappItemParams
    {
        $this->date_to = $date_to;
        return $this;
    }

    public function getPage(): ?int
    {
        return $this->page;
    }

    public function setPage(?int $page): WhatsappItemParams
    {
        $this->page = $page;
        return $this;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }

    public function setLimit(?int $limit): WhatsappItemParams
    {
        $this->limit = $limit;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'recipient' => $this->getRecipient(),
            'whatsapp_message_id' => $this->getWhatsappMessageId(),
            'status' => $this->getStatus(),
            'whatsapp_account_id' => $this->getWhatsappAccountId(),
            'from' => $this->getFrom(),
            'template_id' => $this->getTemplateId(),
            'date_from' => $this->getDateFrom(),
            'date_to' => $this->getDateTo(),
            'page' => $this->getPage(),
            'limit' => $this->getLimit(),
        ];
    }
}
