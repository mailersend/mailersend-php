<?php

namespace MailerSend\Helpers\Builder;

class SuppressionParams
{
    private ?string $domainId = null;
    private array $recipients = [];

    /**
     * @return string|null
     */
    public function getDomainId(): ?string
    {
        return $this->domainId;
    }

    /**
     * @param string|null $domainId
     */
    public function setDomainId(?string $domainId): SuppressionParams
    {
        $this->domainId = $domainId;
        return $this;
    }

    /**
     * @return array
     */
    public function getRecipients(): array
    {
        return $this->recipients;
    }

    /**
     * @param array $recipients
     */
    public function setRecipients(array $recipients): SuppressionParams
    {
        $this->recipients = $recipients;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'domain_id' => $this->getDomainId(),
            'recipients' => $this->getRecipients(),
        ];
    }
}
