<?php

namespace MailerSend\Helpers\Builder;

use Illuminate\Contracts\Support\Arrayable;

class SmsInbound implements Arrayable, \JsonSerializable
{
    protected ?string $smsNumberId = null;
    protected ?string $name = null;
    protected ?string $forward_url = null;
    protected $filter = null;
    protected ?bool $enabled = true;

    public function __construct(string $smsNumberId = null, string $name = null, string $forward_url = null, $filter = null, bool $enabled = null)
    {
        $this->smsNumberId = $smsNumberId;
        $this->name = $name;
        $this->forward_url = $forward_url;
        $this->filter = $filter;
        $this->enabled = $enabled;
    }

    public function getSmsNumberId(): ?string
    {
        return $this->smsNumberId;
    }

    public function setSmsNumberId(string $smsNumberId): SmsInbound
    {
        $this->smsNumberId = $smsNumberId;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): SmsInbound
    {
        $this->name = $name;

        return $this;
    }

    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * @param array|SmsInboundFilter|null $filter
     * @return $this
     */
    public function setFilter($filter): SmsInbound
    {
        $this->filter = $filter;

        return $this;
    }

    public function getForwardUrl(): ?string
    {
        return $this->forward_url;
    }

    public function setForwardUrl(string $forward_url): SmsInbound
    {
        $this->forward_url = $forward_url;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getEnabled(): ?bool
    {
        return $this->enabled;
    }

    /**
     * @param bool|null $enabled
     * @return SmsInbound
     */
    public function setEnabled(?bool $enabled): SmsInbound
    {
        $this->enabled = $enabled;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'sms_number_id' => $this->getSmsNumberId(),
            'name' => $this->getName(),
            'forward_url' => $this->getForwardUrl(),
            'filter' => $this->getFilter(),
            'enabled' => $this->getEnabled()
        ];
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
