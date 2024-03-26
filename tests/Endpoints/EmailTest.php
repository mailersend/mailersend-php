<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use Illuminate\Support\Arr;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\Email;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Exceptions\MailerSendRateLimitException;
use MailerSend\Exceptions\MailerSendValidationException;
use MailerSend\Helpers\Builder\Attachment;
use MailerSend\Helpers\Builder\EmailParams;
use MailerSend\Helpers\Builder\Personalization;
use MailerSend\Helpers\Builder\Recipient;
use MailerSend\Helpers\Builder\Variable;
use MailerSend\Tests\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class EmailTest extends TestCase
{
    protected Email $email;
    protected ResponseInterface $defaultResponse;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = new Client();

        $this->email = new Email(new HttpLayer(self::OPTIONS, $this->client), self::OPTIONS);

        $this->defaultResponse = $this->createMock(ResponseInterface::class);
        $this->defaultResponse->method('getStatusCode')->willReturn(200);
    }

    public function test_send_request_validation_error(): void
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

        $emailParams = (new EmailParams())
            ->setFrom('test@mailersend.com')
            ->setFromName('Sender')
            ->setRecipients([
                [
                    'wrong recipient'
                ]
            ])
            ->setSubject('Subject')
            ->setText('TEXT');

        $this->email->send($emailParams);
    }

    public function test_template_id_doesnt_require_params(): void
    {
        $recipients = [
            new Recipient('recipient@mailersend.com', 'Recipient')
        ];

        $emailParams = (new EmailParams())
            ->setRecipients($recipients)
            ->setTemplateId('templateId');

        $httpLayer = $this->createMock(HttpLayer::class);
        $httpLayer->method('post')
            ->with('https://api.mailersend.com/v1/email', [
                'to' => [
                    $recipients[0]->toArray()
                ],
                'template_id' => 'templateId',
            ])
            ->willReturn([]);

        $response = (new Email($httpLayer, self::OPTIONS))->send($emailParams);

        // It passes without assertion errors
        self::assertEquals([], $response);
    }

    /**
     * @dataProvider validEmailParamsProvider
     * @param EmailParams $emailParams
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_send_email(EmailParams $emailParams): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $this->client->addResponse($response);

        $response = $this->email->send($emailParams);

        $request = $this->client->getLastRequest();
        $request_body = json_decode((string) $request->getBody(), true);

        self::assertEquals('POST', $request->getMethod());
        self::assertEquals('/v1/email', $request->getUri()->getPath());
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
        self::assertCount(count($emailParams->getVariables()), Arr::get($request_body, 'variables') ?? []);
        foreach ($emailParams->getVariables() as $variableKey => $variable) {
            $variable = !is_array($variable) ? $variable->toArray() : $variable;
            self::assertEquals($variable['email'], Arr::get($request_body, "variables.$variableKey.email"));
            foreach ($variable['substitutions'] as $substitutionKey => $substitution) {
                self::assertEquals($substitution['var'], Arr::get($request_body, "variables.$variableKey.substitutions.$substitutionKey.var"));
                self::assertEquals($substitution['value'], Arr::get($request_body, "variables.$variableKey.substitutions.$substitutionKey.value"));
            }
        }
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
    }

    /**
     * @dataProvider invalidEmailParamsProvider
     * @param EmailParams $emailParams
     * @throws MailerSendAssertException
     * @throws \JsonException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function test_send_email_with_errors(EmailParams $emailParams)
    {
        $this->expectException(MailerSendAssertException::class);

        $httpLayer = $this->createMock(HttpLayer::class);
        $httpLayer->method('post')
            ->withAnyParameters()
            ->willReturn([]);

        (new Email($httpLayer, self::OPTIONS))->send($emailParams);
    }

    public function validEmailParamsProvider(): array
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
            'using variables helper' => [
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
                    ->setVariables([
                        new Variable('recipient@mailersend.com', ['var' => 'value'])
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
            'with send at' => [
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
        ];
    }

    public function invalidEmailParamsProvider(): array
    {
        return [
            'template id, html and text missing' => [
                (new EmailParams())
                    ->setRecipients([
                        new Recipient('recipient@mailersend.com', 'Recipient')
                    ])
            ],
            'from is required' => [
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
            ],
            'from name is required' => [
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
            ],
            'subject is required' => [
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
            ],
            'at least one recipients' => [
                (new EmailParams())
                    ->setFrom('sender@mailersend.com')
                    ->setFromName('Sender')
                    ->setReplyTo('reply-to@mailersend.com')
                    ->setReplyToName('Reply To')
                    ->setRecipients([])
                    ->setSubject('Subject')
                    ->setHtml('HTML')
            ],
            'wrongly formed cc' => [
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
            'too many cc recipients' => [
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
            'cc recipient without email' => [
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
            'cc recipient name with ,' => [
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
            'cc recipient name with ;' => [
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
            'wrongly formed bcc' => [
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
            'too many bcc recipients' => [
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
            'bcc recipient without email' => [
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
            ],
            'bcc recipient name with ,' => [
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
            ],
            'bcc recipient name with ;' => [
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
            ],

            'without text param' => [
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
            ],
        ];
    }

    public function test_should_throw_exception_on_rate_limit(): void
    {
        $this->expectException(MailerSendRateLimitException::class);

        $responseBody = $this->createMock(StreamInterface::class);
        $responseBody->method('getContents')->willReturn('{"message": "Too Many Attempts"}');

        $validationErrorResponse = $this->createMock(ResponseInterface::class);
        $validationErrorResponse->method('getStatusCode')->willReturn(429);
        $validationErrorResponse->method('getHeaders')->willReturn([]);
        $this->client->addResponse($validationErrorResponse);

        $emailParams = (new EmailParams())
            ->setFrom('test@mailersend.com')
            ->setFromName('Sender')
            ->setRecipients([
                [
                    'wrong recipient'
                ]
            ])
            ->setSubject('Subject')
            ->setText('TEXT');

        $this->email->send($emailParams);
    }
}
