<?php

namespace MailerSend\Helpers\Builder;

class BlocklistParams
{
    private string $domainId;
    private array $recipients;
    private array $patterns;

    public function __construct()
    {
        $this->recipients = [];
        $this->patterns = [];
    }

    /**
     * @return string
     */
    public function getDomainId(): ?string
    {
        return $this->domainId;
    }

    /**
     * @param string $domainId
     */
    public function setDomainId(string $domainId): BlocklistParams
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
    public function setRecipients(array $recipients): BlocklistParams
    {
        $this->recipients = $recipients;
        return $this;
    }

    /**
     * @return array
     */
    public function getPatterns(): array
    {
        return $this->patterns;
    }

    /**
     * @param array $patterns
     */
    public function setPatterns(array $patterns): BlocklistParams
    {
        $this->patterns = $patterns;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'domain_id' => $this->getDomainId(),
            'recipients' => $this->getRecipients(),
            'patterns' => $this->getPatterns(),
        ];
    }
}
