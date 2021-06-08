<?php

namespace MailerSend\Exceptions;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class MailerSendValidationException extends MailerSendException
{
    protected RequestInterface $request;
    protected ResponseInterface $response;

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response
    ) {
        $body = $response->getBody()->getContents();
        $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

        parent::__construct($data['message']);

        $this->request = $request;
        $this->response = $response;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }
}
