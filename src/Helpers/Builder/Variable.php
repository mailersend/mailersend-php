<?php

namespace MailerSend\Helpers\Builder;

use Assert\Assertion;
use Illuminate\Contracts\Support\Arrayable;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\GeneralHelpers;

class Variable implements Arrayable, \JsonSerializable
{
    protected string $email;
    protected array $substitutions;

    /**
     * @throws MailerSendAssertException
     */
    public function __construct(string $email, array $substitutions)
    {
        $this->setEmail($email);
        $this->setSubstitutions($substitutions);
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
    public function setSubstitutions(array $substitutions): void
    {
        GeneralHelpers::assert(static function () use ($substitutions) {
            Assertion::minCount($substitutions, 1);
        });

        $mapped = [];

        foreach ($substitutions as $var => $value) {
            $mapped[] = [
                'var' => $var,
                'value' => $value,
            ];
        }

        $this->substitutions = $mapped;
    }

    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'substitutions' => $this->substitutions,
        ];
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
