<?php

namespace MailerSend\Helpers\Builder;

use Tightenco\Collect\Contracts\Support\Arrayable;

class Variable implements Arrayable
{
    protected string $email;
    protected array $substitutions;

    public function __construct(string $email, array $substitutions)
    {
        $this->setEmail($email);
        $this->setSubstitutions($substitutions);
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function setSubstitutions(array $substitutions): void
    {
        $mapped = [];
        foreach ($substitutions as $substitution){
            foreach ($substitution as $var => $value){
                $mapped[] = [
                    'var' => $var,
                    'value' => $value,
                ];
            }
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
}