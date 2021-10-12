<?php

namespace MailerSend;

use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\Activity;
use MailerSend\Endpoints\Analytics;
use MailerSend\Endpoints\Blocklist;
use MailerSend\Endpoints\BulkEmail;
use MailerSend\Endpoints\Domain;
use MailerSend\Endpoints\Email;
use MailerSend\Endpoints\HardBounce;
use MailerSend\Endpoints\Message;
use MailerSend\Endpoints\Template;
use MailerSend\Endpoints\SpamComplaint;
use MailerSend\Endpoints\Unsubscribe;
use MailerSend\Endpoints\Webhook;
use MailerSend\Endpoints\Token;
use MailerSend\Endpoints\Recipient;
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
    public BulkEmail $bulkEmail;
    public Message $messages;
    public Webhook $webhooks;
    public Token $token;
    public Activity $activity;
    public Analytics $analytics;
    public Domain $domain;
    public Recipient $recipients;
    public Template $template;
    public Blocklist $blocklist;
    public HardBounce $hardBounce;
    public SpamComplaint $spamComplaint;
    public Unsubscribe $unsubscribe;

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
        $this->bulkEmail = new BulkEmail($this->httpLayer, $this->options);
        $this->messages = new Message($this->httpLayer, $this->options);
        $this->webhooks = new Webhook($this->httpLayer, $this->options);
        $this->token = new Token($this->httpLayer, $this->options);
        $this->activity = new Activity($this->httpLayer, $this->options);
        $this->analytics = new Analytics($this->httpLayer, $this->options);
        $this->domain = new Domain($this->httpLayer, $this->options);
        $this->recipients = new Recipient($this->httpLayer, $this->options);
        $this->template = new Template($this->httpLayer, $this->options);
        $this->blocklist = new Blocklist($this->httpLayer, $this->options);
        $this->hardBounce = new HardBounce($this->httpLayer, $this->options);
        $this->spamComplaint = new SpamComplaint($this->httpLayer, $this->options);
        $this->unsubscribe = new Unsubscribe($this->httpLayer, $this->options);
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
