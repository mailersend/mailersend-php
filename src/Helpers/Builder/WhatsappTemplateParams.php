<?php

namespace MailerSend\Helpers\Builder;

class WhatsappTemplateParams
{
    protected ?string $whatsapp_account_id = null;
    protected ?string $status = null;
    protected ?string $category = null;
    protected ?string $language = null;
    protected ?int $page = null;
    protected ?int $limit = null;

    public function getWhatsappAccountId(): ?string
    {
        return $this->whatsapp_account_id;
    }

    public function setWhatsappAccountId(?string $whatsapp_account_id): WhatsappTemplateParams
    {
        $this->whatsapp_account_id = $whatsapp_account_id;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): WhatsappTemplateParams
    {
        $this->status = $status;
        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): WhatsappTemplateParams
    {
        $this->category = $category;
        return $this;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function setLanguage(?string $language): WhatsappTemplateParams
    {
        $this->language = $language;
        return $this;
    }

    public function getPage(): ?int
    {
        return $this->page;
    }

    public function setPage(?int $page): WhatsappTemplateParams
    {
        $this->page = $page;
        return $this;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }

    public function setLimit(?int $limit): WhatsappTemplateParams
    {
        $this->limit = $limit;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'whatsapp_account_id' => $this->getWhatsappAccountId(),
            'status' => $this->getStatus(),
            'category' => $this->getCategory(),
            'language' => $this->getLanguage(),
            'page' => $this->getPage(),
            'limit' => $this->getLimit(),
        ];
    }
}
