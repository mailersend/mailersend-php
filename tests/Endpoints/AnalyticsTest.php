<?php

namespace MailerSend\Tests\Endpoints;

use MailerSend\Exceptions\MailerSendAssertException;
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
    public function test_get_activity_by_date(ActivityAnalyticsParams $activityAnalyticsParams)
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
        self::assertEquals(implode(',', $activityAnalyticsParams->getEvent()), Arr::get($query, 'event'));
    }

    /**
     * @dataProvider invalidActivityAnalyticsProvider
     */
    public function test_get_activities_by_date_with_errors(ActivityAnalyticsParams $activityAnalyticsParams)
    {
        $this->expectException(MailerSendAssertException::class);

        $httpLayer = $this->createMock(HttpLayer::class);
        $httpLayer->method('get')
            ->withAnyParameters()
            ->willReturn([]);

        (new Analytics($httpLayer, self::OPTIONS))->activityDataByDate($activityAnalyticsParams);
    }

    public function validActivityAnalyticsProvider()
    {
        return [
            'complete request' => [
                (new ActivityAnalyticsParams(100, 101))
                    ->setDomainId('domain_id')
                    ->setGroupBy('days')
                    ->setTags(['tag'])
                    ->setEvent(['processed', 'sent']),
            ],
            'with domain id' => [
                (new ActivityAnalyticsParams(100, 101))
                    ->setDomainId('domain_id')
                    ->setEvent(['processed', 'sent']),
            ],
            'with group by' => [
                (new ActivityAnalyticsParams(100, 101))
                    ->setGroupBy('days')
                    ->setEvent(['processed', 'sent']),
            ],
            'with tag' => [
                (new ActivityAnalyticsParams(100, 101))
                    ->setTags(['tag'])
                    ->setEvent(['processed', 'sent']),
            ],
        ];
    }

    public function invalidActivityAnalyticsProvider()
    {
        return [
            'event array is empty' => [
                (new ActivityAnalyticsParams(100, 101)),
            ],
        ];
    }
}
