<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\BulkEmail;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Exceptions\MailerSendValidationException;
use MailerSend\Helpers\Builder\Attachment;
use MailerSend\Helpers\Builder\EmailParams;
use MailerSend\Helpers\Builder\Personalization;
use MailerSend\Helpers\Builder\Recipient;
use MailerSend\Helpers\Builder\Variable;
use MailerSend\Tests\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Tightenco\Collect\Support\Arr;

class BulkEmailTest extends TestCase
{
    protected BulkEmail $bulkEmail;
    protected ResponseInterface $defaultResponse;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = new Client();

        $this->bulkEmail = new BulkEmail(new HttpLayer(self::OPTIONS, $this->client), self::OPTIONS);

        $this->defaultResponse = $this->createMock(ResponseInterface::class);
        $this->defaultResponse->method('getStatusCode')->willReturn(200);
    }

    public function test_bulk_send_request_validation_error(): void
    {
        $this->expectException(MailerSendValidationException::class);
        $this->expectExceptionMessage('Validation Error');

        $responseBody = $this->createMock(StreamInterface::class);
        $responseBody->method('getContents')->willReturn('{"message": "Validation Error", "errors": []}');

        $validationErrorResponse = $this->createMock(ResponseInterface::class);
        $validationErrorResponse->method('getStatusCode')->willReturn(422);
        $validationErrorResponse->method('getBody')->willReturn($responseBody);
        $validationErrorResponse->method('getHeaders')->willReturn([]);
        $this->client->addResponse($validationErrorResponse);

        $bulkEmailParams[] = (new EmailParams())
            ->setFrom('test@mailersend.com')
            ->setFromName('Sender')
            ->setRecipients([
                [
                    'wrong recipient'
                ]
            ])
            ->setSubject('Subject')
            ->setText('TEXT');

        $this->bulkEmail->send($bulkEmailParams);
    }

    /**
     * @dataProvider validEmailParamsProvider
     * @param array $bulkEmailParams
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_send_email(array $bulkEmailParams): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $this->client->addResponse($response);

        $response = $this->bulkEmail->send($bulkEmailParams);

        $request = $this->client->getLastRequest();
        $request_body = json_decode((string) $request->getBody(), true);

        self::assertEquals('POST', $request->getMethod());
        self::assertEquals('/v1/bulk-email', $request->getUri()->getPath());
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
            self::assertCount(count($emailParams->getVariables()), Arr::get($request_body, "$key.variables") ?? []);
            foreach ($emailParams->getVariables() as $variableKey => $variable) {
                $variable = !is_array($variable) ? $variable->toArray() : $variable;
                self::assertEquals($variable['email'], Arr::get($request_body, "$key.variables.$variableKey.email"));
                foreach ($variable['substitutions'] as $substitutionKey => $substitution) {
                    self::assertEquals($substitution['var'], Arr::get($request_body, "$key.variables.$variableKey.substitutions.$substitutionKey.var"));
                    self::assertEquals($substitution['value'], Arr::get($request_body, "$key.variables.$variableKey.substitutions.$substitutionKey.value"));
                }
            }
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

    /**
     * @dataProvider invalidEmailParamsProvider
     * @param array $emailParams
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_send_email_with_errors(array $bulkEmailParams)
    {
        $this->expectException(MailerSendAssertException::class);

        $httpLayer = $this->createMock(HttpLayer::class);
        $httpLayer->method('post')
            ->withAnyParameters()
            ->willReturn([]);

        $this->bulkEmail->send($bulkEmailParams);
    }

    public function validEmailParamsProvider()
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
            'using variables helper' => [
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
                        ->setVariables([
                            new Variable('recipient1@mailersend.com', ['var1' => 'value1'])
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
                        ->setVariables([
                            new Variable('recipient2@mailersend.com', ['var2' => 'value2'])
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
            'with send at' => [
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
                        ->setSendAt(1665626400),
                ],
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
        ];
    }

    public function invalidEmailParamsProvider()
    {
        return [
            'no emails added' => [
                []
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
                ]
            ],
            'template id, html and text missing' => [
                [
                    (new EmailParams())
                        ->setRecipients([
                            new Recipient('recipient@mailersend.com', 'Recipient')
                        ])
                ]
            ],
            'from is required' => [
                [
                    (new EmailParams())
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
                ]
            ],
            'from name is required' => [
                [
                    (new EmailParams())
                        ->setFrom('sender@mailersend.com')
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
                ]
            ],
            'subject is required' => [
                [
                    (new EmailParams())
                        ->setFrom('sender@mailersend.com')
                        ->setFromName('Sender')
                        ->setReplyTo('reply-to@mailersend.com')
                        ->setReplyToName('Reply To')
                        ->setRecipients([
                            [
                                'name' => 'Recipient',
                                'email' => 'recipient@mailersend.com',
                            ]
                        ])
                        ->setHtml('HTML')
                ]
            ],
            'at least one recipients' => [
                [
                    (new EmailParams())
                        ->setFrom('sender@mailersend.com')
                        ->setFromName('Sender')
                        ->setReplyTo('reply-to@mailersend.com')
                        ->setReplyToName('Reply To')
                        ->setRecipients([])
                        ->setSubject('Subject')
                        ->setHtml('HTML')
                ]
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
                ]
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
                ]
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
                ]
            ],
            'cc recipient name with ,' => [
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
                ]
            ],
            'cc recipient name with ;' => [
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
                ]
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
                ]
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
                ]
            ],
            'bcc recipient without email' => [
                [
                    (new EmailParams())
                        ->setFrom('test@mailersend.com')
                        ->setFromName('Sender')
                        ->setRecipients([
                            new Recipient('recipient@mailersend.com', 'Recipient')
                        ])
                        ->setCc([
                            [
                                'name' => 'BCC'
                            ]
                        ])
                        ->setSubject('Subject')
                        ->setHtml('HTML'),
                ]
            ],
            'bcc recipient name with ,' => [
                [
                    (new EmailParams())
                        ->setFrom('test@mailersend.com')
                        ->setFromName('Sender')
                        ->setRecipients([
                            new Recipient('recipient@mailersend.com', 'Recipient')
                        ])
                        ->setCc([
                            new Recipient('bcc@mailersend.com', 'BCC,'),
                        ])
                        ->setSubject('Subject')
                        ->setHtml('HTML'),
                ]
            ],
            'bcc recipient name with ;' => [
                [
                    (new EmailParams())
                        ->setFrom('test@mailersend.com')
                        ->setFromName('Sender')
                        ->setRecipients([
                            new Recipient('recipient@mailersend.com', 'Recipient')
                        ])
                        ->setCc([
                            new Recipient('bcc@mailersend.com', 'BCC;'),
                        ])
                        ->setSubject('Subject')
                        ->setHtml('HTML'),
                ]
            ],

            'without text param' => [
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
                ]
            ],
        ];
    }

    /**
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_get_status_requires_bulk_email_id(): void
    {
        $this->expectException(MailerSendAssertException::class);

        $this->bulkEmail->getStatus('');
    }
}
