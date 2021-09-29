<a href="https://www.mailersend.com"><img src="https://www.mailersend.com/images/logo.svg" width="200px"/></a>

MailerSend PHP SDK

[![MIT licensed](https://img.shields.io/badge/license-MIT-blue.svg)](./LICENSE.md) ![build badge](https://github.com/mailersend/mailersend-php/actions/workflows/php.yml/badge.svg) ![analysis badge](https://github.com/mailersend/mailersend-php/actions/workflows/static-analysis.yml/badge.svg)

# Table of Contents

* [Installation](#installation)
* [Usage](#usage)
  * [Sending and email](#sending_an_email)
  * [Sending and email with CC and BCC](#cc_and_bcc)
  * [Sending an email with variables (simple personalisation)](#variables)
  * [Sending an email with personalization (advanced personalisation)](#personalization)
  * [Sending a template-based email](#template)
  * [Sending an email with attachment](#attachments)
  * [Debugging validation errors](#debugging-validation-errors)
  * [Activity API](#activity)
  * [Analytics API](#analytics)
  * [Domains API](#domains)
  * [Messages API](#messages)
  * [Recipients API](#recipients)
  * [Tokens API](#tokens)
  * [Webhooks API](#webhooks)
  * [Templates API](#templates)
* [Testing](#testing)
* [Support and Feedback](#support-and-feedback)
* [License](#license)

<a name="installation"></a>

# Installation

## Requirements

- PHP 7.4
- PSR-7 and PSR-18 based HTTP adapter
- An API Key from [mailersend.com](https://www.mailersend.com)

## Setup

This library is built atop of [PSR-7](https://www.php-fig.org/psr/psr-7/) and
[PSR-18](https://www.php-fig.org/psr/psr-18/). You will need to install some implementations for those interfaces.

```bash
composer require php-http/guzzle7-adapter nyholm/psr7
```

After that you can install the SDK.

```bash
composer require mailersend/mailersend
```

<a name="usage"></a>

# Usage

<a name="sending_an_email"></a>
## Sending a basic email.

```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\Recipient;
use MailerSend\Helpers\Builder\EmailParams;

$mailersend = new MailerSend(['api_key' => 'key']);

$recipients = [
    new Recipient('your@client.com', 'Your Client'),
];

$emailParams = (new EmailParams())
    ->setFrom('your@domain.com')
    ->setFromName('Your Name')
    ->setRecipients($recipients)
    ->setSubject('Subject')
    ->setHtml('This is the HTML content')
    ->setText('This is the text content');

$mailersend->email->send($emailParams);
```

HTML content is not required. You still can send an email with Text only.

```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\Recipient;
use MailerSend\Helpers\Builder\EmailParams;

$mailersend = new MailerSend(['api_key' => 'key']);

$recipients = [
    new Recipient('your@client.com', 'Your Client'),
];

$emailParams = (new EmailParams())
    ->setFrom('your@domain.com')
    ->setFromName('Your Name')
    ->setRecipients($recipients)
    ->setSubject('Subject')
    ->setText('This is the text content');

$mailersend->email->send($emailParams);
```

<a name="cc_and_bcc"></a>
## Sending an email with CC and BCC

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
## Sending an email with variables (simple personalization)

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
## Sending an email with personalization (advanced personalization)

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

<a name="template"></a>
## Sending a template-based email

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
## Sending an email with attachment

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
## Debugging validation errors

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

<a name="activity"></a>
## Activity

**List activities**

```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\ActivityParams;

$mailersend = new MailerSend(['api_key' => 'key']);

$activityParams = (new ActivityParams())
                    ->setPage(3)
                    ->setLimit(15)
                    ->setDateFrom(1623073576)
                    ->setDateTo(1623074976)
                    ->setEvent(['processed', 'sent']);

$mailersend->activity->getAll('domainId', $activityParams);
```

<a name="analytics"></a>
## Analytics

**Activity data by date**

```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\ActivityAnalyticsParams;
use MailerSend\Common\Constants;

$mailersend = new MailerSend(['api_key' => 'key']);

$activityAnalyticsParams = (new ActivityAnalyticsParams(100, 101))
                    ->setDomainId('domain_id')
                    ->setGroupBy(Constants::GROUP_BY_DAYS)
                    ->setTags(['tag'])
                    ->setEvent(['processed', 'sent']);

$mailersend->analytics->activityDataByDate($activityAnalyticsParams);
```

**Opens by country**

```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\OpensAnalyticsParams;

$mailersend = new MailerSend(['api_key' => 'key']);

$opensAnalyticsParams = (new OpensAnalyticsParams(100, 101))
                    ->setDomainId('domain_id')
                    ->setTags(['tag']);

$mailersend->analytics->opensByCountry($opensAnalyticsParams);
```

**Opens by user-agent name**

```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\OpensAnalyticsParams;

$mailersend = new MailerSend(['api_key' => 'key']);

$opensAnalyticsParams = (new OpensAnalyticsParams(100, 101))
                    ->setDomainId('domain_id')
                    ->setTags(['tag']);

$mailersend->analytics->opensByUserAgentName($opensAnalyticsParams);
```

**Opens by reading environment**

```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\OpensAnalyticsParams;

$mailersend = new MailerSend(['api_key' => 'key']);

$opensAnalyticsParams = (new OpensAnalyticsParams(100, 101))
                    ->setDomainId('domain_id')
                    ->setTags(['tag']);

$mailersend->analytics->opensByReadingEnvironment($opensAnalyticsParams);
```

<a name="domains"></a>
## Domains

**Get all domains**

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->domain->getAll($page = 1, $limit = 10, $verified = true);
```

**Get a single domain**

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->domain->find('domain_id');
```

**Delete a domain**

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->domain->delete('domain_id');
```

**Get recipients for a domain**

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->domain->recipients($domainId = 'domain_id', $page = 1, $limit = 10);
```

**Update domain settings**

Here you can set as many properties as you need, one or multiple.

```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\DomainSettingsParams;

$mailersend = new MailerSend(['api_key' => 'key']);

$domainSettingsParam = (new DomainSettingsParams())
                            ->setSendPaused(true)
                            ->setTrackClicks(true)
                            ->setTrackOpens(false)
                            ->setTrackUnsubscribe(false)
                            ->setTrackContent(true)
                            ->setTrackUnsubscribeHtml('html')
                            ->setTrackUnsubscribePlain('plain')
                            ->setCustomTrackingEnabled(true)
                            ->setCustomTrackingSubdomain(false);

$mailersend->domain->domainSettings($domainId = 'domain_id', $domainSettingsParam);
```

**Verify a domain**

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->domain->verify('domain_id');
```

## Messages

**List messages**

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->messages->get($limit = 100, $page = 3);
```

**Find a specific message**

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->messages->find('message_id');
```

<a name="recipients"></a>

## Recipients

**List recipients**

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->recipients->get(null, $limit = 100, $page = 3);
```

**List recipients in a specific domain**

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->recipients->get('domain_id', $limit = 100, $page = 3);
```

**Find a specific recipient**

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->recipients->find('recipient_id');
```

**Delete a recipient**

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->recipients->delete('recipient_id');
```

**Add recipients to a suppression list**

**Blocklist**

```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\BlocklistParams;

$mailersend = new MailerSend(['api_key' => 'key']);

$params = (new BlocklistParams())
    ->setDomainId('domain_id')
    ->setRecipients(['recipient_one', 'recipient_two'])
    ->setPatterns(['pattern_one', 'pattern_two']);

$mailersend->blocklist->create($params);
```

**Hard Bounces**

```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\SuppressionParams;

$mailersend = new MailerSend(['api_key' => 'key']);

$params = (new SuppressionParams())
    ->setDomainId('domain_id')
    ->setRecipients(['recipient_one', 'recipient_two']);

$mailersend->hardBounce->create($params);
```

**Spam Complaints**

```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\SuppressionParams;

$mailersend = new MailerSend(['api_key' => 'key']);

$params = (new SuppressionParams())
    ->setDomainId('domain_id')
    ->setRecipients(['recipient_one', 'recipient_two']);

$mailersend->spamComplaint->create($params);
```

**Unsubscribes**

```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\SuppressionParams;

$mailersend = new MailerSend(['api_key' => 'key']);

$params = (new SuppressionParams())
    ->setDomainId('domain_id')
    ->setRecipients(['recipient_one', 'recipient_two']);

$mailersend->unsubscribe->create($params);
```

**Delete recipients from a suppression list**

**Blocklist**

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend(['api_key' => 'key']);

// Delete specific instances
$mailersend->blocklist->delete(['id_one', 'id_two']);

// or delete all
$mailersend->blocklist->delete(null, true);
```

**Hard Bounces**

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend(['api_key' => 'key']);

// Delete specific instances
$mailersend->hardBounce->delete(['id_one', 'id_two']);

// or delete all
$mailersend->hardBounce->delete(null, true);
```

**Spam Complaints**

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend(['api_key' => 'key']);

// Delete specific instances
$mailersend->spamComplaint->delete(['id_one', 'id_two']);

// or delete all
$mailersend->spamComplaint->delete(null, true);
```

**Unsubscribes**

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend(['api_key' => 'key']);

// Delete specific instances
$mailersend->unsubscribe->delete(['id_one', 'id_two']);

// or delete all
$mailersend->unsubscribe->delete(null, true);
```

**Get recipients from a suppression list**

**Blocklist**

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->blocklist->getAll('domain_id', 15);
```

**Hard Bounces**

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->hardBounce->getAll('domain_id', 15);
```

**Spam Complaints**

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->spamComplaint->getAll('domain_id', 15);
```

**Unsubscribes**

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->unsubscribe->getAll('domain_id', 15);
```

<a name="tokens"></a>

## Tokens

**Create a new token**

```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\TokenParams;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->token->create(
    new TokenParams('token name', 'domainId', TokenParams::ALL_SCOPES)
);
```

Because of security reasons, we only allow access token appearance once during creation. In order to see the access token created you can do:

```php
use MailerSend\Helpers\Builder\TokenParams;

$response = $mailersend->token->create(
    new TokenParams('token name', 'domainId', TokenParams::ALL_SCOPES)
);

echo $response['body']['data']['accessToken'];
```

**Pause / Unpause Token**

```php
use MailerSend\Helpers\Builder\TokenParams;

$mailersend->token->update('token_id', TokenParams::STATUS_PAUSE); // PAUSE
$mailersend->token->update('token_id', TokenParams::STATUS_UNPAUSE); // UNPAUSE
```

**Delete Token**

```php
use MailerSend\Helpers\Builder\TokenParams;

$mailersend->token->delete('token_id');
```

<a name="webhooks"></a>
## Webhooks

**List Webhooks**

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->webhooks->get('domain_id');
```

**Find a Webhook**

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->webhooks->find('webhook_id');
```

**Create a Webhook**

```php
use MailerSend\Helpers\Builder\WebhookParams;
use MailerSend\MailerSend;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->webhooks->create(
    new WebhookParams('https://webhook_url', 'Webhook name', WebhookParams::ALL_ACTIVITIES, 'domain_id')
);
```

**Create a disabled Webhook**

```php
use MailerSend\Helpers\Builder\WebhookParams;
use MailerSend\MailerSend;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->webhooks->create(
    new WebhookParams('https://webhook_url', 'Webhook name', WebhookParams::ALL_ACTIVITIES, 'domain_id', false)
);
```

**Update a Webhook**

```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\WebhookParams;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->webhooks->update('webhook_id', 'https://webhook_url', 'Webhook name', WebhookParams::ALL_ACTIVITIES);
```

**Disable/Enable a Webhook**

```php
use MailerSend\Helpers\Builder\WebhookParams;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->webhooks->update('webhook_id', 'https://webhook_url', 'Webhook name', WebhookParams::ALL_ACTIVITIES, true); //Enabled
$mailersend->webhooks->update('webhook_id', 'https://webhook_url', 'Webhook name', WebhookParams::ALL_ACTIVITIES, false); //Disabled
```

**Delete a Webhook**

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->webhooks->delete('webhook_id');
```

*If, at the moment, some endpoint is not available, please use `cURL` and other available tools to access it. [Refer to official API docs for more info](https://developers.mailersend.com/).*

<a name="templates"></a>
## Templates

**Get a list of templates**

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend(['api_key' => 'key']);

// Get all templates of an account
$mailersend->template->getAll();

// Get all templates of a domain
$mailersend->template->getAll('domain_id');

// Get page 2 of templates with 20 records per page
$mailersend->template->getAll('domain_id', 2, 20);
```

**Get a single template**

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->template->find('template_id');
```

**Delete a template**

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->template->delete('template_id');
```

<a name="testing"></a>
# Testing

``` bash
composer test
```

<a name="support-and-feedback"></a>
# Support and Feedback

In case you find any bugs, submit an issue directly here in GitHub.

You are welcome to create SDK for any other programming language.

If you have any troubles using our API or SDK free to contact our support by email [info@mailersend.com](mailto:info@mailersend.com)

The official documentation is at [https://developers.mailersend.com](https://developers.mailersend.com)

<a name="license"></a>
# License

[The MIT License (MIT)](LICENSE.md)

