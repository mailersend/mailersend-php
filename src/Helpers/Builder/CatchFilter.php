<?php

namespace MailerSend\Helpers\Builder;

use Assert\Assertion;
use Assert\AssertionFailedException;
use MailerSend\Common\Constants;
use MailerSend\Contracts\Arrayable;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Arr;

class CatchFilter implements Arrayable, \JsonSerializable
{
    protected string $type;
    protected array $filters = [];

    public const VALID_TYPES = [
        Constants::TYPE_CATCH_ALL,
        Constants::TYPE_CATCH_RECIPIENT,
    ];

    /**
     * @throws MailerSendAssertException
     */
    public function __construct(string $type)
    {
        $this->setType($type);
    }

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @throws MailerSendAssertException
     */
    public function setType(string $type): self
    {
        try {
            Assertion::inArray($type, self::VALID_TYPES, 'CatchFilter type must be one of: ' . implode(', ', self::VALID_TYPES) . '.');
        } catch (AssertionFailedException $e) {
            throw new MailerSendAssertException($e->getMessage());
        }

        $this->type = $type;

        return $this;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * @throws MailerSendAssertException
     */
    public function setFilters(array $filters): self
    {
        try {
            Assertion::maxCount($filters, 5, 'CatchFilter filters cannot contain more than 5 items.');
        } catch (AssertionFailedException $e) {
            throw new MailerSendAssertException($e->getMessage());
        }

        $this->filters = $filters;

        return $this;
    }

    /**
     * @throws MailerSendAssertException
     */
    public function addFilter(Filter $filter): self
    {
        try {
            Assertion::maxCount($this->filters, 4, 'CatchFilter filters cannot contain more than 5 items.');
        } catch (AssertionFailedException $e) {
            throw new MailerSendAssertException($e->getMessage());
        }

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
