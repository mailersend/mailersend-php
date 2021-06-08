<?php

namespace MailerSend\Exceptions;

use Psr\Http\Client\RequestExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class MailerSendHttpException extends MailerSendException implements RequestExceptionInterface
{
    protected RequestInterface $request;
    protected ResponseInterface $response;

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response
    ) {
        $message = sprintf(
            '[url] %s [http method] %s [status code] %s [reason phrase] %s',
            $request->getRequestTarget(),
            $request->getMethod(),
            $response->getStatusCode(),
            $response->getReasonPhrase()
        );

        parent::__construct($message, $response->getStatusCode());
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
