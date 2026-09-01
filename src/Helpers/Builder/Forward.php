<?php

namespace MailerSend\Helpers\Builder;

use Assert\Assertion;
use Assert\AssertionFailedException;
use MailerSend\Common\Constants;
use MailerSend\Contracts\Arrayable;
use MailerSend\Exceptions\MailerSendAssertException;

class Forward implements Arrayable, \JsonSerializable
{
    protected string $type;
    protected string $value;

    public const VALID_TYPES = [
        Constants::TYPE_EMAIL,
        Constants::TYPE_WEBHOOK,
    ];

    /**
     * @throws MailerSendAssertException
     */
    public function __construct(string $type, string $value)
    {
        try {
            Assertion::inArray($type, self::VALID_TYPES, 'Forward type must be one of: ' . implode(', ', self::VALID_TYPES) . '.');
            Assertion::maxLength($value, 191, 'Forward value cannot be longer than 191 characters.');

            if ($type === Constants::TYPE_WEBHOOK) {
                Assertion::url($value, 'Forward value must be a valid URL when type is webhook.');
            } elseif ($type === Constants::TYPE_EMAIL) {
                Assertion::email($value, 'Forward value must be a valid email address when type is email.');
            }
        } catch (AssertionFailedException $e) {
            throw new MailerSendAssertException($e->getMessage());
        }

        $this->type = $type;
        $this->value = $value;
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'value' => $this->value,
        ];
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
