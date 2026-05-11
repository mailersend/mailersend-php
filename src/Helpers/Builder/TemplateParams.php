<?php

namespace MailerSend\Helpers\Builder;

use MailerSend\Contracts\Arrayable;

class TemplateParams implements Arrayable, \JsonSerializable
{
    protected ?string $name = null;
    protected ?string $html = null;
    protected ?string $text = null;
    protected ?string $domainId = null;
    protected ?array $categories = null;
    protected ?array $tags = null;
    protected ?bool $autoGenerate = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getHtml(): ?string
    {
        return $this->html;
    }

    public function setHtml(?string $html): self
    {
        $this->html = $html;
        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): self
    {
        $this->text = $text;
        return $this;
    }

    public function getDomainId(): ?string
    {
        return $this->domainId;
    }

    public function setDomainId(?string $domainId): self
    {
        $this->domainId = $domainId;
        return $this;
    }

    public function getCategories(): ?array
    {
        return $this->categories;
    }

    public function setCategories(?array $categories): self
    {
        $this->categories = $categories;
        return $this;
    }

    public function getTags(): ?array
    {
        return $this->tags;
    }

    public function setTags(?array $tags): self
    {
        $this->tags = $tags;
        return $this;
    }

    public function getAutoGenerate(): ?bool
    {
        return $this->autoGenerate;
    }

    public function setAutoGenerate(?bool $autoGenerate): self
    {
        $this->autoGenerate = $autoGenerate;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->getName(),
            'html' => $this->getHtml(),
            'text' => $this->getText(),
            'domain_id' => $this->getDomainId(),
            'categories' => $this->getCategories(),
            'tags' => $this->getTags(),
            'auto_generate' => $this->getAutoGenerate(),
        ], fn ($value) => $value !== null);
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
