<?php

namespace MailerSend\Helpers\Builder;

class WhatsappParams
{
    protected ?string $from = null;
    protected array $to = [];
    protected ?string $templateId = null;
    protected array $personalization = [];

    public function getFrom(): ?string
    {
        return $this->from;
    }

    public function setFrom(string $from): WhatsappParams
    {
        $this->from = $from;
        return $this;
    }

    public function getTo(): array
    {
        return $this->to;
    }

    public function setTo(array $to): WhatsappParams
    {
        $this->to = $to;
        return $this;
    }

    public function addRecipient(string $recipient): WhatsappParams
    {
        $this->to[] = $recipient;
        return $this;
    }

    public function getTemplateId(): ?string
    {
        return $this->templateId;
    }

    public function setTemplateId(string $templateId): WhatsappParams
    {
        $this->templateId = $templateId;
        return $this;
    }

    public function getPersonalization(): array
    {
        return $this->personalization;
    }

    public function setPersonalization(array $personalization): WhatsappParams
    {
        $this->personalization = $personalization;
        return $this;
    }
}