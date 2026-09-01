<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\BulkEmail;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Arr;
use MailerSend\Helpers\Builder\Attachment;
use MailerSend\Helpers\Builder\EmailParams;
use MailerSend\Helpers\Builder\Personalization;
use MailerSend\Helpers\Builder\Recipient;
use MailerSend\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class BulkEmailTest extends TestCase
{
    protected BulkEmail $bulkEmail;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = new Client();
        $this->bulkEmail = new BulkEmail(new HttpLayer(self::OPTIONS, $this->client), self::OPTIONS);
    }

    /**
     * @dataProvider validEmailParamsProvider
     * @param array $bulkEmailParams
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    #[DataProvider('validEmailParamsProvider')]
    public function test_send_email(array $bulkEmailParams): void
    {
        $this->addSuccessResponse();

        $response = $this->bulkEmail->send($bulkEmailParams);

        $request_body = $this->assertRequest('POST', '/v1/bulk-email');

        self::assertEquals(200, $response['status_code']);

        foreach ($bulkEmailParams as $key => $emailParams) {
            self::assertEquals($emailParams->getFrom(), Arr::get($request_body, "$key.from.email"));
            self::assertEquals($emailParams->getFromName(), Arr::get($request_body, "$key.from.name"));
            self::assertEquals($emailParams->getReplyTo(), Arr::get($request_body, "$key.reply_to.email"));
            self::assertEquals($emailParams->getReplyToName(), Arr::get($request_body, "$key.reply_to.name"));
            self::assertCount(count($emailParams->getRecipients()), Arr::get($request_body, "$key.to"));

            foreach ($emailParams->getRecipients() as $k => $recipient) {
                $recipient = !is_array($recipient) ? $recipient->toArray() : $recipient;
                self::assertEquals($recipient['name'], Arr::get($request_body, "$key.to.$k.name"));
                self::assertEquals($recipient['email'], Arr::get($request_body, "$key.to.$k.email"));
            }

            self::assertCount(count($emailParams->getCc()), Arr::get($request_body, "$key.cc") ?? []);
            foreach ($emailParams->getCc() as $k => $cc) {
                $cc = !is_array($cc) ? $cc->toArray() : $cc;
                self::assertEquals($cc['name'], Arr::get($request_body, "$key.cc.$k.name"));
                self::assertEquals($cc['email'], Arr::get($request_body, "$key.cc.$k.email"));
            }

            self::assertCount(count($emailParams->getBcc()), Arr::get($request_body, "$key.bcc") ?? []);
            foreach ($emailParams->getBcc() as $k => $bcc) {
                $bcc = !is_array($bcc) ? $bcc->toArray() : $bcc;
                self::assertEquals($bcc['name'], Arr::get($request_body, "$key.bcc.$k.name"));
                self::assertEquals($bcc['email'], Arr::get($request_body, "$key.bcc.$k.email"));
            }
            self::assertEquals($emailParams->getSubject(), Arr::get($request_body, "$key.subject"));
            self::assertEquals($emailParams->getHtml(), Arr::get($request_body, "$key.html"));
            self::assertEquals($emailParams->getText(), Arr::get($request_body, "$key.text"));
            self::assertCount(count($emailParams->getTags()), Arr::get($request_body, "$key.tags") ?? []);
            foreach ($emailParams->getTags() as $k => $tag) {
                self::assertEquals($tag, Arr::get($request_body, "$key.tags.$k"));
            }
            self::assertEquals($emailParams->getTemplateId(), Arr::get($request_body, "$key.template_id"));

            self::assertCount(count($emailParams->getAttachments()), Arr::get($request_body, "$key.attachments") ?? []);
            foreach ($emailParams->getAttachments() as $k => $attachment) {
                $attachment = !is_array($attachment) ? $attachment->toArray() : $attachment;
                self::assertEquals($attachment['content'], Arr::get($request_body, "$key.attachments.$k.content"));
                self::assertEquals($attachment['filename'], Arr::get($request_body, "$key.attachments.$k.filename"));
                self::assertEquals($attachment['disposition'], Arr::get($request_body, "$key.attachments.$k.disposition"));
                self::assertEquals($attachment['id'], Arr::get($request_body, "$key.attachments.$k.id"));
            }

            self::assertCount(count($emailParams->getPersonalization()), Arr::get($request_body, "$key.personalization") ?? []);
            foreach ($emailParams->getPersonalization() as $k => $personalization) {
                $personalization = !is_array($personalization) ? $personalization->toArray() : $personalization;
                self::assertEquals($personalization['email'], Arr::get($request_body, "$key.personalization.$k.email"));
                foreach ($personalization['data'] as $variableKey => $variableValue) {
                    self::assertEquals($personalization['data'][$variableKey], Arr::get($request_body, "$key.personalization.$k.data.$variableKey"));
                }
            }
        }
    }

    public function test_send_accepts_valid_send_at(): void
    {
        $this->addSuccessResponse();

        $bulkEmailParams = [
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
                ->setTags(['tag'])
                ->setSendAt(time() + 3600),
        ];

        $response = $this->bulkEmail->send($bulkEmailParams);

        self::assertEquals(200, $response['status_code']);
    }

    public static function validEmailParamsProvider(): array
    {
        return [
            'simple request' => [
                [
                    (new EmailParams())
                        ->setFrom('test@mailersend.com')
                        ->setFromName('Sender')
                        ->setReplyTo('reply-to@mailersend.com')
                        ->setReplyToName('Reply To')
                        ->setRecipients([
                            [
                                'name' => 'Recipient1',
                                'email' => 'recipient1@mailersend.com',
                            ]
                        ])
                        ->setSubject('Subject')
                        ->setHtml('HTML')
                        ->setText('Text')
                        ->setTags([
                            'tag'
                        ]),
                    (new EmailParams())
                        ->setFrom('test@mailersend.com')
                        ->setFromName('Sender')
                        ->setReplyTo('reply-to@mailersend.com')
                        ->setReplyToName('Reply To')
                        ->setRecipients([
                            [
                                'name' => 'Recipient2',
                                'email' => 'recipient2@mailersend.com',
                            ]
                        ])
                        ->setSubject('Subject')
                        ->setHtml('HTML')
                        ->setText('Text')
                        ->setTags([
                            'tag'
                        ]),
                ]
            ],
            'using recipients helper' => [
                [
                    (new EmailParams())
                        ->setFrom('test@mailersend.com')
                        ->setFromName('Sender')
                        ->setRecipients([
                            new Recipient('recipient1@mailersend.com', 'Recipient')
                        ])
                        ->setSubject('Subject')
                        ->setText('TEXT'),
                    (new EmailParams())
                        ->setFrom('test@mailersend.com')
                        ->setFromName('Sender')
                        ->setRecipients([
                            new Recipient('recipient2@mailersend.com', 'Recipient')
                        ])
                        ->setSubject('Subject')
                        ->setText('TEXT'),
                ]
            ],
            'using attachments helper' => [
                [
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
                            new Attachment('attachment1', 'file1.jpg'),
                        ]),
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
                            new Attachment('attachment2', 'file2.jpg'),
                        ]),
                ]
            ],
            'using personalization helper' => [
                [
                    (new EmailParams())
                        ->setFrom('test@mailersend.com')
                        ->setFromName('Sender')
                        ->setRecipients([
                            [
                                'name' => 'Recipient',
                                'email' => 'recipient1@mailersend.com',
                            ]
                        ])
                        ->setSubject('Subject')
                        ->setHtml('HTML')
                        ->setText('Text')
                        ->setPersonalization([
                            new Personalization('recipient1@mailersend.com', [
                                'var1' => 'variable1',
                                'number1' => 1231,
                                'object1' => [
                                    'key1' => 'object-value1'
                                ],
                                'objectCollection1' => [
                                    [
                                        'name1' => 'John1'
                                    ],
                                    [
                                        'name1' => 'Patrick1'
                                    ]
                                ],
                            ])
                        ]),
                    (new EmailParams())
                        ->setFrom('test@mailersend.com')
                        ->setFromName('Sender')
                        ->setRecipients([
                            [
                                'name' => 'Recipient',
                                'email' => 'recipient2@mailersend.com',
                            ]
                        ])
                        ->setSubject('Subject')
                        ->setHtml('HTML')
                        ->setText('Text')
                        ->setPersonalization([
                            new Personalization('recipient2@mailersend.com', [
                                'var2' => 'variable2',
                                'number2' => 1232,
                                'object2' => [
                                    'key2' => 'object-value2'
                                ],
                                'objectCollection2' => [
                                    [
                                        'name2' => 'John2'
                                    ],
                                    [
                                        'name2' => 'Patrick2'
                                    ]
                                ],
                            ])
                        ]),
                ]
            ],
            'with cc' => [
                [
                    (new EmailParams())
                        ->setFrom('test@mailersend.com')
                        ->setFromName('Sender')
                        ->setReplyTo('reply-to@mailersend.com')
                        ->setReplyToName('Reply To')
                        ->setRecipients([
                            new Recipient('recipient@mailersend.com', 'Recipient')
                        ])
                        ->setCc([
                            new Recipient('cc1@mailersend.com', 'CC1')
                        ])
                        ->setSubject('Subject')
                        ->setText('TEXT'),
                    (new EmailParams())
                        ->setFrom('test@mailersend.com')
                        ->setFromName('Sender')
                        ->setReplyTo('reply-to@mailersend.com')
                        ->setReplyToName('Reply To')
                        ->setRecipients([
                            new Recipient('recipient@mailersend.com', 'Recipient')
                        ])
                        ->setCc([
                            new Recipient('cc2@mailersend.com', 'CC2')
                        ])
                        ->setSubject('Subject')
                        ->setText('TEXT'),
                ]
            ],
            'with bcc' => [
                [
                    (new EmailParams())
                        ->setFrom('test@mailersend.com')
                        ->setFromName('Sender')
                        ->setReplyTo('reply-to@mailersend.com')
                        ->setReplyToName('Reply To')
                        ->setRecipients([
                            new Recipient('recipient@mailersend.com', 'Recipient')
                        ])
                        ->setBcc([
                            new Recipient('bcc1@mailersend.com', 'BCC1')
                        ])
                        ->setSubject('Subject')
                        ->setText('TEXT'),
                    (new EmailParams())
                        ->setFrom('test@mailersend.com')
                        ->setFromName('Sender')
                        ->setReplyTo('reply-to@mailersend.com')
                        ->setReplyToName('Reply To')
                        ->setRecipients([
                            new Recipient('recipient@mailersend.com', 'Recipient')
                        ])
                        ->setBcc([
                            new Recipient('bcc2@mailersend.com', 'BCC2')
                        ])
                        ->setSubject('Subject')
                        ->setText('TEXT'),
                ]
            ],
            'without html' => [
                [
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
                        ->setText('Text')
                ]
            ],
            'with precedence header' => [
                [
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
            ],
            'with in_reply_to header' => [
                [
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
            ],
            'with unicode email' => [
                [
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
            ],
        ];
    }

    /**
     * @dataProvider invalidEmailParamsProvider
     * @param array $bulkEmailParams
     * @param string $expectedMessage
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    #[DataProvider('invalidEmailParamsProvider')]
    public function test_send_rejects_invalid_params(array $bulkEmailParams, string $expectedMessage): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($expectedMessage);

        $httpLayer = $this->createStub(HttpLayer::class);
        $httpLayer->method('post')->withAnyParameters()->willReturn([]);

        (new BulkEmail($httpLayer, self::OPTIONS))->send($bulkEmailParams);
    }

    public static function invalidEmailParamsProvider(): array
    {
        return [
            'no emails added' => [
                [],
                'Bulk params should contain at least 1 email',
            ],
            'one of the emails has error' => [
                [
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
                        ->setText('Text'),
                    (new EmailParams())
                        ->setFromName('Sender')
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
                'was expected to be a valid e-mail address',
            ],
            'template id, html and text missing' => [
                [
                    (new EmailParams())
                        ->setRecipients([
                            new Recipient('recipient@mailersend.com', 'Recipient')
                        ])
                ],
                'One of template_id, html or text must be supplied',
            ],
            'from is required' => [
                [
                    (new EmailParams())
                        ->setFromName('Sender')
                        ->setRecipients([
                            [
                                'name' => 'Recipient',
                                'email' => 'recipient@mailersend.com',
                            ]
                        ])
                        ->setSubject('Subject')
                        ->setHtml('HTML')
                ],
                'was expected to be a valid e-mail address',
            ],
            'from name is required' => [
                [
                    (new EmailParams())
                        ->setFrom('sender@mailersend.com')
                        ->setRecipients([
                            [
                                'name' => 'Recipient',
                                'email' => 'recipient@mailersend.com',
                            ]
                        ])
                        ->setSubject('Subject')
                        ->setHtml('HTML')
                ],
                'expected to be string, type NULL given',
            ],
            'subject is required' => [
                [
                    (new EmailParams())
                        ->setFrom('sender@mailersend.com')
                        ->setFromName('Sender')
                        ->setRecipients([
                            [
                                'name' => 'Recipient',
                                'email' => 'recipient@mailersend.com',
                            ]
                        ])
                        ->setHtml('HTML')
                ],
                'expected to be string, type NULL given',
            ],
            'at least one recipient required' => [
                [
                    (new EmailParams())
                        ->setFrom('sender@mailersend.com')
                        ->setFromName('Sender')
                        ->setRecipients([])
                        ->setSubject('Subject')
                        ->setHtml('HTML')
                ],
                'List should have at least 1 elements',
            ],
            'wrongly formed cc' => [
                [
                    (new EmailParams())
                        ->setFrom('test@mailersend.com')
                        ->setFromName('Sender')
                        ->setRecipients([
                            new Recipient('recipient@mailersend.com', 'Recipient')
                        ])
                        ->setCc([
                            [
                                'wrong cc',
                            ]
                        ])
                        ->setSubject('Subject')
                        ->setHtml('HTML'),
                ],
                'does not contain the email parameter',
            ],
            'too many cc recipients' => [
                [
                    (new EmailParams())
                        ->setFrom('test@mailersend.com')
                        ->setFromName('Sender')
                        ->setRecipients([
                            new Recipient('recipient@mailersend.com', 'Recipient')
                        ])
                        ->setCc([
                            new Recipient('cc+1@mailersend.com', 'CC 1'),
                            new Recipient('cc+2@mailersend.com', 'CC 2'),
                            new Recipient('cc+3@mailersend.com', 'CC 3'),
                            new Recipient('cc+4@mailersend.com', 'CC 4'),
                            new Recipient('cc+5@mailersend.com', 'CC 5'),
                            new Recipient('cc+6@mailersend.com', 'CC 6'),
                            new Recipient('cc+7@mailersend.com', 'CC 7'),
                            new Recipient('cc+8@mailersend.com', 'CC 8'),
                            new Recipient('cc+9@mailersend.com', 'CC 9'),
                            new Recipient('cc+10@mailersend.com', 'CC 10'),
                            new Recipient('cc+11@mailersend.com', 'CC 11'),
                        ])
                        ->setSubject('Subject')
                        ->setHtml('HTML'),
                ],
                'List should have at most 10 elements',
            ],
            'cc recipient without email' => [
                [
                    (new EmailParams())
                        ->setFrom('test@mailersend.com')
                        ->setFromName('Sender')
                        ->setRecipients([
                            new Recipient('recipient@mailersend.com', 'Recipient')
                        ])
                        ->setCc([
                            [
                                'name' => 'CC'
                            ]
                        ])
                        ->setSubject('Subject')
                        ->setHtml('HTML'),
                ],
                'does not contain the email parameter',
            ],
            'cc recipient name with comma' => [
                [
                    (new EmailParams())
                        ->setFrom('test@mailersend.com')
                        ->setFromName('Sender')
                        ->setRecipients([
                            new Recipient('recipient@mailersend.com', 'Recipient')
                        ])
                        ->setCc([
                            new Recipient('cc@mailersend.com', 'CC,'),
                        ])
                        ->setSubject('Subject')
                        ->setHtml('HTML'),
                ],
                'does not equal expected value',
            ],
            'cc recipient name with semicolon' => [
                [
                    (new EmailParams())
                        ->setFrom('test@mailersend.com')
                        ->setFromName('Sender')
                        ->setRecipients([
                            new Recipient('recipient@mailersend.com', 'Recipient')
                        ])
                        ->setCc([
                            new Recipient('cc@mailersend.com', 'CC;'),
                        ])
                        ->setSubject('Subject')
                        ->setHtml('HTML'),
                ],
                'does not equal expected value',
            ],
            'wrongly formed bcc' => [
                [
                    (new EmailParams())
                        ->setFrom('test@mailersend.com')
                        ->setFromName('Sender')
                        ->setRecipients([
                            new Recipient('recipient@mailersend.com', 'Recipient')
                        ])
                        ->setBcc([
                            [
                                'wrong bcc',
                            ]
                        ])
                        ->setSubject('Subject')
                        ->setHtml('HTML'),
                ],
                'does not contain the email parameter',
            ],
            'too many bcc recipients' => [
                [
                    (new EmailParams())
                        ->setFrom('test@mailersend.com')
                        ->setFromName('Sender')
                        ->setRecipients([
                            new Recipient('recipient@mailersend.com', 'Recipient')
                        ])
                        ->setBcc([
                            new Recipient('bcc+1@mailersend.com', 'BCC 1'),
                            new Recipient('bcc+2@mailersend.com', 'BCC 2'),
                            new Recipient('bcc+3@mailersend.com', 'BCC 3'),
                            new Recipient('bcc+4@mailersend.com', 'BCC 4'),
                            new Recipient('bcc+5@mailersend.com', 'BCC 5'),
                            new Recipient('bcc+6@mailersend.com', 'BCC 6'),
                            new Recipient('bcc+7@mailersend.com', 'BCC 7'),
                            new Recipient('bcc+8@mailersend.com', 'BCC 8'),
                            new Recipient('bcc+9@mailersend.com', 'BCC 9'),
                            new Recipient('bcc+10@mailersend.com', 'BCC 10'),
                            new Recipient('bcc+11@mailersend.com', 'BCC 11'),
                        ])
                        ->setSubject('Subject')
                        ->setHtml('HTML'),
                ],
                'List should have at most 10 elements',
            ],
            'bcc recipient without email' => [
                [
                    (new EmailParams())
                        ->setFrom('test@mailersend.com')
                        ->setFromName('Sender')
                        ->setRecipients([
                            new Recipient('recipient@mailersend.com', 'Recipient')
                        ])
                        ->setBcc([
                            [
                                'name' => 'BCC'
                            ]
                        ])
                        ->setSubject('Subject')
                        ->setHtml('HTML'),
                ],
                'does not contain the email parameter',
            ],
            'bcc recipient name with comma' => [
                [
                    (new EmailParams())
                        ->setFrom('test@mailersend.com')
                        ->setFromName('Sender')
                        ->setRecipients([
                            new Recipient('recipient@mailersend.com', 'Recipient')
                        ])
                        ->setBcc([
                            new Recipient('bcc@mailersend.com', 'BCC,'),
                        ])
                        ->setSubject('Subject')
                        ->setHtml('HTML'),
                ],
                'does not equal expected value',
            ],
            'bcc recipient name with semicolon' => [
                [
                    (new EmailParams())
                        ->setFrom('test@mailersend.com')
                        ->setFromName('Sender')
                        ->setRecipients([
                            new Recipient('recipient@mailersend.com', 'Recipient')
                        ])
                        ->setBcc([
                            new Recipient('bcc@mailersend.com', 'BCC;'),
                        ])
                        ->setSubject('Subject')
                        ->setHtml('HTML'),
                ],
                'does not equal expected value',
            ],
            'text missing when no template id' => [
                [
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
                ],
                'One of template_id, html or text must be supplied',
            ],
            'too many recipients' => [
                [
                    (new EmailParams())
                        ->setFrom('test@mailersend.com')
                        ->setFromName('Sender')
                        ->setRecipients(array_map(
                            fn ($i) => new Recipient("recipient{$i}@mailersend.com", "Recipient {$i}"),
                            range(1, 51)
                        ))
                        ->setSubject('Subject')
                        ->setHtml('HTML'),
                ],
                'Recipients list should not contain more than 50 items.',
            ],
            'subject too long' => [
                [
                    (new EmailParams())
                        ->setFrom('test@mailersend.com')
                        ->setFromName('Sender')
                        ->setRecipients([new Recipient('recipient@mailersend.com', 'Recipient')])
                        ->setSubject(str_repeat('a', 999))
                        ->setHtml('HTML'),
                ],
                'Subject may not be greater than 998 characters.',
            ],
            'too many tags' => [
                [
                    (new EmailParams())
                        ->setFrom('test@mailersend.com')
                        ->setFromName('Sender')
                        ->setRecipients([new Recipient('recipient@mailersend.com', 'Recipient')])
                        ->setSubject('Subject')
                        ->setHtml('HTML')
                        ->setTags(['tag1', 'tag2', 'tag3', 'tag4', 'tag5', 'tag6']),
                ],
                'Tags list should not contain more than 5 items.',
            ],
            'tag too long' => [
                [
                    (new EmailParams())
                        ->setFrom('test@mailersend.com')
                        ->setFromName('Sender')
                        ->setRecipients([new Recipient('recipient@mailersend.com', 'Recipient')])
                        ->setSubject('Subject')
                        ->setHtml('HTML')
                        ->setTags([str_repeat('a', 192)]),
                ],
                'Each tag may not be greater than 191 characters.',
            ],
            'send_at in the past' => [
                [
                    (new EmailParams())
                        ->setFrom('test@mailersend.com')
                        ->setFromName('Sender')
                        ->setRecipients([new Recipient('recipient@mailersend.com', 'Recipient')])
                        ->setSubject('Subject')
                        ->setHtml('HTML')
                        ->setSendAt(1000000000), // 2001-09-09, always in the past
                ],
                'Send at must not be in the past.',
            ],
            'send_at more than 72 hours in the future' => [
                [
                    (new EmailParams())
                        ->setFrom('test@mailersend.com')
                        ->setFromName('Sender')
                        ->setRecipients([new Recipient('recipient@mailersend.com', 'Recipient')])
                        ->setSubject('Subject')
                        ->setHtml('HTML')
                        ->setSendAt(9999999999), // 2286-11-20, always > 72h in the future
                ],
                'Send at may not be more than 72 hours in the future.',
            ],
            'in_reply_to too long' => [
                [
                    (new EmailParams())
                        ->setFrom('test@mailersend.com')
                        ->setFromName('Sender')
                        ->setRecipients([new Recipient('recipient@mailersend.com', 'Recipient')])
                        ->setSubject('Subject')
                        ->setHtml('HTML')
                        ->setInReplyToHeader(str_repeat('a', 999)),
                ],
                'In reply to may not be greater than 998 characters.',
            ],
            'list_unsubscribe too long' => [
                [
                    (new EmailParams())
                        ->setFrom('test@mailersend.com')
                        ->setFromName('Sender')
                        ->setRecipients([new Recipient('recipient@mailersend.com', 'Recipient')])
                        ->setSubject('Subject')
                        ->setHtml('HTML')
                        ->setListUnsubscribe(str_repeat('a', 991)),
                ],
                'List unsubscribe may not be greater than 990 characters.',
            ],
        ];
    }

    public function test_get_status_returns_correct_method_and_path(): void
    {
        $this->addSuccessResponse();

        $this->bulkEmail->getStatus('bulk-email-id-123');

        $body = $this->assertRequest('GET', '/v1/bulk-email/bulk-email-id-123');
        self::assertEmpty($body);
    }

    public function test_get_status_requires_bulk_email_id(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Bulk email id is required.');

        $this->bulkEmail->getStatus('');
    }

    public function test_send_with_template_id_only(): void
    {
        $this->addSuccessResponse();

        $bulkEmailParams = [
            (new EmailParams())
                ->setTemplateId('template-abc')
                ->setRecipients([
                    new Recipient('recipient@mailersend.com', 'Recipient')
                ]),
        ];

        $this->bulkEmail->send($bulkEmailParams);

        $body = $this->assertRequest('POST', '/v1/bulk-email');
        self::assertEquals('template-abc', $body[0]['template_id']);
        $this->assertBodyExcludes(['subject', 'from'], $body[0]);
    }

    public function test_send_with_tracking_settings(): void
    {
        $this->addSuccessResponse();

        $bulkEmailParams = [
            (new EmailParams())
                ->setFrom('test@mailersend.com')
                ->setFromName('Sender')
                ->setRecipients([
                    new Recipient('recipient@mailersend.com', 'Recipient')
                ])
                ->setSubject('Subject')
                ->setText('Text')
                ->setTrackClicks(true)
                ->setTrackOpens(false)
                ->setTrackContent(true),
        ];

        $this->bulkEmail->send($bulkEmailParams);

        $body = $this->assertRequest('POST', '/v1/bulk-email');
        self::assertTrue($body[0]['settings']['track_clicks']);
        self::assertFalse($body[0]['settings']['track_opens']);
        self::assertTrue($body[0]['settings']['track_content']);
    }

    public function test_send_with_headers(): void
    {
        $this->addSuccessResponse();

        $bulkEmailParams = [
            (new EmailParams())
                ->setFrom('test@mailersend.com')
                ->setFromName('Sender')
                ->setRecipients([
                    new Recipient('recipient@mailersend.com', 'Recipient')
                ])
                ->setSubject('Subject')
                ->setText('Text')
                ->setHeaders([['name' => 'X-Custom-Header', 'value' => 'custom-value']]),
        ];

        $this->bulkEmail->send($bulkEmailParams);

        $body = $this->assertRequest('POST', '/v1/bulk-email');
        self::assertNotEmpty($body[0]['headers']);
        self::assertEquals('X-Custom-Header', $body[0]['headers'][0]['name']);
        self::assertEquals('custom-value', $body[0]['headers'][0]['value']);
    }

    public function test_send_with_references_header(): void
    {
        $this->addSuccessResponse();

        $bulkEmailParams = [
            (new EmailParams())
                ->setFrom('test@mailersend.com')
                ->setFromName('Sender')
                ->setRecipients([
                    new Recipient('recipient@mailersend.com', 'Recipient')
                ])
                ->setSubject('Subject')
                ->setText('Text')
                ->setReferencesHeader(['<ref1@mailersend.com>', '<ref2@mailersend.com>']),
        ];

        $this->bulkEmail->send($bulkEmailParams);

        $body = $this->assertRequest('POST', '/v1/bulk-email');
        self::assertEquals(['<ref1@mailersend.com>', '<ref2@mailersend.com>'], $body[0]['references']);
    }

    public function test_send_with_list_unsubscribe(): void
    {
        $this->addSuccessResponse();

        $bulkEmailParams = [
            (new EmailParams())
                ->setFrom('test@mailersend.com')
                ->setFromName('Sender')
                ->setRecipients([
                    new Recipient('recipient@mailersend.com', 'Recipient')
                ])
                ->setSubject('Subject')
                ->setText('Text')
                ->setListUnsubscribe('https://unsubscribe.example.com'),
        ];

        $this->bulkEmail->send($bulkEmailParams);

        $body = $this->assertRequest('POST', '/v1/bulk-email');
        self::assertEquals('https://unsubscribe.example.com', $body[0]['list_unsubscribe']);
    }

    public function test_send_with_rcpt_to(): void
    {
        $this->addSuccessResponse();

        $bulkEmailParams = [
            (new EmailParams())
                ->setFrom('test@mailersend.com')
                ->setFromName('Sender')
                ->setRecipients([
                    new Recipient('recipient@mailersend.com', 'Recipient')
                ])
                ->setSubject('Subject')
                ->setText('Text')
                ->setRcptTo([
                    new Recipient('rcpt@mailersend.com', 'RCPT To')
                ]),
        ];

        $this->bulkEmail->send($bulkEmailParams);

        $body = $this->assertRequest('POST', '/v1/bulk-email');
        self::assertNotEmpty($body[0]['rcptTo']);
        self::assertEquals('rcpt@mailersend.com', $body[0]['rcptTo'][0]['email']);
    }

    public function test_send_at_accepts_string_value(): void
    {
        $this->addSuccessResponse();

        $sendAt = '2026-01-01T10:00:00+00:00';

        $bulkEmailParams = [
            (new EmailParams())
                ->setFrom('test@mailersend.com')
                ->setFromName('Sender')
                ->setRecipients([
                    new Recipient('recipient@mailersend.com', 'Recipient')
                ])
                ->setSubject('Subject')
                ->setText('Text')
                ->setSendAt($sendAt),
        ];

        $this->bulkEmail->send($bulkEmailParams);

        $body = $this->assertRequest('POST', '/v1/bulk-email');
        self::assertEquals($sendAt, $body[0]['send_at']);
    }

    public function test_send_includes_precedence_bulk_false(): void
    {
        $this->addSuccessResponse();

        $bulkEmailParams = [
            (new EmailParams())
                ->setFrom('test@mailersend.com')
                ->setFromName('Sender')
                ->setRecipients([
                    new Recipient('recipient@mailersend.com', 'Recipient')
                ])
                ->setSubject('Subject')
                ->setText('Text')
                ->setPrecedenceBulkHeader(false),
        ];

        $this->bulkEmail->send($bulkEmailParams);

        $body = $this->assertRequest('POST', '/v1/bulk-email');
        self::assertArrayHasKey('precedence_bulk', $body[0]);
        self::assertFalse($body[0]['precedence_bulk']);
    }

    public function test_send_uses_correct_method_and_path(): void
    {
        $this->addSuccessResponse();

        $this->bulkEmail->send([
            (new EmailParams())
                ->setFrom('test@mailersend.com')
                ->setFromName('Sender')
                ->setRecipients([new Recipient('recipient@mailersend.com', 'Recipient')])
                ->setSubject('Subject')
                ->setText('Text'),
        ]);

        $this->assertRequest('POST', '/v1/bulk-email');
    }

    public function test_send_forwards_status_code(): void
    {
        $this->addSuccessResponse();

        $response = $this->bulkEmail->send([
            (new EmailParams())
                ->setFrom('test@mailersend.com')
                ->setFromName('Sender')
                ->setRecipients([new Recipient('recipient@mailersend.com', 'Recipient')])
                ->setSubject('Subject')
                ->setText('Text'),
        ]);

        self::assertEquals(200, $response['status_code']);
    }

    public function test_send_requires_at_least_one_email(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Bulk params should contain at least 1 email');

        $this->bulkEmail->send([]);
    }
}
