<?php

namespace MailerSend\Helpers\Builder;

use MailerSend\Contracts\Arrayable;

class DmarcMonitoringUpdateParams implements Arrayable, \JsonSerializable
{
    protected string $wantedDmarcRecord;

    public function __construct(string $wantedDmarcRecord)
    {
        $this->wantedDmarcRecord = $wantedDmarcRecord;
    }

    public function getWantedDmarcRecord(): string
    {
        return $this->wantedDmarcRecord;
    }

    public function setWantedDmarcRecord(string $wantedDmarcRecord): self
    {
        $this->wantedDmarcRecord = $wantedDmarcRecord;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'wanted_dmarc_record' => $this->getWantedDmarcRecord(),
        ];
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
