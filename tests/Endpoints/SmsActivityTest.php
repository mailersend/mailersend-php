<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\SmsActivity;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Builder\SmsActivityParams;
use MailerSend\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Http\Message\ResponseInterface;

class SmsActivityTest extends TestCase
{
    protected SmsActivity $smsActivity;
    protected ResponseInterface $defaultResponse;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = new Client();

        $this->smsActivity = new SmsActivity(new HttpLayer(self::OPTIONS, $this->client), self::OPTIONS);

        $this->defaultResponse = $this->createStub(ResponseInterface::class);
        $this->defaultResponse->method('getStatusCode')->willReturn(200);
    }

    public function test_getAll_uses_get_method_and_correct_uri(): void
    {
        $this->addSuccessResponse();

        $this->smsActivity->getAll(new SmsActivityParams());

        $this->assertRequest('GET', '/v1/sms-activity');
    }

    public function test_getAll_with_sms_number_id(): void
    {
        $this->addSuccessResponse();

        $params = (new SmsActivityParams())->setSmsNumberId('hashed_sms_number_id');
        $this->smsActivity->getAll($params);

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);

        self::assertEquals('hashed_sms_number_id', $query['sms_number_id'] ?? null);
    }

    public function test_getAll_without_sms_number_id(): void
    {
        $this->addSuccessResponse();

        $this->smsActivity->getAll(new SmsActivityParams());

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);

        self::assertArrayNotHasKey('sms_number_id', $query);
    }

    public function test_getAll_with_page(): void
    {
        $this->addSuccessResponse();

        $params = (new SmsActivityParams())->setPage(3);
        $this->smsActivity->getAll($params);

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);

        self::assertEquals(3, $query['page'] ?? null);
    }

    public function test_getAll_without_page(): void
    {
        $this->addSuccessResponse();

        $this->smsActivity->getAll(new SmsActivityParams());

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);

        self::assertArrayNotHasKey('page', $query);
    }

    public function test_getAll_with_limit(): void
    {
        $this->addSuccessResponse();

        $params = (new SmsActivityParams())->setLimit(15);
        $this->smsActivity->getAll($params);

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);

        self::assertEquals(15, $query['limit'] ?? null);
    }

    public function test_getAll_without_limit(): void
    {
        $this->addSuccessResponse();

        $this->smsActivity->getAll(new SmsActivityParams());

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);

        self::assertArrayNotHasKey('limit', $query);
    }

    public function test_getAll_with_date_from_and_date_to(): void
    {
        $this->addSuccessResponse();

        $params = (new SmsActivityParams())
            ->setDateFrom(1623073576)
            ->setDateTo(1623074976);

        $this->smsActivity->getAll($params);

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);

        self::assertEquals(1623073576, $query['date_from'] ?? null);
        self::assertEquals(1623074976, $query['date_to'] ?? null);
    }

    public function test_getAll_with_only_date_from_skips_date_range_validation(): void
    {
        $this->addSuccessResponse();

        // Only date_from set — the greaterThan validation must NOT fire.
        $params = (new SmsActivityParams())->setDateFrom(1623073576);
        $this->smsActivity->getAll($params);

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);

        self::assertEquals(1623073576, $query['date_from'] ?? null);
        self::assertArrayNotHasKey('date_to', $query);
    }

    public function test_getAll_with_only_date_to_skips_date_range_validation(): void
    {
        $this->addSuccessResponse();

        // Only date_to set — the greaterThan validation must NOT fire.
        $params = (new SmsActivityParams())->setDateTo(1623074976);
        $this->smsActivity->getAll($params);

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);

        self::assertEquals(1623074976, $query['date_to'] ?? null);
        self::assertArrayNotHasKey('date_from', $query);
    }

    public function test_getAll_without_dates(): void
    {
        $this->addSuccessResponse();

        $this->smsActivity->getAll(new SmsActivityParams());

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);

        self::assertArrayNotHasKey('date_from', $query);
        self::assertArrayNotHasKey('date_to', $query);
    }

    public function test_getAll_with_status(): void
    {
        $this->addSuccessResponse();

        $params = (new SmsActivityParams())->setStatus(['queued', 'sent']);
        $this->smsActivity->getAll($params);

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);

        self::assertEquals(['queued', 'sent'], $query['status'] ?? null);
    }

    public function test_getAll_with_processed_status(): void
    {
        $this->addSuccessResponse();

        $params = (new SmsActivityParams())->setStatus(['processed']);
        $this->smsActivity->getAll($params);

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);

        self::assertEquals(['processed'], $query['status'] ?? null);
    }

    public function test_getAll_with_all_valid_statuses(): void
    {
        $this->addSuccessResponse();

        $allStatuses = ['processed', 'queued', 'sent', 'delivered', 'failed'];
        $params = (new SmsActivityParams())->setStatus($allStatuses);
        $this->smsActivity->getAll($params);

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);

        self::assertEquals($allStatuses, $query['status'] ?? null);
    }

    public function test_getAll_without_status(): void
    {
        $this->addSuccessResponse();

        $this->smsActivity->getAll(new SmsActivityParams());

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);

        self::assertArrayNotHasKey('status', $query);
    }

    public function test_getAll_with_all_params(): void
    {
        $this->addSuccessResponse();

        $params = (new SmsActivityParams())
            ->setSmsNumberId('hashed_sms_number_id')
            ->setPage(3)
            ->setLimit(15)
            ->setDateFrom(1623073576)
            ->setDateTo(1623074976)
            ->setStatus(['queued', 'sent']);

        $response = $this->smsActivity->getAll($params);

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);

        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/v1/sms-activity', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);
        self::assertEquals('hashed_sms_number_id', $query['sms_number_id'] ?? null);
        self::assertEquals(3, $query['page'] ?? null);
        self::assertEquals(15, $query['limit'] ?? null);
        self::assertEquals(1623073576, $query['date_from'] ?? null);
        self::assertEquals(1623074976, $query['date_to'] ?? null);
        self::assertEquals(['queued', 'sent'], $query['status'] ?? null);
    }

    /**
     * @dataProvider invalidSmsActivityParamsProvider
     * @param SmsActivityParams $smsActivityParams
     * @param string $expectedMessage
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    #[DataProvider('invalidSmsActivityParamsProvider')]
    public function test_getAll_rejects_invalid_params(SmsActivityParams $smsActivityParams, string $expectedMessage): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($expectedMessage);

        $this->smsActivity->getAll($smsActivityParams);
    }

    public static function invalidSmsActivityParamsProvider(): array
    {
        return [
            'limit below minimum' => [
                (new SmsActivityParams())
                    ->setLimit(9),
                'Limit is supposed to be between10 and 100.',
            ],
            'limit above maximum' => [
                (new SmsActivityParams())
                    ->setLimit(101),
                'Limit is supposed to be between10 and 100.',
            ],
            'date_from greater than date_to' => [
                (new SmsActivityParams())
                    ->setDateFrom(1623074976)
                    ->setDateTo(1623074975),
                'Provided "1623074975" is not greater than "1623074976".',
            ],
            'date_from equals date_to' => [
                (new SmsActivityParams())
                    ->setDateFrom(1623074976)
                    ->setDateTo(1623074976),
                'Provided "1623074976" is not greater than "1623074976".',
            ],
            'status contains single invalid value' => [
                (new SmsActivityParams())
                    ->setStatus(['invalid_type', 'queued']),
                'The following statuses are invalid: invalid_type',
            ],
            'status contains multiple invalid values' => [
                (new SmsActivityParams())
                    ->setStatus(['bad_one', 'bad_two']),
                'The following statuses are invalid: bad_one, bad_two',
            ],
            'empty sms number id' => [
                (new SmsActivityParams())
                    ->setSmsNumberId(''),
                'Sms number id is wrong.',
            ],
        ];
    }
}
