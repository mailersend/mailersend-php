<?php

namespace MailerSend\Helpers\Builder;

use Tightenco\Collect\Contracts\Support\Arrayable;
use Tightenco\Collect\Support\Arr;

class CatchFilter implements Arrayable, \JsonSerializable
{
    protected string $type;
    protected array $filters = [];

    public function __construct(string $type)
    {
        $this->type = $type;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function setFilters(array $filters): self
    {
        $this->filters = $filters;

        return $this;
    }

    public function addFilter(Filter $filter): self
    {
        $this->filters[] = $filter;

        return $this;
    }

    public function toArray(): array
    {
        $array = [
            'type' => $this->getType(),
        ];

        if (count($this->getFilters()) > 0) {
            Arr::set($array, 'filters', $this->getFilters());
        }

        return $array;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
