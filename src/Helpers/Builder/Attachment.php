<?php

namespace MailerSend\Helpers\Builder;

use Assert\Assertion;
use Assert\AssertionFailedException;
use MailerSend\Contracts\Arrayable;
use MailerSend\Exceptions\MailerSendAssertException;

class Attachment implements Arrayable, \JsonSerializable
{
    protected string $content;
    protected string $filename;
    protected ?string $disposition = null;
    protected ?string $id = null;

    public function __construct(
        ?string $content = null,
        ?string $filename = null,
        ?string $disposition = null,
        ?string $id = null
    ) {
        if ($content) {
            $this->setContent($content);
        }

        if ($filename) {
            $this->setFilename($filename);
        }

        if ($disposition) {
            $this->setDisposition($disposition);
        }

        if ($id) {
            $this->setId($id);
        }
    }

    public function setContent(string $content): void
    {
        if (!$this->isBase64($content)) {
            $this->content = base64_encode($content);
        } else {
            $this->content = $content;
        }
    }

    public function setFilename(string $filename): void
    {
        $this->filename = $filename;
    }

    /**
     * @throws MailerSendAssertException
     */
    public function setDisposition(?string $disposition): void
    {
        if ($disposition !== null) {
            try {
                Assertion::inArray($disposition, ['inline', 'attachment'], 'Disposition must be either "inline" or "attachment".');
            } catch (AssertionFailedException $e) {
                throw new MailerSendAssertException($e->getMessage());
            }
        }

        $this->disposition = $disposition;
    }

    /**
     * @throws MailerSendAssertException
     */
    public function setId(?string $id): void
    {
        if ($id !== null) {
            try {
                Assertion::maxLength($id, 256, 'Attachment id may not be greater than 256 characters.');
            } catch (AssertionFailedException $e) {
                throw new MailerSendAssertException($e->getMessage());
            }
        }

        $this->id = $id;
    }

    public function toArray(): array
    {
        return [
            'content' => $this->content,
            'filename' => $this->filename,
            'disposition' => $this->disposition,
            'id' => $this->id,
        ];
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    protected function isBase64($string): bool
    {
        $decoded_data = base64_decode($string, true);
        $encoded_data = base64_encode($decoded_data);

        if ($encoded_data !== $string) {
            return false;
        }

        return true;
    }
}
