<?php

namespace MailerSend\Helpers\Builder;

use Tightenco\Collect\Contracts\Support\Arrayable;

class Attachment implements Arrayable, \JsonSerializable
{
    protected string $content;
    protected string $filename;

    public function __construct(string $content = null, string $filename = null)
    {
        if ($content) {
            $this->setContent($content);
        }

        if ($filename) {
            $this->setFilename($filename);
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

    public function toArray(): array
    {
        return [
            'content' => $this->content,
            'filename' => $this->filename,
        ];
    }

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
