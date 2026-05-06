<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\Activity;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Builder\ActivityParams;
use MailerSend\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class ActivityTest extends TestCase
{
    protected Activity $activity;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = new Client();

        $this->activity = new Activity(new HttpLayer(self::OPTIONS, $this->client), self::OPTIONS);
    }

    public function test_get_all_uses_correct_method_and_path(): void
    {
        $this->addSuccessResponse();

        $this->activity->getAll('domainId', new ActivityParams());

        $request = $this->client->getLastRequest();
        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/v1/activity/domainId', $request->getUri()->getPath());
    }

    public function test_get_all_forwards_status_code(): void
    {
        $this->addSuccessResponse(200);

        $response = $this->activity->getAll('domainId', new ActivityParams());

        self::assertEquals(200, $response['status_code']);
    }

    public function test_get_all_sends_page_param(): void
    {
        $this->addSuccessResponse();

        $this->activity->getAll('domainId', (new ActivityParams())->setPage(3));

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);
        $this->assertQueryParams(['page' => '3'], $query);
    }

    public function test_get_all_sends_limit_param(): void
    {
        $this->addSuccessResponse();

        $this->activity->getAll('domainId', (new ActivityParams())->setLimit(15));

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);
        $this->assertQueryParams(['limit' => '15'], $query);
    }

    public function test_get_all_sends_date_from_and_date_to_params(): void
    {
        $this->addSuccessResponse();

        $this->activity->getAll('domainId', (new ActivityParams())->setDateFrom(1623073576)->setDateTo(1623074976));

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);
        $this->assertQueryParams(['date_from' => '1623073576', 'date_to' => '1623074976'], $query);
    }

    public function test_get_all_sends_event_param(): void
    {
        $this->addSuccessResponse();

        $this->activity->getAll('domainId', (new ActivityParams())->setEvent(['queued', 'sent']));

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);
        $this->assertQueryParams(['event' => ['queued', 'sent']], $query);
    }

    public function test_get_all_accepts_date_from_without_date_to(): void
    {
        $this->addSuccessResponse();

        $response = $this->activity->getAll('domainId', (new ActivityParams())->setDateFrom(1623073576));

        self::assertEquals(200, $response['status_code']);
    }

    public function test_get_all_accepts_date_to_without_date_from(): void
    {
        $this->addSuccessResponse();

        $response = $this->activity->getAll('domainId', (new ActivityParams())->setDateTo(1623073576));

        self::assertEquals(200, $response['status_code']);
    }

    /**
     * @dataProvider invalidActivityParamsProvider
     * @param string $domainId
     * @param ActivityParams $params
     * @param string $exceptionMessage
     */
    #[DataProvider('invalidActivityParamsProvider')]
    public function test_get_all_rejects_invalid_params(string $domainId, ActivityParams $params, string $exceptionMessage): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $httpLayer = $this->createStub(HttpLayer::class);
        $httpLayer->method('get')->withAnyParameters()->willReturn([]);

        (new Activity($httpLayer, self::OPTIONS))->getAll($domainId, $params);
    }

    public function test_find_uses_correct_method_and_path(): void
    {
        $this->addSuccessResponse();

        $this->activity->find('activity-id');

        $request = $this->client->getLastRequest();
        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/v1/activities/activity-id', $request->getUri()->getPath());
    }

    public function test_find_forwards_status_code(): void
    {
        $this->addSuccessResponse(200);

        $response = $this->activity->find('activity-id');

        self::assertEquals(200, $response['status_code']);
    }

    /**
     * @dataProvider invalidFindParamsProvider
     */
    #[DataProvider('invalidFindParamsProvider')]
    public function test_find_rejects_invalid_params(string $activityId, string $exceptionMessage): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $this->activity->find($activityId);
    }

    public static function invalidFindParamsProvider(): array
    {
        return [
            'missing activity id' => ['', 'Activity id is required.'],
        ];
    }

    public static function invalidActivityParamsProvider(): array
    {
        return [
            'missing domain id' => [
                '',
                new ActivityParams(),
                'Domain id is required.',
            ],
            'limit below minimum' => [
                'domainId',
                (new ActivityParams())->setLimit(9),
                'Limit is supposed to be between 10 and 100.',
            ],
            'limit above maximum' => [
                'domainId',
                (new ActivityParams())->setLimit(101),
                'Limit is supposed to be between 10 and 100.',
            ],
            'date_from greater than date_to' => [
                'domainId',
                (new ActivityParams())->setDateFrom(1623074976)->setDateTo(1623074975),
                'Provided "1623074975" is not greater than "1623074976".',
            ],
            'date_from equal to date_to' => [
                'domainId',
                (new ActivityParams())->setDateFrom(1623074976)->setDateTo(1623074976),
                'Provided "1623074976" is not greater than "1623074976".',
            ],
            'invalid event type' => [
                'domainId',
                (new ActivityParams())->setEvent(['invalid_type', 'queued']),
                'The following types are invalid: invalid_type',
            ],
        ];
    }
}
