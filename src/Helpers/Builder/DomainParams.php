<?php

namespace MailerSend\Helpers\Builder;

class DomainParams
{
    protected ?int $page = null;
    protected ?int $limit = null;
    protected ?bool $verified = null;

    public function getPage(): ?int
    {
        return $this->page;
    }

    public function setPage(?int $page): DomainParams
    {
        $this->page = $page;
        return $this;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }

    public function setLimit(?int $limit): DomainParams
    {
        $this->limit = $limit;
        return $this;
    }

    public function getVerified(): ?bool
    {
        return $this->verified;
    }

    public function setVerified(?bool $verified): DomainParams
    {
        $this->verified = $verified;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'page' => $this->getPage(),
            'limit' => $this->getLimit(),
            'verified' => $this->getVerified(),
        ];
    }
}
