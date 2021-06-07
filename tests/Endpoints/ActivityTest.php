<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\Activity;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Builder\ActivityParams;
use MailerSend\Tests\TestCase;
use Psr\Http\Message\ResponseInterface;

class ActivityTest extends TestCase
{
    protected Activity $activity;
    protected ResponseInterface $defaultResponse;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = new Client();

        $this->activity = new Activity(new HttpLayer(self::OPTIONS, $this->client), self::OPTIONS);

        $this->defaultResponse = $this->createMock(ResponseInterface::class);
        $this->defaultResponse->method('getStatusCode')->willReturn(200);
    }

    /**
     * @dataProvider validActivityParamsProvider
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_get_activities_list(ActivityParams $activityParams)
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $this->client->addResponse($response);

        $response = $this->activity->activityList('domainId', $activityParams);

        $request = $this->client->getLastRequest();

        parse_str($request->getUri()->getQuery(), $query);

        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/v1/activity/domainId', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);

        self::assertEquals($activityParams->getPage(), $query['page']);
        self::assertEquals($activityParams->getLimit(), $query['limit']);
        self::assertEquals($activityParams->getDateFrom(), $query['date_from']);
        self::assertEquals($activityParams->getDateTo(), $query['date_to']);
        self::assertEquals(implode(',', $activityParams->getEvent()), $query['event']);
    }

    /**
     * @dataProvider invalidActivityParamsProvider
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function test_get_activities_list_with_errors(ActivityParams $activityParams)
    {
        $this->expectException(MailerSendAssertException::class);

        $domainId = 'domainId';

        $httpLayer = $this->createMock(HttpLayer::class);
        $httpLayer->method('get')
            ->withAnyParameters()
            ->willReturn([]);

        (new Activity($httpLayer, self::OPTIONS))->activityList($domainId, $activityParams);
    }

    public function validActivityParamsProvider()
    {
        return [
            'no params' => [
                (new ActivityParams()),
            ],
            'with page' => [
                (new ActivityParams())
                    ->setPage(3),
            ],
            'with limit' => [
                (new ActivityParams())
                    ->setLimit(15),
            ],
            'with dates' => [
                (new ActivityParams())
                    ->setDateFrom(1623073576)
                    ->setDateTo(1623074976),
            ],
            'with events' => [
                (new ActivityParams())
                    ->setEvent(['processed', 'sent']),
            ],
            'with all' => [
                (new ActivityParams())
                    ->setPage(3)
                    ->setLimit(15)
                    ->setDateFrom(1623073576)
                    ->setDateTo(1623074976)
                    ->setEvent(['processed', 'sent']),
            ]
        ];
    }

    public function invalidActivityParamsProvider()
    {
        return [
            'limit under 10' => [
                (new ActivityParams())
                    ->setLimit(9),
            ],
            'limit over 100' => [
                (new ActivityParams())
                    ->setLimit(101),
            ],
            'date_from greater than date_to' => [
                (new ActivityParams())
                    ->setDateFrom(1623074976)
                    ->setDateTo(1623074975),
            ],
            'event is not a possible type' => [
                (new ActivityParams())
                    ->setEvent(['invalid_type', 'processed']),
            ],
        ];
    }
}
