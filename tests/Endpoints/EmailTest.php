<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\Email;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Arr;
use MailerSend\Helpers\Builder\Attachment;
use MailerSend\Helpers\Builder\EmailParams;
use MailerSend\Helpers\Builder\Personalization;
use MailerSend\Helpers\Builder\Recipient;
use MailerSend\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class EmailTest extends TestCase
{
    protected Email $email;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = new Client();
        $this->email = new Email(new HttpLayer(self::OPTIONS, $this->client), self::OPTIONS);
    }

    public function test_template_id_doesnt_require_subject_html_or_text(): void
    {
        $recipients = [
            new Recipient('recipient@mailersend.com', 'Recipient')
        ];

        $emailParams = (new EmailParams())
            ->setRecipients($recipients)
            ->setTemplateId('templateId');

        $httpLayer = $this->createStub(HttpLayer::class);
        $httpLayer->method('post')
            ->with('https://api.mailersend.com/v1/email', [
                'to' => [
                    $recipients[0]->toArray()
                ],
                'template_id' => 'templateId',
            ])
            ->willReturn([]);

        $response = (new Email($httpLayer, self::OPTIONS))->send($emailParams);

        self::assertEquals([], $response);
    }

    public function test_language_is_included_in_payload_when_set(): void
    {
        $recipients = [
            new Recipient('recipient@mailersend.com', 'Recipient')
        ];

        $emailParams = (new EmailParams())
            ->setRecipients($recipients)
            ->setTemplateId('templateId')
            ->setLanguage('de');

        $httpLayer = $this->createStub(HttpLayer::class);
        $httpLayer->method('post')
            ->with('https://api.mailersend.com/v1/email', [
                'to' => [
                    $recipients[0]->toArray()
                ],
                'template_id' => 'templateId',
                'language' => 'de',
            ])
            ->willReturn([]);

        $response = (new Email($httpLayer, self::OPTIONS))->send($emailParams);

        self::assertEquals([], $response);
    }

    public function test_language_is_absent_from_payload_when_not_set(): void
    {
        $recipients = [
            new Recipient('recipient@mailersend.com', 'Recipient')
        ];

        $emailParams = (new EmailParams())
            ->setRecipients($recipients)
            ->setTemplateId('templateId');

        $this->addSuccessResponse();

        $this->email->send($emailParams);

        $request_body = $this->assertRequest('POST', '/v1/email');

        self::assertArrayNotHasKey('language', $request_body);
    }

    /**
     * @dataProvider validEmailParamsProvider
     * @param EmailParams $emailParams
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    #[DataProvider('validEmailParamsProvider')]
    public function test_send_email(EmailParams $emailParams): void
    {
        $this->addSuccessResponse();

        $response = $this->email->send($emailParams);

        $request_body = $this->assertRequest('POST', '/v1/email');

        self::assertEquals(200, $response['status_code']);

        self::assertEquals($emailParams->getFrom(), Arr::get($request_body, 'from.email'));
        self::assertEquals($emailParams->getFromName(), Arr::get($request_body, 'from.name'));
        self::assertEquals($emailParams->getReplyTo(), Arr::get($request_body, 'reply_to.email'));
        self::assertEquals($emailParams->getReplyToName(), Arr::get($request_body, 'reply_to.name'));
        self::assertCount(count($emailParams->getRecipients()), Arr::get($request_body, 'to'));
        foreach ($emailParams->getRecipients() as $key => $recipient) {
            $recipient = !is_array($recipient) ? $recipient->toArray() : $recipient;
            self::assertEquals($recipient['name'], Arr::get($request_body, "to.$key.name"));
            self::assertEquals($recipient['email'], Arr::get($request_body, "to.$key.email"));
        }
        self::assertCount(count($emailParams->getCc()), Arr::get($request_body, 'cc') ?? []);
        foreach ($emailParams->getCc() as $key => $cc) {
            $cc = !is_array($cc) ? $cc->toArray() : $cc;
            self::assertEquals($cc['name'], Arr::get($request_body, "cc.$key.name"));
            self::assertEquals($cc['email'], Arr::get($request_body, "cc.$key.email"));
        }
        self::assertCount(count($emailParams->getBcc()), Arr::get($request_body, 'bcc') ?? []);
        foreach ($emailParams->getBcc() as $key => $bcc) {
            $bcc = !is_array($bcc) ? $bcc->toArray() : $bcc;
            self::assertEquals($bcc['name'], Arr::get($request_body, "bcc.$key.name"));
            self::assertEquals($bcc['email'], Arr::get($request_body, "bcc.$key.email"));
        }
        self::assertEquals($emailParams->getSubject(), Arr::get($request_body, 'subject'));
        self::assertEquals($emailParams->getHtml(), Arr::get($request_body, 'html'));
        self::assertEquals($emailParams->getText(), Arr::get($request_body, 'text'));
        self::assertCount(count($emailParams->getTags()), Arr::get($request_body, 'tags') ?? []);
        foreach ($emailParams->getTags() as $key => $tag) {
            self::assertEquals($tag, Arr::get($request_body, "tags.$key"));
        }
        self::assertEquals($emailParams->getTemplateId(), Arr::get($request_body, 'template_id'));

        self::assertCount(count($emailParams->getAttachments()), Arr::get($request_body, 'attachments') ?? []);
        foreach ($emailParams->getAttachments() as $key => $attachment) {
            $attachment = !is_array($attachment) ? $attachment->toArray() : $attachment;
            self::assertEquals($attachment['content'], Arr::get($request_body, "attachments.$key.content"));
            self::assertEquals($attachment['filename'], Arr::get($request_body, "attachments.$key.filename"));
            self::assertEquals($attachment['disposition'], Arr::get($request_body, "attachments.$key.disposition"));
            self::assertEquals($attachment['id'], Arr::get($request_body, "attachments.$key.id"));
        }

        self::assertCount(count($emailParams->getPersonalization()), Arr::get($request_body, 'personalization') ?? []);
        foreach ($emailParams->getPersonalization() as $key => $personalization) {
            $personalization = !is_array($personalization) ? $personalization->toArray() : $personalization;
            self::assertEquals($personalization['email'], Arr::get($request_body, "personalization.$key.email"));
            foreach ($personalization['data'] as $variableKey => $variableValue) {
                self::assertEquals($personalization['data'][$variableKey], Arr::get($request_body, "personalization.$key.data.$variableKey"));
            }
        }
        self::assertCount(count($emailParams->getHeaders()), Arr::get($request_body, 'headers') ?? []);
        foreach ($emailParams->getHeaders() as $key => $header) {
            $header = !is_array($header) ? $header->toArray() : $header;
            self::assertEquals($header['name'], Arr::get($request_body, "headers.$key.name"));
            self::assertEquals($header['value'], Arr::get($request_body, "headers.$key.value"));
        }
        self::assertEquals($emailParams->getSendAt(), Arr::get($request_body, 'send_at'));
        self::assertEquals($emailParams->getPrecedenceBulkHeader(), Arr::get($request_body, 'precedence_bulk'));
        self::assertEquals($emailParams->getInReplyToHeader(), Arr::get($request_body, 'in_reply_to'));
        self::assertEquals($emailParams->trackClicks(), Arr::get($request_body, 'settings.track_clicks'));
        self::assertEquals($emailParams->trackOpens(), Arr::get($request_body, 'settings.track_opens'));
        self::assertEquals($emailParams->trackContent(), Arr::get($request_body, 'settings.track_content'));
        self::assertEquals($emailParams->getListUnsubscribe(), Arr::get($request_body, 'list_unsubscribe'));
        self::assertEquals(
            $emailParams->getReferencesHeader() ?: null,
            Arr::get($request_body, 'references')
        );
    }

    public function test_send_uses_correct_method_and_path(): void
    {
        $this->addSuccessResponse();

        $emailParams = (new EmailParams())
            ->setFrom('test@mailersend.com')
            ->setFromName('Sender')
            ->setRecipients([new Recipient('recipient@mailersend.com', 'Recipient')])
            ->setSubject('Subject')
            ->setText('TEXT');

        $this->email->send($emailParams);

        $this->assertRequest('POST', '/v1/email');
    }

    public function test_send_accepts_valid_send_at(): void
    {
        $this->addSuccessResponse();

        $sendAt = time() + 3600;

        $emailParams = (new EmailParams())
            ->setFrom('test@mailersend.com')
            ->setFromName('Sender')
            ->setReplyTo('reply-to@mailersend.com')
            ->setReplyToName('Reply To')
            ->setRecipients([
                [
                    'name' => 'Recipient',
                    'email' => 'recipient@mailersend.com',
                ]
            ])
            ->setSubject('Subject')
            ->setHtml('HTML')
            ->setText('Text')
            ->setTags(['tag'])
            ->setSendAt($sendAt);

        $this->email->send($emailParams);

        $body = $this->assertRequest('POST', '/v1/email');
        self::assertSame($sendAt, $body['send_at']);
    }

    public function test_send_forwards_status_code(): void
    {
        $this->addSuccessResponse(200);

        $emailParams = (new EmailParams())
            ->setFrom('test@mailersend.com')
            ->setFromName('Sender')
            ->setRecipients([new Recipient('recipient@mailersend.com', 'Recipient')])
            ->setSubject('Subject')
            ->setText('TEXT');

        $response = $this->email->send($emailParams);

        self::assertEquals(200, $response['status_code']);
    }

    public static function validEmailParamsProvider(): array
    {
        return [
            'simple request' => [
                (new EmailParams())
                    ->setFrom('test@mailersend.com')
                    ->setFromName('Sender')
                    ->setReplyTo('reply-to@mailersend.com')
                    ->setReplyToName('Reply To')
                    ->setRecipients([
                        [
                            'name' => 'Recipient',
                            'email' => 'recipient@mailersend.com',
                        ]
                    ])
                    ->setSubject('Subject')
                    ->setHtml('HTML')
                    ->setText('Text')
                    ->setTags([
                        'tag'
                    ]),
            ],
            'using recipients helper' => [
                (new EmailParams())
                    ->setFrom('test@mailersend.com')
                    ->setFromName('Sender')
                    ->setRecipients([
                        new Recipient('recipient@mailersend.com', 'Recipient')
                    ])
                    ->setSubject('Subject')
                    ->setText('TEXT'),
            ],
            'using attachments helper' => [
                (new EmailParams())
                    ->setFrom('test@mailersend.com')
                    ->setFromName('Sender')
                    ->setRecipients([
                        [
                            'name' => 'Recipient',
                            'email' => 'recipient@mailersend.com',
                        ]
                    ])
                    ->setSubject('Subject')
                    ->setHtml('HTML')
                    ->setText('Text')
                    ->setAttachments([
                        new Attachment('attachment', 'file.jpg'),
                    ]),
            ],
            'using personalization helper' => [
                (new EmailParams())
                    ->setFrom('test@mailersend.com')
                    ->setFromName('Sender')
                    ->setRecipients([
                        [
                            'name' => 'Recipient',
                            'email' => 'recipient@mailersend.com',
                        ]
                    ])
                    ->setSubject('Subject')
                    ->setHtml('HTML')
                    ->setText('Text')
                    ->setPersonalization([
                        new Personalization('recipient@mailersend.com', [
                            'var' => 'variable',
                            'number' => 123,
                            'object' => [
                                'key' => 'object-value'
                            ],
                            'objectCollection' => [
                                [
                                    'name' => 'John'
                                ],
                                [
                                    'name' => 'Patrick'
                                ]
                            ],
                        ])
                    ]),
            ],
            'with cc' => [
                (new EmailParams())
                    ->setFrom('test@mailersend.com')
                    ->setFromName('Sender')
                    ->setReplyTo('reply-to@mailersend.com')
                    ->setReplyToName('Reply To')
                    ->setRecipients([
                        new Recipient('recipient@mailersend.com', 'Recipient')
                    ])
                    ->setCc([
                        new Recipient('cc@mailersend.com', 'CC')
                    ])
                    ->setSubject('Subject')
                    ->setText('TEXT'),
            ],
            'with bcc' => [
                (new EmailParams())
                    ->setFrom('test@mailersend.com')
                    ->setFromName('Sender')
                    ->setReplyTo('reply-to@mailersend.com')
                    ->setReplyToName('Reply To')
                    ->setRecipients([
                        new Recipient('recipient@mailersend.com', 'Recipient')
                    ])
                    ->setBcc([
                        new Recipient('bcc@mailersend.com', 'BCC')
                    ])
                    ->setSubject('Subject')
                    ->setText('TEXT'),
            ],
            'without html' => [
                (new EmailParams())
                    ->setFrom('test@mailersend.com')
                    ->setFromName('Sender')
                    ->setReplyTo('reply-to@mailersend.com')
                    ->setReplyToName('Reply To')
                    ->setRecipients([
                        [
                            'name' => 'Recipient',
                            'email' => 'recipient@mailersend.com',
                        ]
                    ])
                    ->setSubject('Subject')
                    ->setText('Text'),
            ],
            'with precedence header' => [
                (new EmailParams())
                    ->setFrom('test@mailersend.com')
                    ->setFromName('Sender')
                    ->setReplyTo('reply-to@mailersend.com')
                    ->setReplyToName('Reply To')
                    ->setRecipients([
                        [
                            'name' => 'Recipient',
                            'email' => 'recipient@mailersend.com',
                        ]
                    ])
                    ->setSubject('Subject')
                    ->setHtml('HTML')
                    ->setText('Text')
                    ->setTags([
                        'tag'
                    ])
                    ->setPrecedenceBulkHeader(true),
            ],
            'with in_reply_to header' => [
                (new EmailParams())
                    ->setFrom('test@mailersend.com')
                    ->setFromName('Sender')
                    ->setReplyTo('reply-to@mailersend.com')
                    ->setReplyToName('Reply To')
                    ->setRecipients([
                        [
                            'name' => 'Recipient',
                            'email' => 'recipient@mailersend.com',
                        ]
                    ])
                    ->setSubject('Subject')
                    ->setHtml('HTML')
                    ->setText('Text')
                    ->setTags([
                        'tag'
                    ])
                    ->setInReplyToHeader('test@mailersend.com'),
            ],
            'with tracking' => [
                (new EmailParams())
                    ->setFrom('test@mailersend.com')
                    ->setFromName('Sender')
                    ->setReplyTo('reply-to@mailersend.com')
                    ->setReplyToName('Reply To')
                    ->setRecipients([
                        [
                            'name' => 'Recipient',
                            'email' => 'recipient@mailersend.com',
                        ]
                    ])
                    ->setSubject('Subject')
                    ->setHtml('HTML')
                    ->setText('Text')
                    ->setTrackClicks(true)
                    ->setTrackOpens(true)
                    ->setTrackContent(true)
            ],
            'with custom headers' => [
                (new EmailParams())
                    ->setFrom('test@mailersend.com')
                    ->setFromName('Sender')
                    ->setReplyTo('reply-to@mailersend.com')
                    ->setReplyToName('Reply To')
                    ->setRecipients([
                        [
                            'name' => 'Recipient',
                            'email' => 'recipient@mailersend.com',
                        ]
                    ])
                    ->setSubject('Subject')
                    ->setHtml('HTML')
                    ->setText('Text')
                    ->setHeaders([
                        [
                          'name' => 'Custom-Header-1',
                          'value' => 'Value 1',
                        ]
                    ])
            ],
            'with references header' => [
                (new EmailParams())
                    ->setFrom('test@mailersend.com')
                    ->setFromName('Sender')
                    ->setReplyTo('reply-to@mailersend.com')
                    ->setReplyToName('Reply To')
                    ->setRecipients([
                        [
                            'name' => 'Recipient',
                            'email' => 'recipient@mailersend.com',
                        ]
                    ])
                    ->setSubject('Subject')
                    ->setHtml('HTML')
                    ->setText('Text')
                    ->setTags([
                        'tag'
                    ])
                    ->setInReplyToHeader('test@mailersend.com')
                    ->setReferencesHeader([
                        '<test@mailersend.com>',
                        '<test2@mailersend.com>'
                    ]),
            ],
            'with list unsubscribe' => [
                (new EmailParams())
                    ->setFrom('test@mailersend.com')
                    ->setFromName('Sender')
                    ->setRecipients([
                        [
                            'name' => 'Recipient',
                            'email' => 'recipient@mailersend.com',
                        ]
                    ])
                    ->setSubject('Subject')
                    ->setHtml('HTML')
                    ->setText('Text')
                    ->setListUnsubscribe('https://www.mailersend.com/unsubscribe'),
            ],
            'with unicode email' => [
                (new EmailParams())
                    ->setFrom('ügyfélszolgálat@mailersend.com')
                    ->setFromName('Ügyfélszolgálat')
                    ->setRecipients([
                        [
                            'name' => 'Recipient',
                            'email' => 'recipient@mailersend.com',
                        ]
                    ])
                    ->setSubject('Subject')
                    ->setHtml('HTML')
                    ->setText('Text'),
            ],
        ];
    }

    public function test_send_rejects_params_that_fail_validation(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('One of template_id, html or text must be supplied');

        $this->email->send(
            (new EmailParams())
                ->setRecipients([new Recipient('recipient@mailersend.com', 'Recipient')])
        );
    }

    public function test_send_excludes_rcpt_to_from_body(): void
    {
        $this->addSuccessResponse();

        $emailParams = (new EmailParams())
            ->setFrom('test@mailersend.com')
            ->setFromName('Sender')
            ->setRecipients([new Recipient('recipient@mailersend.com', 'Recipient')])
            ->setSubject('Subject')
            ->setText('TEXT')
            ->setRcptTo([new Recipient('rcpt@mailersend.com', 'Rcpt To')]);

        $this->email->send($emailParams);

        $body = $this->assertRequest('POST', '/v1/email');

        $this->assertBodyExcludes(['rcptTo'], $body);
    }

    public function test_send_includes_only_the_tracking_settings_that_were_set(): void
    {
        $this->addSuccessResponse();

        $emailParams = (new EmailParams())
            ->setFrom('test@mailersend.com')
            ->setFromName('Sender')
            ->setRecipients([new Recipient('recipient@mailersend.com', 'Recipient')])
            ->setSubject('Subject')
            ->setText('TEXT')
            ->setTrackClicks(false);

        $this->email->send($emailParams);

        $body = $this->assertRequest('POST', '/v1/email');

        self::assertSame(['track_clicks' => false], $body['settings']);
    }

    public function test_send_includes_references_in_body(): void
    {
        $this->addSuccessResponse();

        $references = ['<msg1@mailersend.com>', '<msg2@mailersend.com>'];

        $emailParams = (new EmailParams())
            ->setFrom('test@mailersend.com')
            ->setFromName('Sender')
            ->setRecipients([new Recipient('recipient@mailersend.com', 'Recipient')])
            ->setSubject('Subject')
            ->setText('TEXT')
            ->setInReplyToHeader('<msg1@mailersend.com>')
            ->setReferencesHeader($references);

        $this->email->send($emailParams);

        $body = $this->assertRequest('POST', '/v1/email');

        self::assertArrayHasKey('references', $body);
        self::assertEquals($references, $body['references']);
    }

    public function test_send_includes_list_unsubscribe_in_body(): void
    {
        $this->addSuccessResponse();

        $emailParams = (new EmailParams())
            ->setFrom('test@mailersend.com')
            ->setFromName('Sender')
            ->setRecipients([new Recipient('recipient@mailersend.com', 'Recipient')])
            ->setSubject('Subject')
            ->setText('TEXT')
            ->setListUnsubscribe('https://example.com/unsubscribe');

        $this->email->send($emailParams);

        $body = $this->assertRequest('POST', '/v1/email');

        self::assertArrayHasKey('list_unsubscribe', $body);
        self::assertEquals('https://example.com/unsubscribe', $body['list_unsubscribe']);
    }

    public function test_send_at_accepts_string_value(): void
    {
        $this->addSuccessResponse();

        $sendAt = '2026-01-01T10:00:00+00:00';

        $emailParams = (new EmailParams())
            ->setFrom('test@mailersend.com')
            ->setFromName('Sender')
            ->setRecipients([new Recipient('recipient@mailersend.com', 'Recipient')])
            ->setSubject('Subject')
            ->setText('TEXT')
            ->setSendAt($sendAt);

        $this->email->send($emailParams);

        $body = $this->assertRequest('POST', '/v1/email');
        self::assertEquals($sendAt, $body['send_at']);
    }

    public function test_send_includes_precedence_bulk_false(): void
    {
        $this->addSuccessResponse();

        $emailParams = (new EmailParams())
            ->setFrom('test@mailersend.com')
            ->setFromName('Sender')
            ->setRecipients([new Recipient('recipient@mailersend.com', 'Recipient')])
            ->setSubject('Subject')
            ->setText('TEXT')
            ->setPrecedenceBulkHeader(false);

        $this->email->send($emailParams);

        $body = $this->assertRequest('POST', '/v1/email');
        self::assertArrayHasKey('precedence_bulk', $body);
        self::assertFalse($body['precedence_bulk']);
    }
}
