<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use MailerSend\Common\Constants;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\Analytics;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Builder\ActivityAnalyticsParams;
use MailerSend\Helpers\Builder\OpensAnalyticsParams;
use MailerSend\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class AnalyticsTest extends TestCase
{
    protected Analytics $analytics;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = new Client();

        $this->analytics = new Analytics(new HttpLayer(self::OPTIONS, $this->client), self::OPTIONS);
    }

    public function test_activity_by_date_uses_correct_method_and_path(): void
    {
        $this->addSuccessResponse();

        $this->analytics->activityDataByDate(
            (new ActivityAnalyticsParams(100, 101))->setEvent(['sent'])
        );

        $request = $this->client->getLastRequest();
        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/v1/analytics/date', $request->getUri()->getPath());
    }

    public function test_activity_by_date_forwards_status_code(): void
    {
        $this->addSuccessResponse(200);

        $response = $this->analytics->activityDataByDate(
            (new ActivityAnalyticsParams(100, 101))->setEvent(['sent'])
        );

        self::assertEquals(200, $response['status_code']);
    }

    public function test_activity_by_date_sends_required_params(): void
    {
        $this->addSuccessResponse();

        $this->analytics->activityDataByDate(
            (new ActivityAnalyticsParams(100, 101))->setEvent(['queued', 'sent'])
        );

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);

        $this->assertQueryParams(['date_from' => '100', 'date_to' => '101'], $query);
        $this->assertQueryParams(['event' => ['queued', 'sent']], $query);
    }

    public function test_activity_by_date_sends_domain_id(): void
    {
        $this->addSuccessResponse();

        $this->analytics->activityDataByDate(
            (new ActivityAnalyticsParams(100, 101))
                ->setDomainId('domain_id')
                ->setEvent(['sent'])
        );

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);
        $this->assertQueryParams(['domain_id' => 'domain_id'], $query);
    }

    public function test_activity_by_date_sends_group_by(): void
    {
        $this->addSuccessResponse();

        $this->analytics->activityDataByDate(
            (new ActivityAnalyticsParams(100, 101))
                ->setGroupBy(Constants::GROUP_BY_DAYS)
                ->setEvent(['sent'])
        );

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);
        $this->assertQueryParams(['group_by' => 'days'], $query);
    }

    public function test_activity_by_date_sends_tags(): void
    {
        $this->addSuccessResponse();

        $this->analytics->activityDataByDate(
            (new ActivityAnalyticsParams(100, 101))
                ->setTags(['tag1', 'tag2'])
                ->setEvent(['sent'])
        );

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);
        $this->assertQueryParams(['tags' => ['tag1', 'tag2']], $query);
    }

    /**
     * @dataProvider invalidActivityByDateParamsProvider
     * @param ActivityAnalyticsParams $params
     * @param string $exceptionMessage
     */
    #[DataProvider('invalidActivityByDateParamsProvider')]
    public function test_activity_by_date_rejects_invalid_params(ActivityAnalyticsParams $params, string $exceptionMessage): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $httpLayer = $this->createStub(HttpLayer::class);
        $httpLayer->method('get')->withAnyParameters()->willReturn([]);

        (new Analytics($httpLayer, self::OPTIONS))->activityDataByDate($params);
    }

    public function test_opens_by_country_uses_correct_method_and_path(): void
    {
        $this->addSuccessResponse();

        $this->analytics->opensByCountry(new OpensAnalyticsParams(100, 101));

        $request = $this->client->getLastRequest();
        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/v1/analytics/country', $request->getUri()->getPath());
    }

    public function test_opens_by_country_forwards_status_code(): void
    {
        $this->addSuccessResponse(200);

        $response = $this->analytics->opensByCountry(new OpensAnalyticsParams(100, 101));

        self::assertEquals(200, $response['status_code']);
    }

    public function test_opens_by_country_sends_required_params(): void
    {
        $this->addSuccessResponse();

        $this->analytics->opensByCountry(new OpensAnalyticsParams(100, 101));

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);
        $this->assertQueryParams(['date_from' => '100', 'date_to' => '101'], $query);
    }

    public function test_opens_by_country_sends_domain_id(): void
    {
        $this->addSuccessResponse();

        $this->analytics->opensByCountry(
            (new OpensAnalyticsParams(100, 101))->setDomainId('domain_id')
        );

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);
        $this->assertQueryParams(['domain_id' => 'domain_id'], $query);
    }

    public function test_opens_by_country_sends_tags(): void
    {
        $this->addSuccessResponse();

        $this->analytics->opensByCountry(
            (new OpensAnalyticsParams(100, 101))->setTags(['tag1', 'tag2'])
        );

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);
        $this->assertQueryParams(['tags' => ['tag1', 'tag2']], $query);
    }

    /**
     * @dataProvider invalidOpensDateParamsProvider
     * @param OpensAnalyticsParams $params
     * @param string $exceptionMessage
     */
    #[DataProvider('invalidOpensDateParamsProvider')]
    public function test_opens_by_country_rejects_invalid_dates(OpensAnalyticsParams $params, string $exceptionMessage): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $httpLayer = $this->createStub(HttpLayer::class);
        $httpLayer->method('get')->withAnyParameters()->willReturn([]);

        (new Analytics($httpLayer, self::OPTIONS))->opensByCountry($params);
    }

    public function test_opens_by_user_agent_uses_correct_method_and_path(): void
    {
        $this->addSuccessResponse();

        $this->analytics->opensByUserAgentName(new OpensAnalyticsParams(100, 101));

        $request = $this->client->getLastRequest();
        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/v1/analytics/ua-name', $request->getUri()->getPath());
    }

    public function test_opens_by_user_agent_forwards_status_code(): void
    {
        $this->addSuccessResponse(200);

        $response = $this->analytics->opensByUserAgentName(new OpensAnalyticsParams(100, 101));

        self::assertEquals(200, $response['status_code']);
    }

    public function test_opens_by_user_agent_sends_required_params(): void
    {
        $this->addSuccessResponse();

        $this->analytics->opensByUserAgentName(new OpensAnalyticsParams(100, 101));

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);
        $this->assertQueryParams(['date_from' => '100', 'date_to' => '101'], $query);
    }

    public function test_opens_by_user_agent_sends_domain_id(): void
    {
        $this->addSuccessResponse();

        $this->analytics->opensByUserAgentName(
            (new OpensAnalyticsParams(100, 101))->setDomainId('domain_id')
        );

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);
        $this->assertQueryParams(['domain_id' => 'domain_id'], $query);
    }

    public function test_opens_by_user_agent_sends_tags(): void
    {
        $this->addSuccessResponse();

        $this->analytics->opensByUserAgentName(
            (new OpensAnalyticsParams(100, 101))->setTags(['tag1'])
        );

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);
        $this->assertQueryParams(['tags' => ['tag1']], $query);
    }

    /**
     * @dataProvider invalidOpensDateParamsProvider
     * @param OpensAnalyticsParams $params
     * @param string $exceptionMessage
     */
    #[DataProvider('invalidOpensDateParamsProvider')]
    public function test_opens_by_user_agent_rejects_invalid_dates(OpensAnalyticsParams $params, string $exceptionMessage): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $httpLayer = $this->createStub(HttpLayer::class);
        $httpLayer->method('get')->withAnyParameters()->willReturn([]);

        (new Analytics($httpLayer, self::OPTIONS))->opensByUserAgentName($params);
    }

    public function test_opens_by_reading_environment_uses_correct_method_and_path(): void
    {
        $this->addSuccessResponse();

        $this->analytics->opensByReadingEnvironment(new OpensAnalyticsParams(100, 101));

        $request = $this->client->getLastRequest();
        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/v1/analytics/ua-type', $request->getUri()->getPath());
    }

    public function test_opens_by_reading_environment_forwards_status_code(): void
    {
        $this->addSuccessResponse(200);

        $response = $this->analytics->opensByReadingEnvironment(new OpensAnalyticsParams(100, 101));

        self::assertEquals(200, $response['status_code']);
    }

    public function test_opens_by_reading_environment_sends_required_params(): void
    {
        $this->addSuccessResponse();

        $this->analytics->opensByReadingEnvironment(new OpensAnalyticsParams(100, 101));

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);
        $this->assertQueryParams(['date_from' => '100', 'date_to' => '101'], $query);
    }

    public function test_opens_by_reading_environment_sends_domain_id(): void
    {
        $this->addSuccessResponse();

        $this->analytics->opensByReadingEnvironment(
            (new OpensAnalyticsParams(100, 101))->setDomainId('domain_id')
        );

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);
        $this->assertQueryParams(['domain_id' => 'domain_id'], $query);
    }

    public function test_opens_by_reading_environment_sends_tags(): void
    {
        $this->addSuccessResponse();

        $this->analytics->opensByReadingEnvironment(
            (new OpensAnalyticsParams(100, 101))->setTags(['tag1'])
        );

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);
        $this->assertQueryParams(['tags' => ['tag1']], $query);
    }

    /**
     * @dataProvider invalidOpensDateParamsProvider
     * @param OpensAnalyticsParams $params
     * @param string $exceptionMessage
     */
    #[DataProvider('invalidOpensDateParamsProvider')]
    public function test_opens_by_reading_environment_rejects_invalid_dates(OpensAnalyticsParams $params, string $exceptionMessage): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $httpLayer = $this->createStub(HttpLayer::class);
        $httpLayer->method('get')->withAnyParameters()->willReturn([]);

        (new Analytics($httpLayer, self::OPTIONS))->opensByReadingEnvironment($params);
    }

    public static function invalidActivityByDateParamsProvider(): array
    {
        return [
            'missing event' => [
                new ActivityAnalyticsParams(100, 101),
                'The event[] is a required parameter.',
            ],
            'date_to less than date_from' => [
                (new ActivityAnalyticsParams(101, 100))->setEvent(['sent']),
                'The parameter date_to must be greater than date_from.',
            ],
            'date_to equal to date_from' => [
                (new ActivityAnalyticsParams(100, 100))->setEvent(['sent']),
                'The parameter date_to must be greater than date_from.',
            ],
            'invalid event type' => [
                (new ActivityAnalyticsParams(100, 101))->setEvent(['invalid_type']),
                'The following types are invalid: invalid_type',
            ],
            'invalid group_by' => [
                (new ActivityAnalyticsParams(100, 101))->setEvent(['sent'])->setGroupBy('invalid_group'),
                'Value "invalid_group" is not an element of the valid values',
            ],
        ];
    }

    public static function invalidOpensDateParamsProvider(): array
    {
        return [
            'date_to less than date_from' => [
                new OpensAnalyticsParams(101, 100),
                'The parameter date_to must be greater than date_from.',
            ],
            'date_to equal to date_from' => [
                new OpensAnalyticsParams(100, 100),
                'The parameter date_to must be greater than date_from.',
            ],
        ];
    }

}
