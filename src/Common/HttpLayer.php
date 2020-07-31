<?php

namespace MailerSend\Common;

use Http\Client\HttpClient;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;

class HttpLayer
{
    protected ?HttpClient $httpClient;
    protected ?RequestFactoryInterface $requestFactory;
    protected ?ResponseFactoryInterface $responseFactory;

    public function __construct(
        ?ClientInterface $httpClient = null,
        RequestFactoryInterface $requestFactory = null,
        ResponseFactoryInterface $responseFactory = null
    ) {
        $this->httpClient = $httpClient ?: Psr18ClientDiscovery::find();
        $this->requestFactory = $requestFactory ?: Psr17FactoryDiscovery::findRequestFactory();
        $this->responseFactory = $responseFactory ?: Psr17FactoryDiscovery::findResponseFactory();
    }
}