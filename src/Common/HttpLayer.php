<?php

namespace MailerSend\Common;

use Http\Client\Common\Plugin\AuthenticationPlugin;
use Http\Client\Common\Plugin\ContentTypePlugin;
use Http\Client\Common\Plugin\HeaderDefaultsPlugin;
use Http\Client\Common\PluginClient;
use Http\Client\HttpClient;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Http\Message\Authentication\Bearer;
use JsonException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

class HttpLayer
{
    protected ?HttpClient $httpClient;
    protected ?RequestFactoryInterface $requestFactory;
    protected ?StreamFactoryInterface $streamFactory;

    protected array $options;

    public function __construct(
        ?ClientInterface $httpClient = null,
        ?RequestFactoryInterface $requestFactory = null,
        ?StreamFactoryInterface $streamFactory = null,
        array $options = []
    ) {
        $this->options = $options;

        $this->requestFactory = $requestFactory ?: Psr17FactoryDiscovery::findRequestFactory();
        $this->streamFactory = $streamFactory ?: Psr17FactoryDiscovery::findStreamFactory();
        $this->httpClient = new PluginClient(
            $httpClient ?: Psr18ClientDiscovery::find(),
            $this->buildPlugins()
        );
    }

    /**
     * @param  string  $uri
     * @param  array  $body
     * @param  array  $headers
     * @throws JsonException
     * @throws ClientExceptionInterface
     */
    public function post(string $uri, array $body, array $headers = []): ResponseInterface
    {
        $request = $this->requestFactory->createRequest('POST', $uri)
            ->withBody($this->buildBody($body));

        return $this->httpClient->sendRequest($request);
    }

    protected function buildPlugins(): array
    {
        $authentication = new Bearer($this->options['api_key']);
        $authenticationPlugin = new AuthenticationPlugin($authentication);

        $contentTypePlugin = new ContentTypePlugin();

        $headerDefaultsPlugin = new HeaderDefaultsPlugin([
            'User-Agent' => 'mailersend-php/'.Constants::SDK_VERSION
        ]);

        return [
            $authenticationPlugin,
            $contentTypePlugin,
            $headerDefaultsPlugin,
        ];
    }

    /**
     * @param  array|string  $body
     * @throws JsonException
     */
    protected function buildBody($body): StreamInterface
    {
        $stringBody = is_array($body) ? json_encode($body, JSON_THROW_ON_ERROR) : $body;

        return $this->streamFactory->createStream($stringBody);
    }
}