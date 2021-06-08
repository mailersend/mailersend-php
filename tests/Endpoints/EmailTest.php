<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\Email;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Exceptions\MailerSendValidationException;
use MailerSend\Helpers\Builder\Attachment;
use MailerSend\Helpers\Builder\EmailParams;
use MailerSend\Helpers\Builder\Recipient;
use MailerSend\Helpers\Builder\Variable;
use MailerSend\Tests\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Tightenco\Collect\Support\Arr;

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

    public function test_send_basic_request(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $this->client->addResponse($response);

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
            ->setTags([
                'tag'
            ])
            ->setPersonalization([
                [
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
                ]
            ])
        ;

        $response = $this->email->send($emailParams);

        $request = $this->client->getLastRequest();
        $request_body = json_decode((string) $request->getBody(), true);

        self::assertEquals('POST', $request->getMethod());
        self::assertEquals('/v1/email', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);

        self::assertEquals('test@mailersend.com', Arr::get($request_body, 'from.email'));
        self::assertEquals('Sender', Arr::get($request_body, 'from.name'));
        self::assertEquals('reply-to@mailersend.com', Arr::get($request_body, 'reply_to.email'));
        self::assertEquals('Reply To', Arr::get($request_body, 'reply_to.name'));
        self::assertEquals('Recipient', Arr::get($request_body, 'to.0.name'));
        self::assertEquals('recipient@mailersend.com', Arr::get($request_body, 'to.0.email'));
        self::assertEquals('Subject', Arr::get($request_body, 'subject'));
        self::assertEquals('HTML', Arr::get($request_body, 'html'));
        self::assertEquals('Text', Arr::get($request_body, 'text'));
        self::assertEquals('tag', Arr::get($request_body, 'tags.0'));
        self::assertEquals('variable', Arr::get($request_body, 'personalization.0.var'));
        self::assertEquals(123, Arr::get($request_body, 'personalization.0.number'));
        self::assertEquals('object-value', Arr::get($request_body, 'personalization.0.object.key'));
        self::assertEquals('John', Arr::get($request_body, 'personalization.0.objectCollection.0.name'));
        self::assertEquals('Patrick', Arr::get($request_body, 'personalization.0.objectCollection.1.name'));
    }

    public function test_send_request_recipients_helper(): void
    {
        $this->client->addResponse($this->defaultResponse);

        $recipients = [
            new Recipient('recipient@mailersend.com', 'Recipient')
        ];

        $emailParams = (new EmailParams())
            ->setFrom('test@mailersend.com')
            ->setFromName('Sender')
            ->setRecipients($recipients)
            ->setSubject('Subject')
            ->setHtml('HTML');

        $response = $this->email->send($emailParams);

        self::assertEquals(200, $response['status_code']);

        $request_body = $this->lastRequestBody();

        self::assertEquals('Recipient', Arr::get($request_body, 'to.0.name'));
        self::assertEquals('recipient@mailersend.com', Arr::get($request_body, 'to.0.email'));
    }

    public function test_send_request_attachments_helper(): void
    {
        $this->client->addResponse($this->defaultResponse);

        $attachments = [
            new Attachment('attachment', 'file.jpg'),
        ];

        $emailParams = (new EmailParams())
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
            ->setAttachments($attachments);

        $response = $this->email->send($emailParams);

        self::assertEquals(200, $response['status_code']);

        $attachment = Arr::get($this->lastRequestBody(), 'attachments.0');

        self::assertEquals('attachment', base64_decode(Arr::get($attachment, 'content')));
        self::assertEquals('file.jpg', Arr::get($attachment, 'filename'));
    }

    public function test_send_request_variables_helper(): void
    {
        $this->client->addResponse($this->defaultResponse);

        $vars = [
            new Variable('recipient@mailersend.com', ['var' => 'value'])
        ];

        $emailParams = (new EmailParams())
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
            ->setVariables($vars);

        $response = $this->email->send($emailParams);

        self::assertEquals(200, $response['status_code']);

        $variable = Arr::get($this->lastRequestBody(), 'variables.0');

        self::assertEquals('recipient@mailersend.com', Arr::get($variable, 'email'));
        self::assertEquals('var', Arr::get($variable, 'substitutions.0.var'));
        self::assertEquals('value', Arr::get($variable, 'substitutions.0.value'));
    }

    public function test_send_request_validation_error(): void
    {
        $this->expectException(MailerSendValidationException::class);
        $this->expectExceptionMessage('Validation Error');

        $responseBody = $this->createMock(StreamInterface::class);
        $responseBody->method('getContents')->willReturn('{"message": "Validation Error"}');

        $validationErrorResponse = $this->createMock(ResponseInterface::class);
        $validationErrorResponse->method('getStatusCode')->willReturn(422);
        $validationErrorResponse->method('getBody')->willReturn($responseBody);
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
            ->setHtml('HTML');

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
                'template_id' => 'templateId'
            ])
            ->willReturn([]);

        $response = (new Email($httpLayer, self::OPTIONS))->send($emailParams);

        // It passes without assertion errors
        self::assertEquals([], $response);
    }

    public function test_without_template_id_requires_params(): void
    {
        $this->expectException(MailerSendAssertException::class);

        $recipients = [
            new Recipient('recipient@mailersend.com', 'Recipient')
        ];

        $emailParams = (new EmailParams())
            ->setRecipients($recipients)
            ->setHtml('HTML')
            ->setText('Text');

        $httpLayer = $this->createMock(HttpLayer::class);
        $httpLayer->method('post')
            ->withAnyParameters()
            ->willReturn([]);

        (new Email($httpLayer, self::OPTIONS))->send($emailParams);
    }
}
