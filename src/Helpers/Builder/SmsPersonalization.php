<?php

namespace MailerSend\Helpers\Builder;

use Assert\Assertion;
use Illuminate\Contracts\Support\Arrayable;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\GeneralHelpers;

class SmsPersonalization implements Arrayable, \JsonSerializable
{
    protected string $recipient;
    protected array $data;

    /**
     * @throws MailerSendAssertException
     */
    public function __construct(string $recipient, array $substitutions)
    {
        $this->setRecipient($recipient);
        $this->setData($substitutions);
    }

    /**
     * @throws MailerSendAssertException
     */
    public function setRecipient(string $recipient): void
    {
        GeneralHelpers::assert(static function () use ($recipient) {
            Assertion::startsWith($recipient, '+');
        });

        $this->recipient = $recipient;
    }

    /**
     * @throws MailerSendAssertException
     */
    public function setData(array $data): void
    {
        GeneralHelpers::assert(static function () use ($data) {
            Assertion::minCount($data, 1);
        });

        $this->data = $data;
    }

    public function toArray(): array
    {
        return [
            'phone_number' => $this->recipient,
            'data' => $this->data,
        ];
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
