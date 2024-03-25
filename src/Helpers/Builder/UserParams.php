<?php

namespace MailerSend\Helpers\Builder;

use Illuminate\Contracts\Support\Arrayable;

class UserParams implements Arrayable, \JsonSerializable
{
    protected ?string $email = null;
    protected ?string $role = null;
    protected array $permissions = [];
    protected array $templates = [];
    protected array $domains = [];
    protected ?bool $requires_periodic_password_change = false;

    /**
     * @param string $email
     * @param string $role
     */
    public function __construct(string $email = null, string $role = null)
    {
        $this->email = $email;
        $this->role = $role;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
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
