# Guide

Some more advanced usages of our SDK.

*Disclaimer: As the SDK is still in early stages, please refer to `tests/` and `src/` for more info on possible usages.*

# Table of Contents

* [Available Helpers](#helpers)
* [Sending an email with variables (personalisation)](#variables)
* [Sending a templated email](#templated)
* [Sending an email with attachment](#attachments)

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
    new Variable('your@domain.com', ['var' => 'value'])
];

// This will help you build the attachments array and will encode the contents of attachments
$attachments = [
    new Attachment(file_get_contents('attachment.jpg'), 'attachment.jpg')
];
```

<a name="variables"></a>
# Sending an email with variables (personalisation)

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
    ->setSubject('Subject {$var}')
    ->setHtml('This is the html version with a {$var}.')
    ->setText('This is the text versions with a {$var}.')
    ->setVariables($variables);

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
