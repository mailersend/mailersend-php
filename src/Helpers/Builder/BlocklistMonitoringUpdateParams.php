<?php

namespace MailerSend\Helpers\Builder;

use MailerSend\Contracts\Arrayable;

class BlocklistMonitoringUpdateParams implements Arrayable, \JsonSerializable
{
    protected ?string $name = null;
    protected ?bool $notify = null;
    protected ?string $notifyEmail = null;
    protected ?string $notifyAddress = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getNotify(): ?bool
    {
        return $this->notify;
    }

    public function setNotify(?bool $notify): self
    {
        $this->notify = $notify;

        return $this;
    }

    public function getNotifyEmail(): ?string
    {
        return $this->notifyEmail;
    }

    public function setNotifyEmail(?string $notifyEmail): self
    {
        $this->notifyEmail = $notifyEmail;

        return $this;
    }

    public function getNotifyAddress(): ?string
    {
        return $this->notifyAddress;
    }

    public function setNotifyAddress(?string $notifyAddress): self
    {
        $this->notifyAddress = $notifyAddress;

        return $this;
    }

    public function toArray(): array
    {
        return array_filter(
            [
                'name' => $this->getName(),
                'notify' => $this->getNotify(),
                'notify_email' => $this->getNotifyEmail(),
                'notify_address' => $this->getNotifyAddress(),
            ],
            fn ($v) => $v !== null
        );
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
