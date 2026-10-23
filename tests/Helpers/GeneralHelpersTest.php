<?php

namespace MailerSend\Tests\Helpers;

use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Builder\EmailParams;
use MailerSend\Helpers\Builder\Recipient;
use MailerSend\Helpers\GeneralHelpers;
use MailerSend\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class GeneralHelpersTest extends TestCase
{
    /**
     * @dataProvider acceptedEmailParamsProvider
     */
    #[DataProvider('acceptedEmailParamsProvider')]
    public function test_validate_email_params_accepts_params_the_api_accepts(EmailParams $emailParams): void
    {
        GeneralHelpers::validateEmailParams($emailParams);

        $this->addToAssertionCount(1);
    }

    public static function acceptedEmailParamsProvider(): array
    {
        return [
            'from name not set' => [
                (new EmailParams())
                    ->setFrom('sender@mailersend.com')
                    ->setRecipients([new Recipient('recipient@mailersend.com', 'Recipient')])
                    ->setSubject('Subject')
                    ->setHtml('HTML'),
            ],
            'template id without from and subject' => [
                (new EmailParams())
                    ->setRecipients([new Recipient('recipient@mailersend.com', 'Recipient')])
                    ->setTemplateId('template-id'),
            ],
            'cc name with comma' => [
                self::validParams()->setCc([new Recipient('cc@mailersend.com', 'Doe, John')]),
            ],
            'cc name with semicolon' => [
                self::validParams()->setCc([new Recipient('cc@mailersend.com', 'Doe; John')]),
            ],
            'bcc name with comma' => [
                self::validParams()->setBcc([new Recipient('bcc@mailersend.com', 'Doe, John')]),
            ],
            'bcc name with semicolon' => [
                self::validParams()->setBcc([new Recipient('bcc@mailersend.com', 'Doe; John')]),
            ],
        ];
    }

    /**
     * @dataProvider invalidEmailParamsProvider
     */
    #[DataProvider('invalidEmailParamsProvider')]
    public function test_validate_email_params_rejects_invalid_params(EmailParams $emailParams, string $expectedMessage): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($expectedMessage);

        GeneralHelpers::validateEmailParams($emailParams);
    }

    public static function invalidEmailParamsProvider(): array
    {
        return [
            'template id, html and text missing' => [
                (new EmailParams())
                    ->setRecipients([new Recipient('recipient@mailersend.com', 'Recipient')]),
                'One of template_id, html or text must be supplied',
            ],
            'from missing without template id' => [
                (new EmailParams())
                    ->setFromName('Sender')
                    ->setRecipients([new Recipient('recipient@mailersend.com', 'Recipient')])
                    ->setSubject('Subject')
                    ->setHtml('HTML'),
                'From email must be a valid email address.',
            ],
            'from not an email address' => [
                self::validParams()->setFrom('not-an-email'),
                'From email must be a valid email address.',
            ],
            'subject missing without template id' => [
                (new EmailParams())
                    ->setFrom('sender@mailersend.com')
                    ->setFromName('Sender')
                    ->setRecipients([new Recipient('recipient@mailersend.com', 'Recipient')])
                    ->setHtml('HTML'),
                'Subject is required.',
            ],
            'no recipients without template id' => [
                self::validParams()->setRecipients([]),
                'At least one recipient is required.',
            ],
            'no recipients with template id' => [
                (new EmailParams())
                    ->setRecipients([])
                    ->setTemplateId('template-id'),
                'At least one recipient is required.',
            ],
            'too many recipients' => [
                self::validParams()->setRecipients(array_map(
                    fn ($i) => new Recipient("recipient{$i}@mailersend.com", "Recipient {$i}"),
                    range(1, 51)
                )),
                'Recipients list should not contain more than 50 items.',
            ],
            'subject too long' => [
                self::validParams()->setSubject(str_repeat('a', 999)),
                'Subject may not be greater than 998 characters.',
            ],
            'subject with CRLF' => [
                self::validParams()->setSubject("Subject\r\nBcc: attacker@evil.com"),
                'Subject must not contain CR or LF characters.',
            ],
            'too many tags' => [
                self::validParams()->setTags(['tag1', 'tag2', 'tag3', 'tag4', 'tag5', 'tag6']),
                'Tags list should not contain more than 5 items.',
            ],
            'tag too long' => [
                self::validParams()->setTags([str_repeat('a', 192)]),
                'Each tag may not be greater than 191 characters.',
            ],
            'send_at in the past' => [
                self::validParams()->setSendAt(1000000000), // 2001-09-09, always in the past
                'Send at must not be in the past.',
            ],
            'send_at more than 72 hours in the future' => [
                self::validParams()->setSendAt(9999999999), // 2286-11-20, always > 72h in the future
                'Send at may not be more than 72 hours in the future.',
            ],
            'in_reply_to too long' => [
                self::validParams()->setInReplyToHeader(str_repeat('a', 999)),
                'In reply to may not be greater than 998 characters.',
            ],
            'in_reply_to with CRLF' => [
                self::validParams()->setInReplyToHeader("legit-123\r\nBcc: attacker@evil.com"),
                'In reply to must not contain CR or LF characters.',
            ],
            'in_reply_to with trailing LF' => [
                self::validParams()->setInReplyToHeader("legit-123\n"),
                'In reply to must not contain CR or LF characters.',
            ],
            'list_unsubscribe too long' => [
                self::validParams()->setListUnsubscribe(str_repeat('a', 991)),
                'List unsubscribe may not be greater than 990 characters.',
            ],
            'list_unsubscribe with CRLF' => [
                self::validParams()->setListUnsubscribe("https://example.com/u\r\nBcc: attacker@evil.com"),
                'List unsubscribe must not contain CR or LF characters.',
            ],
            'list_unsubscribe with trailing LF' => [
                self::validParams()->setListUnsubscribe("https://example.com/u\n"),
                'List unsubscribe must not contain CR or LF characters.',
            ],
            'references with CRLF' => [
                self::validParams()->setReferencesHeader(["<a@example.com>\r\nBcc: attacker@evil.com"]),
                'Each reference must not contain CR or LF characters.',
            ],
            'header name with CRLF' => [
                self::validParams()->setHeaders([['name' => "X-Custom\r\nBcc: attacker@evil.com", 'value' => 'value']]),
                'Header name must not contain CR or LF characters.',
            ],
            'header value with CRLF' => [
                self::validParams()->setHeaders([['name' => 'X-Custom', 'value' => "value\r\nBcc: attacker@evil.com"]]),
                'Header value must not contain CR or LF characters.',
            ],
            'header value with trailing LF' => [
                self::validParams()->setHeaders([['name' => 'X-Custom', 'value' => "value\n"]]),
                'Header value must not contain CR or LF characters.',
            ],
            'too many cc recipients' => [
                self::validParams()->setCc(array_map(
                    fn ($i) => new Recipient("cc{$i}@mailersend.com", "CC {$i}"),
                    range(1, 11)
                )),
                'CC list should not contain more than 10 items.',
            ],
            'cc recipient without email' => [
                self::validParams()->setCc([['name' => 'CC']]),
                'The element with index 0 in CC array does not contain the email parameter.',
            ],
            'too many bcc recipients' => [
                self::validParams()->setBcc(array_map(
                    fn ($i) => new Recipient("bcc{$i}@mailersend.com", "BCC {$i}"),
                    range(1, 11)
                )),
                'BCC list should not contain more than 10 items.',
            ],
            'bcc recipient without email' => [
                self::validParams()->setBcc([['name' => 'BCC']]),
                'The element with index 0 in BCC array does not contain the email parameter.',
            ],
        ];
    }

    public function test_validate_email_params_rejects_params_whose_recipients_were_never_set(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('At least one recipient is required.');

        GeneralHelpers::validateEmailParams(
            (new EmailParams())
                ->setFrom('sender@mailersend.com')
                ->setSubject('Subject')
                ->setHtml('HTML')
        );
    }

    protected static function validParams(): EmailParams
    {
        return (new EmailParams())
            ->setFrom('sender@mailersend.com')
            ->setFromName('Sender')
            ->setRecipients([new Recipient('recipient@mailersend.com', 'Recipient')])
            ->setSubject('Subject')
            ->setHtml('HTML');
    }
}
