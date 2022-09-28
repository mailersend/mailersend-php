<?php

namespace MailerSend\Helpers\Builder;

use Tightenco\Collect\Contracts\Support\Arrayable;

class Attachment implements Arrayable, \JsonSerializable
{
    protected string $content;
    protected string $filename;
    protected ?string $disposition = null;
    protected ?string $id = null;

    public function __construct(
        string $content = null,
        string $filename = null,
        string $disposition = null,
        string $id = null
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

    public function setDisposition(?string $disposition): void
    {
        $this->disposition = $disposition;
    }

    public function setId(?string $id): void
    {
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
