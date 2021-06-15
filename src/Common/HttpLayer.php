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
use MailerSend\Helpers\HttpErrorHelper;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

class HttpLayer
{
    protected ?HttpClient $pluginClient;
    protected ?RequestFactoryInterface $requestFactory;
    protected ?StreamFactoryInterface $streamFactory;

    protected array $options;

    public function __construct(
        array $options = [],
        ?ClientInterface $httpClient = null,
        ?RequestFactoryInterface $requestFactory = null,
        ?StreamFactoryInterface $streamFactory = null
    ) {
        $this->options = $options;

        $httpClient = $httpClient ?: Psr18ClientDiscovery::find();
        $this->pluginClient = new PluginClient($httpClient, $this->buildPlugins());

        $this->requestFactory = $requestFactory ?: Psr17FactoryDiscovery::findRequestFactory();
        $this->streamFactory = $streamFactory ?: Psr17FactoryDiscovery::findStreamFactory();
    }

    /**
     * @throws ClientExceptionInterface
     * @throws JsonException
     */
    public function get(string $uri, array $body = []): array
    {
        return $this->call_method('GET', $uri, $body);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws JsonException
     */
    public function post(string $uri, array $body): array
    {
        return $this->call_method('POST', $uri, $body);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws JsonException
     */
    public function put(string $uri, array $body): array
    {
        return $this->call_method('PUT', $uri, $body);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws JsonException
     */
    public function delete(string $uri, array $body = []): array
    {
        return $this->call_method('DELETE', $uri, $body);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws JsonException
     */
    protected function call_method(string $method, string $uri, array $body): array
    {
        $request = $this->requestFactory->createRequest($method, $uri)
            ->withBody($this->buildBody($body));

        return $this->buildResponse($this->pluginClient->sendRequest($request));
    }

    /**
     * @throws JsonException
     * @throws ClientExceptionInterface
     */
    public function request(string $method, string $uri, string $body = ''): array
    {
        $request = $this->requestFactory->createRequest($method, $uri);

        if (!empty($body)) {
            $request = $request->withBody($this->streamFactory->createStream($body));
        }

        return $this->buildResponse($this->pluginClient->sendRequest($request));
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

    /**
     * @throws JsonException
     */
    protected function buildResponse(ResponseInterface $response): array
    {
        $contentTypes = $response->getHeader('Content-Type');
        $contentType = $response->hasHeader('Content-Type') ?
            reset($contentTypes) : null;

        $body = '';

        if ($response->getBody()) {
            switch ($contentType) {
                case 'application/json':
                    $body = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
                    break;
                default:
                    $body = $response->getBody()->getContents();
            }
        }

        return [
            'status_code' => $response->getStatusCode(),
            'headers' => $response->getHeaders(),
            'body' => $body,
            'response' => $response,
        ];
    }

    protected function buildPlugins(): array
    {
        $authentication = new Bearer($this->options['api_key']);
        $authenticationPlugin = new AuthenticationPlugin($authentication);

        $contentTypePlugin = new ContentTypePlugin();

        $headerDefaultsPlugin = new HeaderDefaultsPlugin([
            'User-Agent' => 'mailersend-php/'.Constants::SDK_VERSION
        ]);

        $httpErrorPlugin = new HttpErrorHelper();

        return [
            $authenticationPlugin,
            $contentTypePlugin,
            $headerDefaultsPlugin,
            $httpErrorPlugin
        ];
    }
}
