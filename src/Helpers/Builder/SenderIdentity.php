<?php

namespace MailerSend\Helpers\Builder;

use Illuminate\Contracts\Support\Arrayable;

class SenderIdentity implements Arrayable, \JsonSerializable
{
    protected string $domainId;
    protected string $name;
    protected string $email;
    protected ?string $replyToEmail = null;
    protected ?string $replyToName = null;
    protected ?bool $addNote = false;
    protected ?string $personalNote = null;

    /**
     * @param string $domainId
     * @param string $name
     * @param string $email
     */
    public function __construct(string $domainId, string $name, string $email)
    {
        $this->domainId = $domainId;
        $this->name = $name;
        $this->email = $email;
    }

    public function getDomainId(): string
    {
        return $this->domainId;
    }

    public function setDomainId(string $domainId): self
    {
        $this->domainId = $domainId;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getReplyToEmail(): ?string
    {
        return $this->replyToEmail;
    }

    public function setReplyToEmail(?string $replyToEmail): self
    {
        $this->replyToEmail = $replyToEmail;
        return $this;
    }

    public function getReplyToName(): ?string
    {
        return $this->replyToName;
    }

    public function setReplyToName(?string $replyToName): self
    {
        $this->replyToName = $replyToName;
        return $this;
    }

    public function isAddNote(): ?bool
    {
        return $this->addNote;
    }

    public function setAddNote(?bool $addNote): self
    {
        $this->addNote = $addNote;
        return $this;
    }

    public function getPersonalNote(): ?string
    {
        return $this->personalNote;
    }

    public function setPersonalNote(?string $personalNote): self
    {
        $this->personalNote = $personalNote;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return [
            'domain_id' => $this->getDomainId(),
            'name' => $this->getName(),
            'email' => $this->getEmail(),
            'reply_to_email' => $this->getReplyToEmail(),
            'reply_to_name' => $this->getReplyToName(),
            'add_note' => $this->isAddNote(),
            'personal_note' => $this->getPersonalNote(),
        ];
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
