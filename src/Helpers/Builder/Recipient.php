<?php

namespace MailerSend\Helpers\Builder;

use Tightenco\Collect\Contracts\Support\Arrayable;

class Recipient implements Arrayable
{
    protected ?string $name;
    protected string $email;

    public function __construct(string $email, ?string $name)
    {
        $this->setEmail($email);
        $this->setName($name);
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
        ];
    }
}