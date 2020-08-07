<?php

namespace MailerSend\Tests\Endpoints;

use Http\Mock\Client;
use MailerSend\Common\HttpLayer;
use MailerSend\Endpoints\Email;
use MailerSend\Exceptions\MailerSendValidationException;
use MailerSend\Helpers\Builder\Attachment;
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

        $response = $this->email->send(
            'test@mailersend.com',
            'Sender',
            [
                [
                    'name' => 'Recipient',
                    'email' => 'recipient@mailersend.com',
                ]
            ],
            'Subject',
            'HTML',
            'Text',
            null,
            [
                'tag'
            ]
        );

        $request = $this->client->getLastRequest();
        $request_body = json_decode((string) $request->getBody(), true);

        self::assertEquals('POST', $request->getMethod());
        self::assertEquals('/v1/email', $request->getUri()->getPath());
        self::assertEquals(200, $response['status_code']);

        self::assertEquals('test@mailersend.com', Arr::get($request_body, 'from.email'));
        self::assertEquals('Sender', Arr::get($request_body, 'from.name'));
        self::assertEquals('Recipient', Arr::get($request_body, 'to.0.name'));
        self::assertEquals('recipient@mailersend.com', Arr::get($request_body, 'to.0.email'));
        self::assertEquals('Subject', Arr::get($request_body, 'subject'));
        self::assertEquals('HTML', Arr::get($request_body, 'html'));
        self::assertEquals('Text', Arr::get($request_body, 'text'));
        self::assertEquals('tag', Arr::get($request_body, 'tags.0'));
    }

    public function test_send_request_recipients_helper(): void
    {
        $this->client->addResponse($this->defaultResponse);

        $recipients = [
            new Recipient('recipient@mailersend.com', 'Recipient')
        ];

        $response = $this->email->send(
            'test@mailersend.com',
            'Sender',
            $recipients,
            'Subject',
            'HTML'
        );

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

        $response = $this->email->send(
            'test@mailersend.com',
            'Sender',
            [
                [
                    'name' => 'Recipient',
                    'email' => 'recipient@mailersend.com',
                ]
            ],
            'Subject',
            'HTML',
            'Text',
            null,
            [],
            [],
            $attachments
        );

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

        $response = $this->email->send(
            'test@mailersend.com',
            'Sender',
            [
                [
                    'name' => 'Recipient',
                    'email' => 'recipient@mailersend.com',
                ]
            ],
            'Subject',
            'HTML',
            'Text',
            null,
            [],
            $vars,
            []
        );

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

        $this->email->send(
            'test@mailersend.com',
            'Sender',
            [
                [
                    'wrong recipient'
                ]
            ],
            'Subject',
            'HTML',
            'Text'
        );
    }
}
