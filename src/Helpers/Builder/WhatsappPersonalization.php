<?php

namespace MailerSend\Helpers\Builder;

use MailerSend\Contracts\Arrayable;

class WhatsappPersonalization implements Arrayable, \JsonSerializable
{
    protected string $to;
    protected array $header = [];
    protected array $body = [];
    protected array $buttons = [];

    public function __construct(string $to)
    {
        $this->to = $to;
    }

    public function setHeader(array $header): WhatsappPersonalization
    {
        $this->header = $header;
        return $this;
    }

    public function setBody(array $body): WhatsappPersonalization
    {
        $this->body = $body;
        return $this;
    }

    public function setButtons(array $buttons): WhatsappPersonalization
    {
        $this->buttons = $buttons;
        return $this;
    }

    public function toArray(): array
    {
        $data = array_filter([
            'header' => $this->header ?: null,
            'body' => $this->body ?: null,
            'buttons' => $this->buttons ?: null,
        ]);

        return [
            'to' => $this->to,
            'data' => $data,
        ];
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}