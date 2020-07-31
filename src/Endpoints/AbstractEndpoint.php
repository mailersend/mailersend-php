<?php

namespace MailerSend\Endpoints;

use MailerSend\Common\HttpLayer;

abstract class AbstractEndpoint
{
    protected HttpLayer $httpLayer;
    protected array $options;
    protected string $endpoint = '';

    public function __construct(HttpLayer $httpLayer, array $options)
    {
        $this->httpLayer = $httpLayer;
        $this->options = $options;
    }

    protected function buildUri(string $path = ''): string
    {
        $base = $this->options['protocol'].'://'.$this->options['host'].'/'.$this->options['version'].'/'.$this->endpoint;

        return $path ? $base.'/'.$path : $base;
    }
}