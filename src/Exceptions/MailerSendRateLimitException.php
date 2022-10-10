<?php

namespace MailerSend\Exceptions;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class MailerSendRateLimitException extends MailerSendHttpException
{
    protected RequestInterface $request;
    protected ResponseInterface $response;
    protected array $headers;

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response
    ) {
        $this->request = $request;
        $this->response = $response;
        $this->headers = $response->getHeaders();

        parent::__construct($request, $response);
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }
}
