<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\Emails;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Builder\EmailsParams;
use MailerSend\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class EmailsTest extends TestCase
{
    protected Emails $emails;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = new Client();

        $this->emails = new Emails(new HttpLayer(self::OPTIONS, $this->client), self::OPTIONS);
    }

    public function test_get_all_uses_correct_method_and_path(): void
    {
        $this->addSuccessResponse();

        $this->emails->getAll(self::validParams());

        $request = $this->client->getLastRequest();
        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/v1/emails', $request->getUri()->getPath());
    }

    public function test_get_all_forwards_status_code(): void
    {
        $this->addSuccessResponse(200);

        $response = $this->emails->getAll(self::validParams());

        self::assertEquals(200, $response['status_code']);
    }

    public function test_get_all_sends_required_params(): void
    {
        $this->addSuccessResponse();

        $this->emails->getAll(
            (new EmailsParams())
                ->setDomainId('7nxe3yjmeq28vp0k')
                ->setDateFrom(1756263985)
                ->setDateTo(1756350385)
        );

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);
        $this->assertQueryParams([
            'domain_id' => '7nxe3yjmeq28vp0k',
            'date_from' => '1756263985',
            'date_to' => '1756350385',
        ], $query);
    }

    public function test_get_all_sends_page_and_limit_params(): void
    {
        $this->addSuccessResponse();

        $this->emails->getAll(self::validParams()->setPage(3)->setLimit(25));

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);
        $this->assertQueryParams(['page' => '3', 'limit' => '25'], $query);
    }

    public function test_get_all_accepts_datetime_strings_for_dates(): void
    {
        $this->addSuccessResponse();

        $this->emails->getAll(
            (new EmailsParams())
                ->setDomainId('7nxe3yjmeq28vp0k')
                ->setDateFrom('2026-08-26 00:00:00')
                ->setDateTo('2026-08-27 23:59:59')
        );

        $request = $this->client->getLastRequest();
        parse_str($request->getUri()->getQuery(), $query);
        $this->assertQueryParams([
            'date_from' => '2026-08-26 00:00:00',
            'date_to' => '2026-08-27 23:59:59',
        ], $query);
    }

    /**
     * The API rejects a scalar `status` with a 422, so it must go out as
     * repeated indexed array params rather than a comma-joined string.
     */
    public function test_get_all_serializes_status_as_indexed_array_params(): void
    {
        $this->addSuccessResponse();

        $this->emails->getAll(self::validParams()->setStatus(['queued', 'sent', 'delivered']));

        $query = urldecode($this->client->getLastRequest()->getUri()->getQuery());
        self::assertStringContainsString('status[0]=queued', $query);
        self::assertStringContainsString('status[1]=sent', $query);
        self::assertStringContainsString('status[2]=delivered', $query);
        self::assertStringNotContainsString('status=queued', $query);
        self::assertStringNotContainsString('queued,sent', $query);

        parse_str($this->client->getLastRequest()->getUri()->getQuery(), $parsed);
        self::assertEquals(['queued', 'sent', 'delivered'], $parsed['status']);
    }

    public function test_get_all_serializes_interaction_as_indexed_array_params(): void
    {
        $this->addSuccessResponse();

        $this->emails->getAll(self::validParams()->setInteraction(['opened', 'clicked']));

        $query = urldecode($this->client->getLastRequest()->getUri()->getQuery());
        self::assertStringContainsString('interaction[0]=opened', $query);
        self::assertStringContainsString('interaction[1]=clicked', $query);
        self::assertStringNotContainsString('interaction=opened', $query);
        self::assertStringNotContainsString('opened,clicked', $query);

        parse_str($this->client->getLastRequest()->getUri()->getQuery(), $parsed);
        self::assertEquals(['opened', 'clicked'], $parsed['interaction']);
    }

    public function test_get_all_sends_all_optional_filters(): void
    {
        $this->addSuccessResponse();

        $this->emails->getAll(
            self::validParams()
                ->setPage(2)
                ->setLimit(50)
                ->setStatus(['sent'])
                ->setInteraction(['opened'])
                ->setRecipientEmail('rcpt@example.org')
                ->setMessageId('6a8fa9b1902fab56e0ce50aa')
                ->setTemplateId('7nxe3yjmeq28vp0k')
                ->setSubject('Welcome')
                ->setTag('newsletter')
        );

        parse_str($this->client->getLastRequest()->getUri()->getQuery(), $query);
        $this->assertQueryParams([
            'page' => '2',
            'limit' => '50',
            'status' => ['sent'],
            'interaction' => ['opened'],
            'recipient_email' => 'rcpt@example.org',
            'message_id' => '6a8fa9b1902fab56e0ce50aa',
            'template_id' => '7nxe3yjmeq28vp0k',
            'subject' => 'Welcome',
            'tag' => 'newsletter',
        ], $query);
    }

    public function test_get_all_omits_unset_optional_filters(): void
    {
        $this->addSuccessResponse();

        $this->emails->getAll(self::validParams());

        parse_str($this->client->getLastRequest()->getUri()->getQuery(), $query);
        self::assertEquals(['domain_id', 'date_from', 'date_to'], array_keys($query));

        foreach (
            [
                'page',
                'limit',
                'status',
                'interaction',
                'recipient_email',
                'message_id',
                'template_id',
                'subject',
                'tag',
            ] as $key
        ) {
            self::assertArrayNotHasKey($key, $query, "Query must not contain key '$key'.");
        }
    }

    public function test_get_all_parses_list_response_row(): void
    {
        $this->addJsonResponse(self::listResponse());

        $response = $this->emails->getAll(self::validParams());

        $row = $response['body']['data'][0];
        self::assertEquals('6a8fa9b1902fab56e0ce50dd', $row['id']);
        self::assertEquals('sender@example.com', $row['from']);
        self::assertEquals('rcpt@example.org', $row['to']);
        self::assertEquals('Welcome', $row['subject']);
        self::assertNull($row['text']);
        self::assertNull($row['html']);
        self::assertEquals('7nxe3yjmeq28vp0k', $row['template_id']);
        self::assertEquals('7nxe3yjmeq28vp0k', $row['domain_id']);
        self::assertEquals('6a8fa9b1902fab56e0ce50aa', $row['message_id']);
        self::assertEquals('sent', $row['status']);
        self::assertEquals(['newsletter'], $row['tags']);
        self::assertEquals(['opened'], $row['interaction']);
        self::assertNull($row['suppression_reason']);
        self::assertEquals('2026-08-27T16:48:42.000000Z', $row['created_at']);
        self::assertEquals('2026-08-27T16:48:42.000000Z', $row['updated_at']);
        self::assertEquals([['name' => 'X-Custom', 'value' => 'foo']], $row['headers']);
    }

    public function test_get_all_parses_list_response_envelope(): void
    {
        $this->addJsonResponse(self::listResponse());

        $response = $this->emails->getAll(self::validParams());

        $body = $response['body'];
        self::assertCount(1, $body['data']);

        self::assertEquals('https://api.mailersend.com/v1/emails?page=1', $body['links']['first']);
        self::assertNull($body['links']['last']);
        self::assertNull($body['links']['prev']);
        self::assertNull($body['links']['next']);

        self::assertEquals(1, $body['meta']['current_page']);
        self::assertEquals('https://api.mailersend.com/v1/emails?page=1', $body['meta']['current_page_url']);
        self::assertEquals(1, $body['meta']['from']);
        self::assertEquals('https://api.mailersend.com/v1/emails', $body['meta']['path']);
        self::assertEquals(10, $body['meta']['per_page']);
        self::assertEquals(3, $body['meta']['to']);
        self::assertArrayNotHasKey('total', $body['meta']);
        self::assertArrayNotHasKey('last_page', $body['meta']);
    }

    public function test_get_all_parses_empty_page_envelope(): void
    {
        $this->addJsonResponse(self::emptyPageResponse());

        $response = $this->emails->getAll(self::validParams()->setPage(9));

        $body = $response['body'];
        self::assertSame([], $body['data']);
        self::assertEquals('https://api.mailersend.com/v1/emails?page=8', $body['links']['prev']);
        self::assertNull($body['links']['next']);
        self::assertNull($body['links']['last']);
        self::assertEquals(9, $body['meta']['current_page']);
        self::assertNull($body['meta']['from']);
        self::assertNull($body['meta']['to']);
    }

    public function test_find_uses_correct_method_and_path(): void
    {
        $this->addSuccessResponse();

        $this->emails->find('6a8fa9b1902fab56e0ce50dd');

        $request = $this->client->getLastRequest();
        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/v1/email/6a8fa9b1902fab56e0ce50dd', $request->getUri()->getPath());
    }

    public function test_find_forwards_status_code(): void
    {
        $this->addSuccessResponse(200);

        $response = $this->emails->find('6a8fa9b1902fab56e0ce50dd');

        self::assertEquals(200, $response['status_code']);
    }

    public function test_find_parses_recipient_and_activity(): void
    {
        $this->addJsonResponse(self::singleEmailResponse());

        $response = $this->emails->find('6a8fa9b1902fab56e0ce50dd');

        $email = $response['body']['data'];
        self::assertEquals('6a8fa9b1902fab56e0ce50dd', $email['id']);
        self::assertEquals('sent', $email['status']);
        self::assertEquals('Hello there', $email['text']);
        self::assertEquals('<p>Hello there</p>', $email['html']);

        self::assertEquals([
            'id' => '6a8fa9b1902fab56e0ce50bb',
            'email' => 'rcpt@example.org',
            'created_at' => '2026-08-27T16:48:42.000000Z',
            'updated_at' => '2026-08-27T16:48:42.000000Z',
        ], $email['recipient']);

        self::assertCount(2, $email['activity']);
        self::assertEquals([
            'id' => '6a8fa9b1902fab56e0ce50c1',
            'type' => 'sent',
            'created_at' => '2026-08-27T16:48:43.000000Z',
        ], $email['activity'][0]);
        self::assertEquals('opened', $email['activity'][1]['type']);
    }

    public function test_find_parses_email_without_content_tracking(): void
    {
        $payload = self::singleEmailResponse();
        $payload['data']['text'] = null;
        $payload['data']['html'] = null;
        $payload['data']['headers'] = null;

        $this->addJsonResponse($payload);

        $email = $this->emails->find('6a8fa9b1902fab56e0ce50dd')['body']['data'];

        self::assertNull($email['text']);
        self::assertNull($email['html']);
        self::assertNull($email['headers']);
        self::assertCount(2, $email['activity']);
    }

    /**
     * @dataProvider invalidEmailsParamsProvider
     * @param EmailsParams $params
     * @param string $exceptionMessage
     */
    #[DataProvider('invalidEmailsParamsProvider')]
    public function test_get_all_rejects_invalid_params(EmailsParams $params, string $exceptionMessage): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $httpLayer = $this->createStub(HttpLayer::class);
        $httpLayer->method('get')->withAnyParameters()->willReturn([]);

        (new Emails($httpLayer, self::OPTIONS))->getAll($params);
    }

    /**
     * @dataProvider invalidFindParamsProvider
     */
    #[DataProvider('invalidFindParamsProvider')]
    public function test_find_rejects_invalid_params(string $emailId, string $exceptionMessage): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $this->emails->find($emailId);
    }

    public static function invalidFindParamsProvider(): array
    {
        return [
            'missing email id' => ['', 'Email id is required.'],
        ];
    }

    public static function invalidEmailsParamsProvider(): array
    {
        return [
            'missing domain id' => [
                (new EmailsParams())->setDateFrom(1756263985)->setDateTo(1756350385),
                'Domain id is required.',
            ],
            'missing date from' => [
                (new EmailsParams())->setDomainId('7nxe3yjmeq28vp0k')->setDateTo(1756350385),
                'Date from is required.',
            ],
            'missing date to' => [
                (new EmailsParams())->setDomainId('7nxe3yjmeq28vp0k')->setDateFrom(1756263985),
                'Date to is required.',
            ],
            'date_to lower than date_from' => [
                self::validParams()->setDateFrom(1756350385)->setDateTo(1756350384),
                'Date to must be greater than date from.',
            ],
            'date_to equal to date_from' => [
                self::validParams()->setDateFrom(1756350385)->setDateTo(1756350385),
                'Date to must be greater than date from.',
            ],
            'limit below minimum' => [
                self::validParams()->setLimit(9),
                'Limit is supposed to be between 10 and 100.',
            ],
            'limit above maximum' => [
                self::validParams()->setLimit(101),
                'Limit is supposed to be between 10 and 100.',
            ],
            'invalid status' => [
                self::validParams()->setStatus(['invalid_status', 'sent']),
                'The following statuses are invalid: invalid_status',
            ],
            'invalid interaction' => [
                self::validParams()->setInteraction(['invalid_interaction', 'opened']),
                'The following interactions are invalid: invalid_interaction',
            ],
            'invalid recipient email' => [
                self::validParams()->setRecipientEmail('not-an-email'),
                'Recipient email must be a valid email address.',
            ],
            'subject shorter than minimum' => [
                self::validParams()->setSubject('ab'),
                'Subject must be at least 3 characters long.',
            ],
        ];
    }

    protected static function validParams(): EmailsParams
    {
        return (new EmailsParams())
            ->setDomainId('7nxe3yjmeq28vp0k')
            ->setDateFrom(1756263985)
            ->setDateTo(1756350385);
    }

    protected static function listResponse(): array
    {
        return [
            'data' => [
                [
                    'id' => '6a8fa9b1902fab56e0ce50dd',
                    'from' => 'sender@example.com',
                    'to' => 'rcpt@example.org',
                    'subject' => 'Welcome',
                    'text' => null,
                    'html' => null,
                    'template_id' => '7nxe3yjmeq28vp0k',
                    'domain_id' => '7nxe3yjmeq28vp0k',
                    'message_id' => '6a8fa9b1902fab56e0ce50aa',
                    'status' => 'sent',
                    'tags' => ['newsletter'],
                    'interaction' => ['opened'],
                    'suppression_reason' => null,
                    'created_at' => '2026-08-27T16:48:42.000000Z',
                    'updated_at' => '2026-08-27T16:48:42.000000Z',
                    'headers' => [['name' => 'X-Custom', 'value' => 'foo']],
                ],
            ],
            'links' => [
                'first' => 'https://api.mailersend.com/v1/emails?page=1',
                'last' => null,
                'prev' => null,
                'next' => null,
            ],
            'meta' => [
                'current_page' => 1,
                'current_page_url' => 'https://api.mailersend.com/v1/emails?page=1',
                'from' => 1,
                'path' => 'https://api.mailersend.com/v1/emails',
                'per_page' => 10,
                'to' => 3,
            ],
        ];
    }

    protected static function emptyPageResponse(): array
    {
        return [
            'data' => [],
            'links' => [
                'first' => 'https://api.mailersend.com/v1/emails?page=1',
                'last' => null,
                'prev' => 'https://api.mailersend.com/v1/emails?page=8',
                'next' => null,
            ],
            'meta' => [
                'current_page' => 9,
                'current_page_url' => 'https://api.mailersend.com/v1/emails?page=9',
                'from' => null,
                'path' => 'https://api.mailersend.com/v1/emails',
                'per_page' => 10,
                'to' => null,
            ],
        ];
    }

    protected static function singleEmailResponse(): array
    {
        return [
            'data' => [
                'id' => '6a8fa9b1902fab56e0ce50dd',
                'from' => 'sender@example.com',
                'to' => 'rcpt@example.org',
                'subject' => 'Welcome',
                'text' => 'Hello there',
                'html' => '<p>Hello there</p>',
                'template_id' => '7nxe3yjmeq28vp0k',
                'domain_id' => '7nxe3yjmeq28vp0k',
                'message_id' => '6a8fa9b1902fab56e0ce50aa',
                'status' => 'sent',
                'tags' => ['newsletter'],
                'interaction' => ['opened'],
                'suppression_reason' => null,
                'created_at' => '2026-08-27T16:48:42.000000Z',
                'updated_at' => '2026-08-27T16:48:42.000000Z',
                'headers' => [['name' => 'X-Custom', 'value' => 'foo']],
                'recipient' => [
                    'id' => '6a8fa9b1902fab56e0ce50bb',
                    'email' => 'rcpt@example.org',
                    'created_at' => '2026-08-27T16:48:42.000000Z',
                    'updated_at' => '2026-08-27T16:48:42.000000Z',
                ],
                'activity' => [
                    [
                        'id' => '6a8fa9b1902fab56e0ce50c1',
                        'type' => 'sent',
                        'created_at' => '2026-08-27T16:48:43.000000Z',
                    ],
                    [
                        'id' => '6a8fa9b1902fab56e0ce50c2',
                        'type' => 'opened',
                        'created_at' => '2026-08-27T16:49:11.000000Z',
                    ],
                ],
            ],
        ];
    }

    /**
     * @throws \JsonException
     */
    protected function addJsonResponse(array $payload, int $statusCode = 200): void
    {
        $body = $this->createStub(StreamInterface::class);
        $body->method('getContents')->willReturn(json_encode($payload, JSON_THROW_ON_ERROR));

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn($statusCode);
        $response->method('hasHeader')->with('Content-Type')->willReturn(true);
        $response->method('getHeader')->with('Content-Type')->willReturn(['application/json']);
        $response->method('getHeaders')->willReturn(['Content-Type' => ['application/json']]);
        $response->method('getBody')->willReturn($body);

        $this->client->addResponse($response);
    }
}
