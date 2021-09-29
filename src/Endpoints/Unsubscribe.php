<?php

namespace MailerSend\Endpoints;

use MailerSend\Common\HttpLayer;

class Unsubscribe extends Suppression
{
    public function __construct(HttpLayer $httpLayer, array $options)
    {
        $endpoint = 'suppressions/unsubscribes';
        parent::__construct($httpLayer, $options, $endpoint);
    }
}
