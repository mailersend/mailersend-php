<?php

namespace MailerSend\Endpoints;

use MailerSend\Common\HttpLayer;

class OnHoldList extends Suppression
{
    public function __construct(HttpLayer $httpLayer, array $options)
    {
        $endpoint = 'suppressions/on-hold-list';
        parent::__construct($httpLayer, $options, $endpoint);
    }
}
