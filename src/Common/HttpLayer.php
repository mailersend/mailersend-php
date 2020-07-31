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
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

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
        array $plugins = [],
        array $options = []
    ) {
        $this->options = $options;

        $this->requestFactory = $requestFactory ?: Psr17FactoryDiscovery::findRequestFactory();
        $this->streamFactory = $streamFactory ?: Psr17FactoryDiscovery::findStreamFactory();
        $this->httpClient = new PluginClient(
            $httpClient ?: Psr18ClientDiscovery::find(),
            $plugins ?: $this->buildPlugins()
        );
    }

    /**
     * @param  string  $uri
     * @param  array  $body
     * @param  array  $headers
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function post(string $uri, array $body, array $headers = []): \Psr\Http\Message\ResponseInterface
    {
        $stringBody = json_encode($body, JSON_THROW_ON_ERROR);

        $request = $this->requestFactory->createRequest('POST', $uri)
            ->withBody(
                $this->streamFactory->createStream($stringBody)
            );

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
}