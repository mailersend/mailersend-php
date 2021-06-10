<?php

namespace MailerSend;

use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\Email;
use MailerSend\Endpoints\Message;
use MailerSend\Exceptions\MailerSendException;
use Tightenco\Collect\Support\Arr;

/**
 * This is the PHP SDK for MailerSend
 *
 * Class MailerSend
 * @package MailerSend
 */
class MailerSend
{
    protected array $options;
    protected static array $defaultOptions = [
        'host' => 'api.mailersend.com',
        'protocol' => 'https',
        'api_path' => 'v1',
        'api_key' => '',
        'debug' => false,
    ];

    protected ?HttpLayer $httpLayer;

    public Email $email;
    public Message $messages;

    /**
     * @param  array  $options  Additional options for the SDK
     * @param  HttpLayer  $httpLayer
     * @throws MailerSendException
     */
    public function __construct(array $options = [], ?HttpLayer $httpLayer = null)
    {
        $this->setOptions($options);
        $this->setHttpLayer($httpLayer);
        $this->setEndpoints();
    }

    protected function setEndpoints(): void
    {
        $this->email = new Email($this->httpLayer, $this->options);
        $this->messages = new Message($this->httpLayer, $this->options);
    }

    protected function setHttpLayer(?HttpLayer $httpLayer = null): void
    {
        $this->httpLayer = $httpLayer ?: new HttpLayer($this->options);
    }

    /**
     * @throws MailerSendException
     */
    protected function setOptions(?array $options): void
    {
        $this->options = self::$defaultOptions;

        foreach ($options as $option => $value) {
            if (array_key_exists($option, $this->options)) {
                $this->options[$option] = $value;
            }
        }

        if (empty(Arr::get($this->options, 'api_key'))) {
            throw new MailerSendException('Please set "api_key" in SDK options.');
        }
    }
}
