<?php

namespace MailerSend\Helpers\Builder;

use Assert\Assertion;
use Assert\AssertionFailedException;
use MailerSend\Contracts\Arrayable;
use MailerSend\Exceptions\MailerSendAssertException;

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
     * @throws MailerSendAssertException
     */
    public function __construct(string $domainId, string $name, string $email)
    {
        try {
            Assertion::maxLength($name, 191, 'Name may not be greater than 191 characters.');
            Assertion::maxLength($email, 320, 'Email may not be greater than 320 characters.');
        } catch (AssertionFailedException $e) {
            throw new MailerSendAssertException($e->getMessage());
        }

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

    /**
     * @throws MailerSendAssertException
     */
    public function setName(string $name): self
    {
        try {
            Assertion::maxLength($name, 191, 'Name may not be greater than 191 characters.');
        } catch (AssertionFailedException $e) {
            throw new MailerSendAssertException($e->getMessage());
        }

        $this->name = $name;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @throws MailerSendAssertException
     */
    public function setEmail(string $email): self
    {
        try {
            Assertion::maxLength($email, 320, 'Email may not be greater than 320 characters.');
        } catch (AssertionFailedException $e) {
            throw new MailerSendAssertException($e->getMessage());
        }

        $this->email = $email;
        return $this;
    }

    public function getReplyToEmail(): ?string
    {
        return $this->replyToEmail;
    }

    /**
     * @throws MailerSendAssertException
     */
    public function setReplyToEmail(?string $replyToEmail): self
    {
        if ($replyToEmail !== null) {
            try {
                Assertion::maxLength($replyToEmail, 320, 'Reply to email may not be greater than 320 characters.');
            } catch (AssertionFailedException $e) {
                throw new MailerSendAssertException($e->getMessage());
            }
        }

        $this->replyToEmail = $replyToEmail;
        return $this;
    }

    public function getReplyToName(): ?string
    {
        return $this->replyToName;
    }

    /**
     * @throws MailerSendAssertException
     */
    public function setReplyToName(?string $replyToName): self
    {
        if ($replyToName !== null) {
            try {
                Assertion::maxLength($replyToName, 191, 'Reply to name may not be greater than 191 characters.');
            } catch (AssertionFailedException $e) {
                throw new MailerSendAssertException($e->getMessage());
            }
        }

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

    /**
     * @throws MailerSendAssertException
     */
    public function setPersonalNote(?string $personalNote): self
    {
        if ($personalNote !== null) {
            try {
                Assertion::maxLength($personalNote, 250, 'Personal note may not be greater than 250 characters.');
            } catch (AssertionFailedException $e) {
                throw new MailerSendAssertException($e->getMessage());
            }
        }

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

    /**
     * Returns only the fields accepted by the PUT (update) endpoint.
     * The PUT endpoint does not accept domain_id, email, add_note, or personal_note.
     */
    public function toUpdateArray(): array
    {
        return [
            'name' => $this->getName(),
            'reply_to_email' => $this->getReplyToEmail(),
            'reply_to_name' => $this->getReplyToName(),
        ];
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
