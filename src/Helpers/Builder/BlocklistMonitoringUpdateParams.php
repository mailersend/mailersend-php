<?php

namespace MailerSend\Helpers\Builder;

use Assert\Assertion;
use MailerSend\Contracts\Arrayable;
use MailerSend\Helpers\GeneralHelpers;

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
        if ($name !== null) {
            GeneralHelpers::assert(
                fn () => Assertion::maxLength($name, 255, 'Name may not be greater than 255 characters.')
            );
        }

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
        if ($notifyEmail !== null) {
            GeneralHelpers::assert(
                fn () => Assertion::maxLength($notifyEmail, 255, 'Notify email may not be greater than 255 characters.')
            );
            GeneralHelpers::assert(
                fn () => Assertion::email($notifyEmail, 'Notify email is invalid.')
            );
        }

        $this->notifyEmail = $notifyEmail;

        return $this;
    }

    public function getNotifyAddress(): ?string
    {
        return $this->notifyAddress;
    }

    public function setNotifyAddress(?string $notifyAddress): self
    {
        if ($notifyAddress !== null) {
            GeneralHelpers::assert(
                fn () => Assertion::maxLength($notifyAddress, 500, 'Notify address may not be greater than 500 characters.')
            );
            GeneralHelpers::assert(
                fn () => Assertion::url($notifyAddress, 'Notify address must be a valid URL.')
            );
        }

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
