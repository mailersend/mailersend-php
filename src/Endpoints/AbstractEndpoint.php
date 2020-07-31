<?php

namespace MailerSend\Endpoints;

use MailerSend\Common\HttpLayer;

abstract class AbstractEndpoint
{
    protected HttpLayer $httpLayer;
    protected array $options;

    public function __construct(HttpLayer $httpLayer, array $options)
    {
        $this->httpLayer = $httpLayer;
        $this->options = $options;
    }

    protected function buildUri(string $path, array $params = []): string
    {
        $paramsArray = [];
        foreach ($params as $key => $value) {
            if (is_array($value)) {
                $value = implode(',', $value);
            }

            $paramsArray[] = $key.'='.$value;
        }

        $paramsString = implode('&', $paramsArray);

        return $this->options['protocol'].'://'.
            $this->options['host'].
            ($this->options['port'] ? ':'.$this->options ['port'] : '').
            '/api/'.$this->options['version'].'/'.
            $path.
            ($paramsString ? '?'.$paramsString : '');
    }
}