<?php

namespace MailerSend\Helpers\Builder;

class SmsParams
{
    protected ?string $from = null;
    protected array $to = [];
    protected ?string $text = null;
    protected array $personalization = [];

    public function getFrom(): ?string
    {
        return $this->from;
    }

    public function setFrom(string $from): SmsParams
    {
        $this->from = $from;
        return $this;
    }

    /**
     * @return array
     */
    public function getTo(): array
    {
        return $this->to;
    }

    /**
     * @param array $to
     * @return SmsParams
     */
    public function setTo(array $to): SmsParams
    {
        $this->to = $to;
        return $this;
    }

    /**
     * @param string $recipient
     * @return $this
     */
    public function addRecipient(string $recipient): SmsParams
    {
        $this->to[] = $recipient;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getText(): ?string
    {
        return $this->text;
    }

    /**
     * @param string|null $text
     * @return SmsParams
     */
    public function setText(?string $text): SmsParams
    {
        $this->text = $text;
        return $this;
    }

    /**
     * @return array
     */
    public function getPersonalization(): array
    {
        return $this->personalization;
    }

    /**
     * @param array $personalization
     * @return SmsParams
     */
    public function setPersonalization(array $personalization): SmsParams
    {
        $this->personalization = $personalization;
        return $this;
    }
}
