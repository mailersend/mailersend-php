<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\Domain;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Builder\DomainParams;
use MailerSend\Tests\TestCase;
use Psr\Http\Message\ResponseInterface;
use Tightenco\Collect\Support\Arr;

class DomainTest extends TestCase
{
    protected Domain $domain;
    protected ResponseInterface $defaultResponse;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = new Client();

        $this->domain = new Domain(new HttpLayer(self::OPTIONS, $this->client), self::OPTIONS);

        $this->defaultResponse = $this->createMock(ResponseInterface::class);
        $this->defaultResponse->method('getStatusCode')->willReturn(200);
    }

    /**
     * @dataProvider validDomainParamsProvider
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_domain_list(DomainParams $domainParams)
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $this->client->addResponse($response);

        $response = $this->domain->domainList($domainParams);

        $request = $this->client->getLastRequest();

        parse_str($request->getUri()->getQuery(), $query);

        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/v1/domains', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);

        self::assertEquals($domainParams->getPage(), Arr::get($query, 'page'));
        self::assertEquals($domainParams->getLimit(), Arr::get($query, 'limit'));
        self::assertEquals($domainParams->getVerified(), Arr::get($query, 'verified'));
    }

    /**
     * @dataProvider invalidDomainParamsProvider
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_domain_list_with_errors(DomainParams $domainParams)
    {
        $this->expectException(MailerSendAssertException::class);

        $httpLayer = $this->createMock(HttpLayer::class);
        $httpLayer->method('post')
            ->withAnyParameters()
            ->willReturn([]);

        (new Domain($httpLayer, self::OPTIONS))->domainList($domainParams);
    }

    /**
     * @dataProvider validDomainParamsProvider
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_recipients(DomainParams $domainParams)
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $this->client->addResponse($response);

        $domain_id = 'domain_id';

        $response = $this->domain->recipients($domain_id, $domainParams);

        $request = $this->client->getLastRequest();

        parse_str($request->getUri()->getQuery(), $query);

        self::assertEquals('GET', $request->getMethod());
        self::assertEquals("/v1/domains/$domain_id/recipients", $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);

        self::assertEquals($domainParams->getPage(), Arr::get($query, 'page'));
        self::assertEquals($domainParams->getLimit(), Arr::get($query, 'limit'));
    }

    public function validDomainParamsProvider(): array
    {
        return [
            'empty request' => [
                (new DomainParams()),
            ],
            'with page' => [
                (new DomainParams())
                    ->setPage(1),
            ],
            'with limit' => [
                (new DomainParams())
                    ->setLimit(10),
            ],
            'with verified' => [
                (new DomainParams())
                    ->setVerified(true),
            ],
            'complete request' => [
                (new DomainParams())
                    ->setPage(1)
                    ->setLimit(10)
                    ->setVerified(true),
            ]
        ];
    }

    public function invalidDomainParamsProvider(): array
    {
        return [
            'with limit under 10' => [
                (new DomainParams())
                    ->setLimit(9),
            ],
            'with limit over 100' => [
                (new DomainParams())
                    ->setLimit(101),
            ]
        ];
    }
}
