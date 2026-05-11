<?php

namespace MailerSend\Helpers\Builder;

use Assert\Assertion;
use Assert\AssertionFailedException;
use MailerSend\Common\Constants;
use MailerSend\Contracts\Arrayable;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Arr;

class Filter implements Arrayable, \JsonSerializable
{
    protected string $comparer;
    protected string $value;
    protected ?string $key;

    /**
     * @throws MailerSendAssertException
     */
    public function __construct(string $comparer, string $value, ?string $key = null)
    {
        try {
            Assertion::inArray(
                $comparer,
                Constants::POSSIBLE_SMS_INBOUND_COMPARERS,
                'Filter comparer must be one of: ' . implode(', ', Constants::POSSIBLE_SMS_INBOUND_COMPARERS) . '.'
            );
        } catch (AssertionFailedException $e) {
            throw new MailerSendAssertException($e->getMessage());
        }

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
