<?php

namespace MailerSend\Helpers\Builder;

use Illuminate\Contracts\Support\Arrayable;

class SmsInboundFilter implements Arrayable, \JsonSerializable
{
    protected string $comparer;
    protected string $value;

    public function __construct(string $comparer, string $value)
    {
        $this->comparer = $comparer;
        $this->value = $value;
    }

    public function toArray(): array
    {
        return [
            'comparer' => $this->comparer,
            'value' => $this->value,
        ];
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
