<?php

namespace MailerSend\Helpers\Builder;

class BlocklistParams extends SuppressionParams
{
    private array $patterns = [];

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
