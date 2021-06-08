# Guide

Some more advanced usages of our SDK.

*Disclaimer: As the SDK is still in early stages, please refer to `tests/` and `src/` for more info on possible usages.*

# Table of Contents

* [Available Helpers](#helpers)
* [Sending and email with CC and BCC](#cc_and_bcc)
* [Sending an email with variables (simple personalisation)](#variables)
* [Sending an email with personalization (advanced personalisation)](#personalization)
* [Sending a templated email](#templated)
* [Sending an email with attachment](#attachments)
* [Debugging validation errors](#debugging-validation-errors)

<a name="helpers"></a>
# Available helpers

```php
use MailerSend\Helpers\Builder\Attachment;
use MailerSend\Helpers\Builder\Variable;
use MailerSend\Helpers\Builder\Recipient;

// This will help you build the recipient array
$recipients = [
    new Recipient('your@client.com', 'Your Client'),
];

// This will help you build the variable array
$variables = [
    new Variable('your@client.com', ['var' => 'value'])
];

// This will help you build the attachments array and will encode the contents of attachments
$attachments = [
    new Attachment(file_get_contents('attachment.jpg'), 'attachment.jpg')
];
```

<a name="cc_and_bcc"></a>
# Sending an email with CC and BCC

Send an email with CC and BCC.

```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\Recipient;
use MailerSend\Helpers\Builder\EmailParams;

$mailersend = new MailerSend(['api_key' => 'key']);

$recipients = [
    new Recipient('your@client.com', 'Your Client'),
];

$cc = [
    new Recipient('cc@mail.com', 'CC'),
];

$bcc = [
    new Recipient('bcc@mail.com', 'BCC'),
];

$emailParams = (new EmailParams())
    ->setFrom('your@domain.com')
    ->setFromName('Your Name')
    ->setRecipients($recipients)
    ->setCc($cc)
    ->setBcc($bcc)
    ->setSubject('Subject')
    ->setHtml('This is the HTML content')
    ->setText('This is the text content');

$mailersend->email->send($emailParams);
```

<a name="variables"></a>
# Sending an email with variables (simple personalization)

```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\Variable;
use MailerSend\Helpers\Builder\Recipient;
use MailerSend\Helpers\Builder\EmailParams;

$mailersend = new MailerSend(['api_key' => 'key']);

$recipients = [
    new Recipient('your@client.com', 'Your Client'),
];

$variables = [
    new Variable('your@client.com', ['var' => 'value'])
];

$emailParams = (new EmailParams())
    ->setFrom('your@domain.com')
    ->setFromName('Your Name')
    ->setRecipients($recipients)
    ->setSubject('Subject {$var}')
    ->setHtml('This is the html version with a {$var}.')
    ->setText('This is the text versions with a {$var}.')
    ->setVariables($variables);

$mailersend->email->send($emailParams);
```

<a name="personalization"></a>
# Sending an email with personalization (advanced personalization)

```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\Personalization;
use MailerSend\Helpers\Builder\Recipient;
use MailerSend\Helpers\Builder\EmailParams;

$mailersend = new MailerSend(['api_key' => 'key']);

$recipients = [
    new Recipient('your@client.com', 'Your Client'),
];

$personalization = [
    new Personalization('your@client.com', [
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
];

$emailParams = (new EmailParams())
    ->setFrom('your@domain.com')
    ->setFromName('Your Name')
    ->setRecipients($recipients)
    ->setSubject('Subject {$var}')
    ->setHtml('This is the html version with a {$var}.')
    ->setText('This is the text versions with a {$var}.')
    ->setPersonalization($personalization);

$mailersend->email->send($emailParams);
```

<a name="templated"></a>
# Sending a templated email

```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\Variable;
use MailerSend\Helpers\Builder\Recipient;
use MailerSend\Helpers\Builder\EmailParams;

$mailersend = new MailerSend(['api_key' => 'key']);

$recipients = [
    new Recipient('your@client.com', 'Your Client'),
];

$variables = [
    new Variable('your@client.com', ['var' => 'value'])
];

$tags = ['tag'];

$emailParams = (new EmailParams())
    ->setFrom('your@domain.com')
    ->setFromName('Your Name')
    ->setRecipients($recipients)
    ->setSubject('Subject')
    ->setTemplateId('ss243wdasd')
    ->setVariables($variables);

$mailersend->email->send($emailParams);
```

<a name="attachments"></a>
# Sending an email with attachment

```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\Attachment;
use MailerSend\Helpers\Builder\Recipient;
use MailerSend\Helpers\Builder\EmailParams;

$mailersend = new MailerSend(['api_key' => 'key']);

$recipients = [
    new Recipient('your@client.com', 'Your Client'),
];

$attachments = [
    new Attachment(file_get_contents('attachment.jpg'), 'attachment.jpg')
];

$emailParams = (new EmailParams())
    ->setFrom('your@domain.com')
    ->setFromName('Your Name')
    ->setRecipients($recipients)
    ->setSubject('Subject')
    ->setHtml('This is the html version.')
    ->setText('This is the text version.')
    ->setAttachments($attachments);

$mailersend->email->send($emailParams);
```

<a name="debugging-validation-errors"></a>
# Debugging validation errors

```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\Variable;
use MailerSend\Helpers\Builder\Recipient;
use MailerSend\Helpers\Builder\EmailParams;
use MailerSend\Exceptions\MailerSendValidationException;

$mailersend = new MailerSend(['api_key' => 'key']);

$recipients = [
    new Recipient('your@client.com', 'Your Client'),
];

// This should be your@client.com, as in $recipients
$variables = [
    new Variable('your@domain.com', ['var' => 'value'])
];

$emailParams = (new EmailParams())
    ->setFrom('your@domain.com')
    ->setFromName('Your Name')
    ->setRecipients($recipients)
    ->setSubject('Subject {$var}')
    ->setHtml('This is the html version with a {$var}.')
    ->setText('This is the text versions with a {$var}.')
    ->setVariables($variables);

try{
    $mailersend->email->send($emailParams);
} catch(MailerSendValidationException $e){
    // See src/Exceptions/MailerSendValidationException.php for more more info
    print_r($e->getResponse()->getBody()->getContents());
}
```
