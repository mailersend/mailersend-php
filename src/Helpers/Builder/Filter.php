<?php

namespace MailerSend\Helpers\Builder;

use MailerSend\Contracts\Arrayable;
use MailerSend\Helpers\Arr;

class Filter implements Arrayable, \JsonSerializable
{
    protected string $comparer;
    protected string $value;
    protected ?string $key;

    public function __construct(string $comparer, string $value, ?string $key = null)
    {
        $this->comparer = $comparer;
        $this->value = $value;
        $this->key = $key;
    }

    public function toArray(): array
    {
        $array = [
            'comparer' => $this->comparer,
            'value' => $this->value,
        ];

        if ($this->key) {
            Arr::set($array, 'key', $this->key);
        }

        return $array;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
