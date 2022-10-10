<?php

namespace MailerSend\Exceptions;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class MailerSendValidationException extends MailerSendException
{
    protected RequestInterface $request;
    protected ResponseInterface $response;
    protected string $body;
    protected int $statusCode;
    protected array $errors;

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response
    ) {
        $this->body = $response->getBody()->getContents();
        //Rewind stream to beginning of the file to use it for the second time
        $response->getBody()->rewind();

        $this->statusCode = $response->getStatusCode();

        $data = json_decode($this->body, true, 512, JSON_THROW_ON_ERROR);

        $this->errors = $data['errors'] ?? [];

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

    public function getBody(): string
    {
        return $this->body;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
