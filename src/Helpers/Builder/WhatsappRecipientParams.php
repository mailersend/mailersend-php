<?php

namespace MailerSend\Helpers\Builder;

class WhatsappRecipientParams
{
    protected ?string $status = null;
    protected ?string $phone_number = null;
    protected ?int $page = null;
    protected ?int $limit = null;

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): WhatsappRecipientParams
    {
        $this->status = $status;
        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phone_number;
    }

    public function setPhoneNumber(?string $phone_number): WhatsappRecipientParams
    {
        $this->phone_number = $phone_number;
        return $this;
    }

    public function getPage(): ?int
    {
        return $this->page;
    }

    public function setPage(?int $page): WhatsappRecipientParams
    {
        $this->page = $page;
        return $this;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }

    public function setLimit(?int $limit): WhatsappRecipientParams
    {
        $this->limit = $limit;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'status' => $this->getStatus(),
            'phone_number' => $this->getPhoneNumber(),
            'page' => $this->getPage(),
            'limit' => $this->getLimit(),
        ];
    }
}
