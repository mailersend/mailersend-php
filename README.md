<a href="https://www.mailersend.com"><img src="https://www.mailersend.com/images/logo.svg" width="200px"/></a>

MailerSend PHP SDK

[![MIT licensed](https://img.shields.io/badge/license-MIT-blue.svg)](./LICENSE.md) ![build badge](https://github.com/mailersend/mailersend-php/actions/workflows/php.yml/badge.svg) ![analysis badge](https://github.com/mailersend/mailersend-php/actions/workflows/static-analysis.yml/badge.svg)

# Table of Contents

* [Installation](#installation)
* [Usage](#usage)
  * [Email API](#email-api)
    * [Send an email](#send-an-email)
    * [Add CC, BCC recipients](#cc-bcc-recipients)
    * [Send a template-based email](#template)
    * [Advanced personalization](#personalization)
    * [Simple personalization](#variables)
    * [Send an email with attachment](#attachments)
  * [Bulk emails API](#bulk-email-api)
    * [Send bulk email](#send-bulk-email)
    * [Get bulk email status](#get-bulk-email-status)
  * [Activity API](#activity)
    * [Get a list of activities](#get-a-list-of-activities)
  * [Analytics API](#analytics)
    * [Get activity data by date](#get-activity-data-by-date)
    * [Opens by country](#opens-by-country)
    * [Opens by user-agent](#opens-by-user-agent)
    * [Opens by reading environment](#opens-by-reading-environment)
  * [Domains API](#domains)
    * [Get a list of domains](#get-a-list-of-domains)
    * [Get domain](#get-domain)
    * [Delete domain](#delete-domain)
    * [Get a list of recipients per domain](#get-a-list-of-recipients-per-domain)
    * [Update domain settings](#update-domain-settings)
    * [Verify a domain](#verify-a-domain)
  * [Messages API](#messages)
    * [Get a list of messages](#get-a-list-of-messages)
    * [Get info on a message](#get-info-on-a-message)
  * [Tokens API](#tokens)
    * [Create a token](#create-a-token)
    * [Update token](#update-token)
    * [Delete token](#delete-token)
  * [Recipients API](#recipients)
    * [Get a list of recipients](#get-a-list-of-recipients)
    * [Get single recipient](#get-single-recipient)
    * [Delete recipient](#delete-recipient)
    * [Add recipients to a suppression list](#add-recipients-to-a-suppression-list)
    * [Delete recipients from a suppression list](#delete-recipients-from-a-suppression-list)
    * [Get recipients from a suppression list](#get-recipients-from-a-suppression-list)
  * [Webhooks API](#webhooks)
    * [Get a list of webhooks](#get-a-list-of-webhooks)
    * [Get webhook](#get-webhook)
    * [Create webhook](#create-webhook)
    * [Update webhook](#update-webhook)
    * [Delete webhook](#delete-webhook)
  * [Templates API](#templates)
    * [Get a list of templates](#get-a-list-of-templates)
    * [Get a single template](#get-a-single-template)
    * [Delete a template](#delete-a-template)
* [Debugging validation errors](#debugging-validation-errors)
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

<a name="email-api"></a>

## Email

<a name="send-an-email"></a>

### Send an email

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

<a name="cc-bcc-recipients"></a>

### Add CC, BCC recipients

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

<a name="template"></a>

### Send a template-based email

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
    ->setVariables($variables)
    ->setTags($tags);

$mailersend->email->send($emailParams);
```

<a name="personalization"></a>

### Advanced personalization

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

<a name="variables"></a>

### Simple personalization

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

<a name="attachments"></a>

### Send email with attachment

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

<a name="bulk-email-api"></a>
## Bulk email API

<a name="send-bulk-email"></a>
###Send bulk email

```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\Recipient;
use MailerSend\Helpers\Builder\EmailParams;

$mailersend = new MailerSend(['api_key' => 'key']);

$recipients = [
    new Recipient('your@client.com', 'Your Client'),
];

$bulkEmailParams = [];

$bulkEmailParams[] = (new EmailParams())
    ->setFrom('your@domain.com')
    ->setFromName('Your Name')
    ->setRecipients($recipients)
    ->setSubject('Subject')
    ->setHtml('This is the HTML content')
    ->setText('This is the text content');

$bulkEmailParams[] = (new EmailParams())
    ->setFrom('your@domain.com')
    ->setFromName('Your Name')
    ->setRecipients($recipients)
    ->setSubject('Subject')
    ->setHtml('This is the HTML content')
    ->setText('This is the text content');

$mailersend->bulkEmail->send($bulkEmailParams);
```

<a name="get-bulk-email-status"></a>
###Get bulk email status

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->bulkEmail->getStatus('bulk_email_id');
```

<a name="activity"></a>

## Activity

<a name="get-a-list-of-activities"></a>

### Get a list of activities

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

<a name="activity-data-by-date"></a>

### Get activity data by date

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

<a name="opens-by-country"></a>

### Opens by country

```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\OpensAnalyticsParams;

$mailersend = new MailerSend(['api_key' => 'key']);

$opensAnalyticsParams = (new OpensAnalyticsParams(100, 101))
                    ->setDomainId('domain_id')
                    ->setTags(['tag']);

$mailersend->analytics->opensByCountry($opensAnalyticsParams);
```

<a name="opens-by-user-agent"></a>

### Opens by user-agent

```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\OpensAnalyticsParams;

$mailersend = new MailerSend(['api_key' => 'key']);

$opensAnalyticsParams = (new OpensAnalyticsParams(100, 101))
                    ->setDomainId('domain_id')
                    ->setTags(['tag']);

$mailersend->analytics->opensByUserAgentName($opensAnalyticsParams);
```

<a name="opens-by-reading-environment"></a>

### Opens by reading environment

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

<a name="get-a-list-of-domains"></a>

### Get a list of domains

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->domain->getAll($page = 1, $limit = 10, $verified = true);
```

<a name="get-domain"></a>

### Get domain

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->domain->find('domain_id');
```

<a name="delete-domain"></a>
### Delete domain

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->domain->delete('domain_id');
```

<a name="get-a-list-of-recipients-per-domain"></a>

## Get a list of recipients per domain

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->domain->recipients($domainId = 'domain_id', $page = 1, $limit = 10);
```

<a name="update-domain-settings"></a>

### Update domain settings

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

<a name="verify-a-domain"></a>

### Verify a domain

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->domain->verify('domain_id');
```

<a name="messages"></a>

## Messages

<a name="get-a-list-of-messages"></a>

### Get a list of messages

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->messages->get($limit = 100, $page = 3);
```

<a name="get-info-on-a-message"></a>

### Get info on a message

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->messages->find('message_id');
```

<a name="tokens"></a>

## Tokens

<a name="create_a_token"></a>

### Create a token

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
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\TokenParams;

$mailersend = new MailerSend(['api_key' => 'key']);

$response = $mailersend->token->create(
    new TokenParams('token name', 'domainId', TokenParams::ALL_SCOPES)
);

echo $response['body']['data']['accessToken'];
```

<a name="update-token"></a>

### Update token

```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\TokenParams;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->token->update('token_id', TokenParams::STATUS_PAUSE); // PAUSE
$mailersend->token->update('token_id', TokenParams::STATUS_UNPAUSE); // UNPAUSE
```

<a name="delete-token"></a>

### Delete Token

```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\TokenParams;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->token->delete('token_id');
```

<a name="recipients"></a>

## Recipients

<a name="get-a-list-of-recipients"></a>

### Get a list of recipients

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->recipients->get(null, $limit = 100, $page = 3);
// Or for a specific domain
$mailersend->recipients->get('domain_id', $limit = 100, $page = 3);
```

<a name="get-single-recipient"></a>

### Get single recipient

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->recipients->find('recipient_id');
```

<a name="delete-recipient"></a>

### Delete recipient

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->recipients->delete('recipient_id');
```

<a name="add-recipients-to-a-suppression-list"></a>

### Add recipients to a suppression list

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

<a name="delete-recipients-from-a-suppression-list"></a>

### Delete recipients from a suppression list

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

<a name="get-recipients-from-a-suppression-list"></a>

### Get recipients from a suppression list

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

<a name="webhooks"></a>

## Webhooks

<a name="get-a-list-of-webhooks"></a>

### Get a list of webhooks

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->webhooks->get('domain_id');
```

<a name="get-webhook"></a>

### Get webhook

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->webhooks->find('webhook_id');
```

<a name="create-webhook"></a>

### Create webhook

```php
use MailerSend\Helpers\Builder\WebhookParams;
use MailerSend\MailerSend;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->webhooks->create(
    new WebhookParams('https://webhook_url', 'Webhook name', WebhookParams::ALL_ACTIVITIES, 'domain_id')
);

// Or a disabled webhook

$mailersend->webhooks->create(
    new WebhookParams('https://webhook_url', 'Webhook name', WebhookParams::ALL_ACTIVITIES, 'domain_id', false)
);
```

<a name="update-webhook"></a>

### Update webhook

```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\WebhookParams;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->webhooks->update('webhook_id', 'https://webhook_url', 'Webhook name', WebhookParams::ALL_ACTIVITIES);

// Enable webhook
$mailersend->webhooks->update('webhook_id', 'https://webhook_url', 'Webhook name', WebhookParams::ALL_ACTIVITIES, true);

// Disable webhook
$mailersend->webhooks->update('webhook_id', 'https://webhook_url', 'Webhook name', WebhookParams::ALL_ACTIVITIES, false);
```

<a name="delete-webhook"></a>

### Delete webhook

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->webhooks->delete('webhook_id');
```

*If, at the moment, some endpoint is not available, please use `cURL` and other available tools to access it. [Refer to official API docs for more info](https://developers.mailersend.com/).*

<a name="templates"></a>

## Templates

<a name="get-a-list-of-templates"></a>

### Get a list of templates

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

<a name="Get-a-single-template"></a>

### Get a single template

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->template->find('template_id');
```

<a name="delete-a-template"></a>

### Delete a template

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->template->delete('template_id');
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

