<?php

namespace MailerSend\Helpers\Builder;

class EmailsParams
{
    protected ?string $domain_id = null;
    /** @var int|string|null */
    protected $date_from = null;
    /** @var int|string|null */
    protected $date_to = null;
    protected ?int $page = null;
    protected ?int $limit = null;
    protected array $status = [];
    protected array $interaction = [];
    protected ?string $recipient_email = null;
    protected ?string $message_id = null;
    protected ?string $template_id = null;
    protected ?string $subject = null;
    protected ?string $tag = null;

    public function getDomainId(): ?string
    {
        return $this->domain_id;
    }

    public function setDomainId(?string $domain_id): EmailsParams
    {
        $this->domain_id = $domain_id;
        return $this;
    }

    /** @return int|string|null */
    public function getDateFrom()
    {
        return $this->date_from;
    }

    /**
     * @param int|string|null $date_from Unix timestamp or datetime string, e.g. 1443651141 or '2015-10-01 00:00:00'
     */
    public function setDateFrom($date_from): EmailsParams
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
     * @param int|string|null $date_to Unix timestamp or datetime string, e.g. 1443661141 or '2015-10-01 23:59:59'
     */
    public function setDateTo($date_to): EmailsParams
    {
        $this->date_to = $date_to;
        return $this;
    }

    public function getPage(): ?int
    {
        return $this->page;
    }

    /**
     * @param int|null $page Min: 1, Max: 1000, Default: 1
     */
    public function setPage(?int $page): EmailsParams
    {
        $this->page = $page;
        return $this;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }

    public function setLimit(?int $limit): EmailsParams
    {
        $this->limit = $limit;
        return $this;
    }

    public function getStatus(): array
    {
        return $this->status;
    }

    /**
     * @param array $status Any of Constants::POSSIBLE_EMAIL_STATUSES
     */
    public function setStatus(array $status): EmailsParams
    {
        $this->status = $status;
        return $this;
    }

    public function getInteraction(): array
    {
        return $this->interaction;
    }

    /**
     * @param array $interaction Any of Constants::POSSIBLE_EMAIL_INTERACTIONS
     */
    public function setInteraction(array $interaction): EmailsParams
    {
        $this->interaction = $interaction;
        return $this;
    }

    public function getRecipientEmail(): ?string
    {
        return $this->recipient_email;
    }

    public function setRecipientEmail(?string $recipient_email): EmailsParams
    {
        $this->recipient_email = $recipient_email;
        return $this;
    }

    public function getMessageId(): ?string
    {
        return $this->message_id;
    }

    public function setMessageId(?string $message_id): EmailsParams
    {
        $this->message_id = $message_id;
        return $this;
    }

    public function getTemplateId(): ?string
    {
        return $this->template_id;
    }

    public function setTemplateId(?string $template_id): EmailsParams
    {
        $this->template_id = $template_id;
        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(?string $subject): EmailsParams
    {
        $this->subject = $subject;
        return $this;
    }

    public function getTag(): ?string
    {
        return $this->tag;
    }

    public function setTag(?string $tag): EmailsParams
    {
        $this->tag = $tag;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'domain_id' => $this->getDomainId(),
            'date_from' => $this->getDateFrom(),
            'date_to' => $this->getDateTo(),
            'page' => $this->getPage(),
            'limit' => $this->getLimit(),
            'status' => $this->getStatus(),
            'interaction' => $this->getInteraction(),
            'recipient_email' => $this->getRecipientEmail(),
            'message_id' => $this->getMessageId(),
            'template_id' => $this->getTemplateId(),
            'subject' => $this->getSubject(),
            'tag' => $this->getTag(),
        ];
    }
}
