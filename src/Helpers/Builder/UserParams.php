<?php

namespace MailerSend\Helpers\Builder;

use Assert\Assertion;
use MailerSend\Common\Roles;
use MailerSend\Contracts\Arrayable;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\GeneralHelpers;
use MailerSend\Helpers\MailerSendAssertion;

class UserParams implements Arrayable, \JsonSerializable
{
    protected ?string $email = null;
    protected ?string $role = null;
    protected array $permissions = [];
    protected array $templates = [];
    protected array $domains = [];
    protected ?bool $requires_periodic_password_change = false;

    /**
     * @param string|null $email
     * @param string|null $role
     * @throws MailerSendAssertException
     */
    public function __construct(?string $email = null, ?string $role = null)
    {
        if ($email !== null) {
            $this->setEmail($email);
        }
        if ($role !== null) {
            $this->setRole($role);
        }
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @throws MailerSendAssertException
     */
    public function setEmail(string $email): self
    {
        GeneralHelpers::assert(
            fn () => Assertion::maxLength($email, 320, 'Email may not be greater than 320 characters.')
        );
        GeneralHelpers::assert(
            fn () => MailerSendAssertion::email($email)
        );

        $this->email = $email;
        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    /**
     * @throws MailerSendAssertException
     */
    public function setRole(string $role): self
    {
        GeneralHelpers::assert(
            fn () => Assertion::inArray($role, Roles::ALL_ROLES, 'Invalid role.')
        );

        $this->role = $role;
        return $this;
    }

    public function getPermissions(): array
    {
        return $this->permissions;
    }

    public function setPermissions(array $permissions): self
    {
        $this->permissions = $permissions;
        return $this;
    }

    public function getTemplates(): array
    {
        return $this->templates;
    }

    public function setTemplates(array $templates): self
    {
        $this->templates = $templates;
        return $this;
    }

    public function getDomains(): array
    {
        return $this->domains;
    }

    public function setDomains(array $domains): self
    {
        $this->domains = $domains;
        return $this;
    }

    public function getRequiresPeriodicPasswordChange(): ?bool
    {
        return $this->requires_periodic_password_change;
    }

    public function setRequiresPeriodicPasswordChange(?bool $requires_periodic_password_change): self
    {
        $this->requires_periodic_password_change = $requires_periodic_password_change;
        return $this;
    }

    public function toArray()
    {
        return array_filter([
                'email' => $this->getEmail(),
                'role' => $this->getRole(),
                'permissions' => $this->getPermissions(),
                'domains' => $this->getDomains(),
                'templates' => $this->getTemplates(),
                'requires_periodic_password_change' => $this->getRequiresPeriodicPasswordChange()
            ]);
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
