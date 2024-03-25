<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use Illuminate\Support\Arr;
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
    public function test_get_all(ActivityParams $activityParams): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $this->client->addResponse($response);

        $response = $this->activity->getAll('domainId', $activityParams);

        $request = $this->client->getLastRequest();

        parse_str($request->getUri()->getQuery(), $query);

        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/v1/activity/domainId', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);

        self::assertEquals($activityParams->getPage(), Arr::get($query, 'page'));
        self::assertEquals($activityParams->getLimit(), Arr::get($query, 'limit'));
        self::assertEquals($activityParams->getDateFrom(), Arr::get($query, 'date_from'));
        self::assertEquals($activityParams->getDateTo(), Arr::get($query, 'date_to'));
        self::assertCount(count($activityParams->getEvent()), Arr::get($query, 'event') ?? []);
    }

    /**
     * @dataProvider invalidActivityParamsProvider
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function test_get_all_with_errors(string $domainId, ActivityParams $activityParams): void
    {
        $this->expectException(MailerSendAssertException::class);

        $httpLayer = $this->createMock(HttpLayer::class);
        $httpLayer->method('get')
            ->withAnyParameters()
            ->willReturn([]);

        (new Activity($httpLayer, self::OPTIONS))->getAll($domainId, $activityParams);
    }

    /**
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_find_requires_activity_id(): void
    {
        $this->expectException(MailerSendAssertException::class);

        $this->activity->find('');
    }

    public function validActivityParamsProvider(): array
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
                    ->setEvent(['queued', 'sent']),
            ],
            'with all' => [
                (new ActivityParams())
                    ->setPage(3)
                    ->setLimit(15)
                    ->setDateFrom(1623073576)
                    ->setDateTo(1623074976)
                    ->setEvent(['queued', 'sent']),
            ]
        ];
    }

    public function invalidActivityParamsProvider(): array
    {
        return [
            'missing domain id' => [
                '',
                (new ActivityParams())
                    ->setLimit(10),
            ],
            'limit under 10' => [
                'domainId',
                (new ActivityParams())
                    ->setLimit(9),
            ],
            'limit over 100' => [
                'domainId',
                (new ActivityParams())
                    ->setLimit(101),
            ],
            'date_from greater than date_to' => [
                'domainId',
                (new ActivityParams())
                    ->setDateFrom(1623074976)
                    ->setDateTo(1623074975),
            ],
            'event is not a possible type' => [
                'domainId',
                (new ActivityParams())
                    ->setEvent(['invalid_type', 'queued']),
            ],
        ];
    }
}
