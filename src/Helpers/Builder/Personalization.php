<?php

namespace MailerSend\Helpers\Builder;

use Assert\Assertion;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\GeneralHelpers;
use Tightenco\Collect\Contracts\Support\Arrayable;

class Personalization implements Arrayable, \JsonSerializable
{
    protected string $email;
    protected array $data;

    /**
     * @throws MailerSendAssertException
     */
    public function __construct(string $email, array $substitutions)
    {
        $this->setEmail($email);
        $this->setData($substitutions);
    }

    /**
     * @throws MailerSendAssertException
     */
    public function setEmail(string $email): void
    {
        GeneralHelpers::assert(static function () use ($email) {
            Assertion::email($email);
        });

        $this->email = $email;
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
            'email' => $this->email,
            'data' => $this->data,
        ];
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
