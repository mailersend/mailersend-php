<?php

namespace MailerSend\Tests\Endpoints;

use MailerSend\Common\Constants;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Builder\OpensAnalyticsParams;
use MailerSend\Tests\TestCase;
use Http\Mock\Client;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\Analytics;
use MailerSend\Helpers\Builder\ActivityAnalyticsParams;
use Psr\Http\Message\ResponseInterface;
use Tightenco\Collect\Support\Arr;

class AnalyticsTest extends TestCase
{
    protected Analytics $analytics;
    protected ResponseInterface $defaultResponse;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = new Client();

        $this->analytics = new Analytics(new HttpLayer(self::OPTIONS, $this->client), self::OPTIONS);

        $this->defaultResponse = $this->createMock(ResponseInterface::class);
        $this->defaultResponse->method('getStatusCode')->willReturn(200);
    }

    /**
     * @dataProvider validActivityAnalyticsProvider
     */
    public function test_get_activity_by_date(ActivityAnalyticsParams $activityAnalyticsParams): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $this->client->addResponse($response);

        $response = $this->analytics->activityDataByDate($activityAnalyticsParams);

        $request = $this->client->getLastRequest();

        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/v1/analytics/date', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);

        parse_str($request->getUri()->getQuery(), $query);

        self::assertEquals($activityAnalyticsParams->getDomainId(), Arr::get($query, 'domain_id'));
        self::assertEquals($activityAnalyticsParams->getDateFrom(), Arr::get($query, 'date_from'));
        self::assertEquals($activityAnalyticsParams->getDateTo(), Arr::get($query, 'date_to'));
        self::assertEquals($activityAnalyticsParams->getGroupBy(), Arr::get($query, 'group_by'));
        self::assertCount(count($activityAnalyticsParams->getTags()), Arr::get($query, 'tags') ?? []);
        self::assertCount(count($activityAnalyticsParams->getEvent()), Arr::get($query, 'event') ?? []);
        foreach ($activityAnalyticsParams->getTags() as $key => $tag) {
            self::assertEquals($tag, Arr::get($query, "tags.$key"));
        }
        foreach ($activityAnalyticsParams->getEvent() as $key => $event) {
            self::assertEquals($event, Arr::get($query, "event.$key"));
        }
    }

    /**
     * @dataProvider invalidActivityAnalyticsProvider
     */
    public function test_get_activities_by_date_with_errors(ActivityAnalyticsParams $activityAnalyticsParams): void
    {
        $this->expectException(MailerSendAssertException::class);

        $httpLayer = $this->createMock(HttpLayer::class);
        $httpLayer->method('get')
            ->withAnyParameters()
            ->willReturn([]);

        (new Analytics($httpLayer, self::OPTIONS))->activityDataByDate($activityAnalyticsParams);
    }

    public function validActivityAnalyticsProvider(): array
    {
        return [
            'basic request' => [
                (new ActivityAnalyticsParams(100, 101))
                    ->setEvent(['queued', 'sent']),
            ],
            'complete request' => [
                (new ActivityAnalyticsParams(100, 101))
                    ->setDomainId('domain_id')
                    ->setGroupBy(Constants::GROUP_BY_DAYS)
                    ->setTags(['tag'])
                    ->setEvent(['queued', 'sent']),
            ],
            'with domain id' => [
                (new ActivityAnalyticsParams(100, 101))
                    ->setDomainId('domain_id')
                    ->setEvent(['queued', 'sent']),
            ],
            'with group by' => [
                (new ActivityAnalyticsParams(100, 101))
                    ->setGroupBy('days')
                    ->setEvent(['queued', 'sent']),
            ],
            'with tag' => [
                (new ActivityAnalyticsParams(100, 101))
                    ->setTags(['tag'])
                    ->setEvent(['queued', 'sent']),
            ],
        ];
    }

    public function invalidActivityAnalyticsProvider(): array
    {
        return [
            'event array is empty' => [
                (new ActivityAnalyticsParams(100, 101)),
            ],
        ];
    }

    /**
     * @dataProvider validOpensAnalyticsProvider
     */
    public function test_opens_by_country(OpensAnalyticsParams $opensAnalyticsParams)
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $this->client->addResponse($response);

        $response = $this->analytics->opensByCountry($opensAnalyticsParams);

        $request = $this->client->getLastRequest();

        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/v1/analytics/country', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);

        parse_str($request->getUri()->getQuery(), $query);

        self::assertEquals($opensAnalyticsParams->getDomainId(), Arr::get($query, 'domain_id'));
        self::assertEquals($opensAnalyticsParams->getDateFrom(), Arr::get($query, 'date_from'));
        self::assertEquals($opensAnalyticsParams->getDateTo(), Arr::get($query, 'date_to'));
        self::assertCount(count($opensAnalyticsParams->getTags()), Arr::get($query, 'tags') ?? []);
        foreach ($opensAnalyticsParams->getTags() as $key => $tag) {
            self::assertEquals($tag, Arr::get($query, "tags.$key"));
        }
    }

    /**
     * @dataProvider validOpensAnalyticsProvider
     */
    public function test_opens_by_user_agent(OpensAnalyticsParams $opensAnalyticsParams)
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $this->client->addResponse($response);

        $response = $this->analytics->opensByUserAgentName($opensAnalyticsParams);

        $request = $this->client->getLastRequest();

        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/v1/analytics/ua-name', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);

        parse_str($request->getUri()->getQuery(), $query);

        self::assertEquals($opensAnalyticsParams->getDomainId(), Arr::get($query, 'domain_id'));
        self::assertEquals($opensAnalyticsParams->getDateFrom(), Arr::get($query, 'date_from'));
        self::assertEquals($opensAnalyticsParams->getDateTo(), Arr::get($query, 'date_to'));
        self::assertCount(count($opensAnalyticsParams->getTags()), Arr::get($query, 'tags') ?? []);
        foreach ($opensAnalyticsParams->getTags() as $key => $tag) {
            self::assertEquals($tag, Arr::get($query, "tags.$key"));
        }
    }

    /**
     * @dataProvider validOpensAnalyticsProvider
     */
    public function test_opens_by_reading_environment(OpensAnalyticsParams $opensAnalyticsParams)
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $this->client->addResponse($response);

        $response = $this->analytics->opensByReadingEnvironment($opensAnalyticsParams);

        $request = $this->client->getLastRequest();

        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/v1/analytics/ua-type', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);

        parse_str($request->getUri()->getQuery(), $query);

        self::assertEquals($opensAnalyticsParams->getDomainId(), Arr::get($query, 'domain_id'));
        self::assertEquals($opensAnalyticsParams->getDateFrom(), Arr::get($query, 'date_from'));
        self::assertEquals($opensAnalyticsParams->getDateTo(), Arr::get($query, 'date_to'));
        self::assertCount(count($opensAnalyticsParams->getTags()), Arr::get($query, 'tags') ?? []);
        foreach ($opensAnalyticsParams->getTags() as $key => $tag) {
            self::assertEquals($tag, Arr::get($query, "tags.$key"));
        }
    }

    public function validOpensAnalyticsProvider(): array
    {
        return [
            'basic request' => [
                new OpensAnalyticsParams(100, 101)
            ],
            'complete request' => [
                (new OpensAnalyticsParams(100, 101))
                    ->setDomainId('domain_id')
                    ->setTags(['tag']),
            ],
            'with domain id' => [
                (new OpensAnalyticsParams(100, 101))
                    ->setDomainId('domain_id'),
            ],
            'with tag' => [
                (new OpensAnalyticsParams(100, 101))
                    ->setTags(['tag']),
            ]
        ];
    }
}
