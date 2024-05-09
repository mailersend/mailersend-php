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
        * [Send a scheduled message](#send-a-scheduled-message)
        * [Send email with precedence bulk header](#precedence-bulk-header)
        * [Send email with custom headers](#custom-headers)
    * [Bulk emails API](#bulk-email-api)
        * [Send bulk email](#send-bulk-email)
        * [Get bulk email status](#get-bulk-email-status)
    * [Inbound routing](#inbound-routing)
        * [Get a list of inbound routes](#get-a-list-of-inbound-routes)
        * [Get a single inbound route](#get-a-single-inbound-route)
        * [Add an inbound route](#add-an-inbound-route)
        * [Update an inbound route](#update-an-inbound-route)
        * [Delete an inbound route](#delete-an-inbound-route)
    * [Activity API](#activity)
        * [Get a list of activities](#get-a-list-of-activities)
        * [Get a single activity](#get-a-single-activity)
    * [Analytics API](#analytics)
        * [Get activity data by date](#get-activity-data-by-date)
        * [Opens by country](#opens-by-country)
        * [Opens by user-agent](#opens-by-user-agent)
        * [Opens by reading environment](#opens-by-reading-environment)
    * [Domains API](#domains)
        * [Get a list of domains](#get-a-list-of-domains)
        * [Get domain](#get-domain)
        * [Add a domain](#add-a-domain)
        * [Delete domain](#delete-domain)
        * [Get a list of recipients per domain](#get-a-list-of-recipients-per-domain)
        * [Update domain settings](#update-domain-settings)
        * [Verify a domain](#verify-a-domain)
        * [Get DNS records](#get-dns-records)
    * [Messages API](#messages)
        * [Get a list of messages](#get-a-list-of-messages)
        * [Get info on a message](#get-info-on-a-message)
    * [Scheduled messages API](#scheduled-messages)
        * [Get a list of scheduled messages](#get-a-list-of-scheduled-messages)
        * [Get a single scheduled message](#get-a-single-scheduled-message)
        * [Delete a scheduled message](#delete-a-scheduled-message)
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
    * [Email Verification API](#email-verification)
        * [Get all email verification lists](#get-all-email-verification-lists)
        * [Get an email verification list](#get-an-email-verification-list)
        * [Create an email verification list](#create-an-email–verification-list)
        * [Verify an email list](#verify-an-email-list)
        * [Get email verification list results](#get-email-verification-list-results)
    * [SMS API](#sms-api)
        * [Send an sms](#send-sms)
        * [Personalization](#sms-personalization)
    * [SMS phone number API](#sms-numbers-api)
        * [Get a list of sms phone numbers](#get-a-list-of-sms-numbers)
        * [Get an SMS phone number](#get-sms-number)
        * [Update a single SMS phone number](#update-sms-number)
        * [Delete an SMS phone number](#delete-sms-number)
    * [SMS messages API](#sms-messages-api)
        * [Get a list of SMS messages](#get-a-list-of-sms-messages)
        * [Get an SMS message](#get-sms-message)
    * [SMS Activity API](#sms-activity-api)
        * [Get a list of SMS activities](#get-a-list-of-sms-activities)
    * [SMS Recipients API](#sms-recipients-api)
        * [Get a list of SMS recipients](#get-a-list-of-sms-recipients)
        * [Get an SMS recipient](#get-sms-recipient)
        * [Update a single SMS recipient](#update-sms-recipient)
    * [SMS webhooks API](#sms-webhooks-api)
        * [Get a list of SMS webhooks](#get-a-list-of-sms-webhooks)
        * [Get a single SMS webhook](#get-sms-webhook)
        * [Create an SMS webhook](#create-sms-webhook)
        * [Update a single SMS webhook](#update-sms-webhook)
    * [SMS inbound routing API](#sms-inbounds-api)
        * [Get a list of SMS inbound routes](#get-a-list-of-sms-inbounds)
        * [Get a single SMS inbound route](#get-sms-inbound)
        * [Add an SMS inbound route](#create-sms-inbound)
        * [Update an inbound route](#update-sms-inbound)
        * [Delete an SMS inbound route](#delete-sms-inbound)
    * [Sender Identities](#sender-identity-routing)
        * [Get a list of Sender Identities](#get-a-list-of-sender-identity-routes)
        * [Get a single Sender Identity](#get-a-single-sender-identity-route)
        * [Get a single Sender Identity by email](#get-a-single-sender-identity-by-email-route)
        * [Add a Sender_Identity](#add-a-sender-identity-route)
        * [Update a Sender Identity](#update-a-sender-identity-route)
        * [Update a Sender Identity by email](#update-a-sender-identity-by-email-route)
        * [Delete a Sender Identity](#delete-a-sender-identity-route)
        * [Delete a Sender Identity by email](#delete-a-sender-identity-by-email-route)
    * [SMTP Users](#smtp-users-routing)
        * [Get a list of SMTP Users](#get-a-list-of-smtp-users)
        * [Get a single SMTP User](#get-a-single-smtp-user)
        * [Add SMTP User](#add-smtp-user)
        * [Update SMTP User](#update-smtp-user)
        * [Delete SMTP User](#delete-smtp-user)
    * [Users](#users-routing)
        * [Get a list of Users](#get-a-list-of-users)
        * [Get a single User](#get-a-single-user)
        * [Add a User](#add-a-user)
        * [Update a User](#update-a-user)
        * [Delete a User](#delete-a-user)
        * [Get a list of Invites](#get-a-list-of-invites)
        * [Get a single Invite](#get-a-single-invite)
        * [Resend an Invite](#resend-an-invite)
        * [Cancel an Invite](#cancel-an-invite)
    * [Other endpoints](#other-endpoints)
        * [Get API quota](#get-api-quota)
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

This library, after version v0.22.0 is not compatible with Laravel 8.0 or lower. Please use older versions of SDK, or update your Laravel version.

This library is built atop of [PSR-7](https://www.php-fig.org/psr/psr-7/) and
[PSR-18](https://www.php-fig.org/psr/psr-18/). You will need to install some implementations for those interfaces.

```bash
composer require php-http/guzzle7-adapter nyholm/psr7
```

After that you can install the SDK.

```bash
composer require mailersend/mailersend
```

Finally, add an environment variable called `MAILERSEND_API_KEY` with the appropriate API key.

Optionally, although not recommended, you can manually add the API key when instantiating the `MailerSend` class, like so:

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend(['api_key' => 'your_api_key']);
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

$mailersend = new MailerSend();

$recipients = [
    new Recipient('your@client.com', 'Your Client'),
];

$emailParams = (new EmailParams())
    ->setFrom('your@domain.com')
    ->setFromName('Your Name')
    ->setRecipients($recipients)
    ->setSubject('Subject')
    ->setHtml('This is the HTML content')
    ->setText('This is the text content')
    ->setReplyTo('reply to')
    ->setReplyToName('reply to name');

$mailersend->email->send($emailParams);
```

HTML content is not required. You still can send an email with Text only.

```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\Recipient;
use MailerSend\Helpers\Builder\EmailParams;

$mailersend = new MailerSend();

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

$mailersend = new MailerSend();

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

$mailersend = new MailerSend();

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

$mailersend = new MailerSend();

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

$mailersend = new MailerSend();

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

$mailersend = new MailerSend();

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

<a name="send-a-scheduled-message"></a>
### Send a scheduled message

```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\Recipient;
use MailerSend\Helpers\Builder\EmailParams;

$mailersend = new MailerSend();

$recipients = [
    new Recipient('your@client.com', 'Your Client'),
];

$emailParams = (new EmailParams())
    ->setFrom('your@domain.com')
    ->setFromName('Your Name')
    ->setRecipients($recipients)
    ->setSubject('Subject')
    ->setHtml('This is the html version.')
    ->setText('This is the text version.')
    ->setSendAt(1665626400);
    ->setPrecedenceBulkHeader(true);

$mailersend->email->send($emailParams);
```

<a name="precedence-bulk-header"></a>
### Send email with precedence bulk header

```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\Recipient;
use MailerSend\Helpers\Builder\EmailParams;

$mailersend = new MailerSend();

$recipients = [
    new Recipient('your@client.com', 'Your Client'),
];

$emailParams = (new EmailParams())
    ->setFrom('your@domain.com')
    ->setFromName('Your Name')
    ->setRecipients($recipients)
    ->setSubject('Subject')
    ->setHtml('This is the html version.')
    ->setText('This is the text version.')
    ->setPrecedenceBulkHeader(true);

$mailersend->email->send($emailParams);
```

### Send an email with tracking

```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\Recipient;
use MailerSend\Helpers\Builder\EmailParams;

$mailersend = new MailerSend();

$recipients = [
    new Recipient('your@client.com', 'Your Client'),
];

$emailParams = (new EmailParams())
    ->setFrom('your@domain.com')
    ->setFromName('Your Name')
    ->setRecipients($recipients)
    ->setSubject('Subject')
    ->setHtml('This is the HTML content')
    ->setText('This is the text content')
    ->setTrackClicks(true)
    ->setTrackOpens(true)
    ->setTrackContent(true);

$mailersend->email->send($emailParams);
```

<a name="custom-headers"></a>
### Send an email with custom headers

```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\Recipient;
use MailerSend\Helpers\Builder\EmailParams;
use MailerSend\Helpers\Builder\Header;

$mailersend = new MailerSend(['api_key' => 'key']);

$recipients = [
    new Recipient('your@client.com', 'Your Client'),
];

$headers = [
    new Header('Custom-Header-1', 'Value 1')
    new Header('Custom-Header-2', 'Value 2')
];

$emailParams = (new EmailParams())
    ->setFrom('your@domain.com')
    ->setFromName('Your Name')
    ->setRecipients($recipients)
    ->setSubject('Subject')
    ->setHtml('This is the HTML content')
    ->setText('This is the text content')
    ->setHeaders($headers);

$mailersend->email->send($emailParams);
```

<a name="bulk-email-api"></a>
## Bulk email API

<a name="send-bulk-email"></a>
### Send bulk email

```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\Recipient;
use MailerSend\Helpers\Builder\EmailParams;

$mailersend = new MailerSend();

$bulkEmailParams = [];

$bulkEmailParams[] = (new EmailParams())
    ->setFrom('your@domain.com')
    ->setFromName('Your Name')
    ->setRecipients([
        new Recipient('recipient1@client.com', 'Your Client'),
    ])
    ->setSubject('Subject')
    ->setHtml('This is the HTML content')
    ->setText('This is the text content');

$bulkEmailParams[] = (new EmailParams())
    ->setFrom('your@domain.com')
    ->setFromName('Your Name')
    ->setRecipients([
        new Recipient('recipient2@client.com', 'Your Client'),
    ])
    ->setSubject('Subject')
    ->setHtml('This is the HTML content')
    ->setText('This is the text content');

$mailersend->bulkEmail->send($bulkEmailParams);
```

<a name="get-bulk-email-status"></a>
### Get bulk email status

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend();

$mailersend->bulkEmail->getStatus('bulk_email_id');
```

<a name="inbound-routing"></a>

## Inbound routing

<a name="get-a-list-of-inbound-routes"></a>

### Get a list of inbound routes

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend();

$mailersend->inbound->getAll($domainId = 'domainId', $page = 1, $limit = 10);
```

<a name="get-a-single-inbound-route"></a>

### Get a single inbound route

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend();

$mailersend->inbound->find('inboundId');
```

<a name="add-an-inbound-route"></a>

### Add an inbound route

Example using only classes:

```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\Inbound;
use \MailerSend\Helpers\Builder\CatchFilter;
use \MailerSend\Helpers\Builder\MatchFilter;
use \MailerSend\Helpers\Builder\Forward;
use \MailerSend\Helpers\Builder\Filter;
use \MailerSend\Common\Constants;

$mailersend = new MailerSend();

$mailersend->inbound->create(
    (new Inbound('domainId', 'name', true))
        ->setInboundDomain('inboundDomain')
        ->setCatchFilter(
            (new CatchFilter(Constants::TYPE_CATCH_RECIPIENT)
                ->addFilter(new Filter(Constants::COMPARER_EQUAL, 'test@mailersend.com'))))
        ->setMatchFilter(
            (new MatchFilter(Constants::TYPE_MATCH_SENDER))
                ->addFilter(new Filter(Constants::COMPARER_EQUAL, 'sender@mailersend.com', 'sender')))
        ->addForward(new Forward(Constants::COMPARER_EQUAL, 'value'))
);
```

Example using both classes and arrays:

```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\Inbound;
use \MailerSend\Helpers\Builder\CatchFilter;
use \MailerSend\Helpers\Builder\MatchFilter;
use \MailerSend\Helpers\Builder\Forward;
use \MailerSend\Common\Constants;

$mailersend = new MailerSend();

$mailersend->inbound->create(
    (new Inbound('domainId', 'name', true))
        ->setInboundDomain('inboundDomain')
        ->setCatchFilter(
            (new CatchFilter(Constants::TYPE_CATCH_RECIPIENT))
                ->setFilters([
                    [
                        'comparer' => Constants::COMPARER_EQUAL,
                        'value' => 'test@mailersend.com',
                    ]
                ])
        )
        ->setMatchFilter(
            (new MatchFilter(Constants::TYPE_MATCH_SENDER))
                ->setFilters([
                    [
                        'comparer' => Constants::COMPARER_EQUAL,
                        'value' => 'sender@mailersend.com',
                        'key' => 'sender',
                    ]
                ])
        )
        ->addForward(new Forward(Constants::COMPARER_EQUAL, 'value'))
);
```

Example using only arrays:

```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\Inbound;
use \MailerSend\Helpers\Builder\CatchFilter;
use \MailerSend\Helpers\Builder\MatchFilter;
use \MailerSend\Helpers\Builder\Forward;
use \MailerSend\Common\Constants;

$mailersend = new MailerSend();

$mailersend->inbound->create(
    (new Inbound('domainId', 'name', true))
        ->setInboundDomain('inboundDomain')
        ->setCatchFilter([
            'type' => Constants::TYPE_CATCH_RECIPIENT,
            'filters' => [
                [
                    'comparer' => Constants::COMPARER_EQUAL,
                    'value' => 'test@mailersend.com',
                ],
            ],
        ])
        ->setMatchFilter([
            'type' => Constants::TYPE_MATCH_SENDER,
            'filters' => [
                [
                    'comparer' => Constants::COMPARER_EQUAL,
                    'value' => 'sender@mailersend.com',
                    'key' => 'sender',
                ],
            ],
        ])
        ->setForwards([
            [
                'type' => Constants::COMPARER_EQUAL,
                'value' => 'value',
            ]
        ])
);
```

<a name="update-an-inbound-route"></a>

### Update an inbound route

The examples on building the `Inbound` object portrayed in the 'Add an inbound route' also apply in here.

```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\Inbound;
use \MailerSend\Helpers\Builder\CatchFilter;
use \MailerSend\Helpers\Builder\MatchFilter;
use \MailerSend\Helpers\Builder\Forward;
use \MailerSend\Common\Constants;

$mailersend = new MailerSend();

$mailersend->inbound->update(
    'inboundId',
    (new Inbound('domainId', 'name', true))
        ->setInboundDomain('inboundDomain')
        ->setCatchFilter(
            (new CatchFilter(Constants::TYPE_CATCH_ALL))
        )
        ->setMatchFilter(new MatchFilter(Constants::TYPE_MATCH_ALL))
        ->addForward(new Forward(Constants::COMPARER_EQUAL, 'value'))
);
```

<a name="delete-an-inbound-route"></a>

### Delete an inbound route

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend();

$mailersend->inbound->delete('inboundId');
```

<a name="activity"></a>

## Activity

<a name="get-a-list-of-activities"></a>

### Get a list of activities

```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\ActivityParams;

$mailersend = new MailerSend();

$activityParams = (new ActivityParams())
                    ->setPage(3)
                    ->setLimit(15)
                    ->setDateFrom(1623073576)
                    ->setDateTo(1623074976)
                    ->setEvent(['queued', 'sent']);

$mailersend->activity->getAll('domainId', $activityParams);
```

<a name="#get-a-single-activity"></a>

### Get a single activity

```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\ActivityParams;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->activity->find('activity_id');
```

<a name="analytics"></a>

## Analytics

<a name="activity-data-by-date"></a>

### Get activity data by date

```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\ActivityAnalyticsParams;
use MailerSend\Common\Constants;

$mailersend = new MailerSend();

$activityAnalyticsParams = (new ActivityAnalyticsParams(100, 101))
                    ->setDomainId('domain_id')
                    ->setGroupBy(Constants::GROUP_BY_DAYS)
                    ->setTags(['tag'])
                    ->setEvent(['queued', 'sent']);

$mailersend->analytics->activityDataByDate($activityAnalyticsParams);
```

<a name="opens-by-country"></a>

### Opens by country

```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\OpensAnalyticsParams;

$mailersend = new MailerSend();

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

$mailersend = new MailerSend();

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

$mailersend = new MailerSend();

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

$mailersend = new MailerSend();

$mailersend->domain->getAll($page = 1, $limit = 10, $verified = true);
```

<a name="get-domain"></a>

### Get domain

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend();

$mailersend->domain->find('domain_id');
```

<a name="add-a-domain"></a>

### Add a domain

```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\DomainParams;

$mailersend = new MailerSend();

$domainParams = (new DomainParams('domainName'))
                    ->setReturnPathSubdomain('returnPath')
                    ->setCustomTrackingSubdomain('customTracking')
                    ->getInboundRoutingSubdomain('inboundRouting');

$mailersend->domain->create($domainParams);

```

<a name="delete-domain"></a>
### Delete domain

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend();

$mailersend->domain->delete('domain_id');
```

<a name="get-a-list-of-recipients-per-domain"></a>

### Get a list of recipients per domain

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend();

$mailersend->domain->recipients($domainId = 'domain_id', $page = 1, $limit = 10);
```

<a name="update-domain-settings"></a>

### Update domain settings

Here you can set as many properties as you need, one or multiple.

```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\DomainSettingsParams;

$mailersend = new MailerSend();

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

$mailersend = new MailerSend();

$mailersend->domain->verify('domain_id');
```

<a name="get-dns-records"></a>

### Get DNS records

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend();

$mailersend->domain->getDnsRecords('domain_id');
```

<a name="messages"></a>

## Messages

<a name="get-a-list-of-messages"></a>

### Get a list of messages

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend();

$mailersend->messages->get($limit = 100, $page = 3);
```

<a name="get-info-on-a-message"></a>

### Get info on a message

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend();

$mailersend->messages->find('message_id');
```

<a name="scheduled-messages"></a>

## Scheduled Messages

<a name="get-a-list-of-scheduled-messages"></a>

### Get a list of scheduled messages

```php
use MailerSend\MailerSend;
use \MailerSend\Common\Constants;

$mailersend = new MailerSend();

$mailersend->scheduleMessages->getAll(
    'domain_id',
    Constants::STATUS_SCHEDULED,
    $limit = 100,
    $page = 3
)
```

<a name="get-a-single-scheduled-message"></a>

### Get a single scheduled message

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend();

$mailersend->scheduleMessages->find('message_id');
```

<a name="delete-a-scheduled-message"></a>

### Delete a scheduled message

```php
use MailerSend\MailerSend;
use \MailerSend\Common\Constants;

$mailersend = new MailerSend();

$mailersend->scheduleMessages->delete('message_id');
```

<a name="tokens"></a>

## Tokens

<a name="create_a_token"></a>

### Create a token

```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\TokenParams;

$mailersend = new MailerSend();

$mailersend->token->create(
    new TokenParams('token name', 'domainId', TokenParams::ALL_SCOPES)
);
```

Because of security reasons, we only allow access token appearance once during creation. In order to see the access token created you can do:

```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\TokenParams;

$mailersend = new MailerSend();

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

$mailersend = new MailerSend();

$mailersend->token->update('token_id', TokenParams::STATUS_PAUSE); // PAUSE
$mailersend->token->update('token_id', TokenParams::STATUS_UNPAUSE); // UNPAUSE
```

<a name="delete-token"></a>

### Delete Token

```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\TokenParams;

$mailersend = new MailerSend();

$mailersend->token->delete('token_id');
```

<a name="recipients"></a>

## Recipients

<a name="get-a-list-of-recipients"></a>

### Get a list of recipients

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend();

$mailersend->recipients->get(null, $limit = 100, $page = 3);
// Or for a specific domain
$mailersend->recipients->get('domain_id', $limit = 100, $page = 3);
```

<a name="get-single-recipient"></a>

### Get single recipient

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend();

$mailersend->recipients->find('recipient_id');
```

<a name="delete-recipient"></a>

### Delete recipient

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend();

$mailersend->recipients->delete('recipient_id');
```

<a name="add-recipients-to-a-suppression-list"></a>

### Add recipients to a suppression list

**Blocklist**

```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\BlocklistParams;

$mailersend = new MailerSend();

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

$mailersend = new MailerSend();

$params = (new SuppressionParams())
    ->setDomainId('domain_id')
    ->setRecipients(['recipient_one', 'recipient_two']);

$mailersend->hardBounce->create($params);
```

**Spam Complaints**

```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\SuppressionParams;

$mailersend = new MailerSend();

$params = (new SuppressionParams())
    ->setDomainId('domain_id')
    ->setRecipients(['recipient_one', 'recipient_two']);

$mailersend->spamComplaint->create($params);
```

**Unsubscribes**

```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\SuppressionParams;

$mailersend = new MailerSend();

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

$mailersend = new MailerSend();

// Delete specific instances
$mailersend->blocklist->delete(['id_one', 'id_two']);

// or delete all
$mailersend->blocklist->delete(null, true);

// You can also specify the domain
$mailersend->blocklist->delete(['id'], false, 'domain_id');
```

**Hard Bounces**

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend();

// Delete specific instances
$mailersend->hardBounce->delete(['id_one', 'id_two']);

// or delete all
$mailersend->hardBounce->delete(null, true);

// You can also specify the domain
$mailersend->hardBounce->delete(['id'], false, 'domain_id');
```

**Spam Complaints**

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend();

// Delete specific instances
$mailersend->spamComplaint->delete(['id_one', 'id_two']);

// or delete all
$mailersend->spamComplaint->delete(null, true);

// You can also specify the domain
$mailersend->spamComplaint->delete(['id'], false, 'domain_id');
```

**Unsubscribes**

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend();

// Delete specific instances
$mailersend->unsubscribe->delete(['id_one', 'id_two']);

// or delete all
$mailersend->unsubscribe->delete(null, true);

// You can also specify the domain
$mailersend->unsubscribe->delete(['id'], false, 'domain_id');
```

**On Hold List**

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend(['api_key' => 'key']);

// Delete specific instances
$mailersend->onHoldList->delete(['id_one', 'id_two']);

// or delete all
$mailersend->onHoldList->delete(null, true);
```

<a name="get-recipients-from-a-suppression-list"></a>

### Get recipients from a suppression list

**Blocklist**

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend();

$mailersend->blocklist->getAll('domain_id', 15);
```

**Hard Bounces**

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend();

$mailersend->hardBounce->getAll('domain_id', 15);
```

**Spam Complaints**

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend();

$mailersend->spamComplaint->getAll('domain_id', 15);
```

**Unsubscribes**

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend();

$mailersend->unsubscribe->getAll('domain_id', 15);
```

**On Hold List**

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->onHoldList->getAll('domain_id', 15);
```

<a name="webhooks"></a>

## Webhooks

<a name="get-a-list-of-webhooks"></a>

### Get a list of webhooks

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend();

$mailersend->webhooks->get('domain_id');
```

<a name="get-webhook"></a>

### Get webhook

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend();

$mailersend->webhooks->find('webhook_id');
```

<a name="create-webhook"></a>

### Create webhook

```php
use MailerSend\Helpers\Builder\WebhookParams;
use MailerSend\MailerSend;

$mailersend = new MailerSend();

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

$mailersend = new MailerSend();

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

$mailersend = new MailerSend();

$mailersend->webhooks->delete('webhook_id');
```

*If, at the moment, some endpoint is not available, please use `cURL` and other available tools to access it. [Refer to official API docs for more info](https://developers.mailersend.com/).*

<a name="templates"></a>

## Templates

<a name="get-a-list-of-templates"></a>

### Get a list of templates

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend();

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

$mailersend = new MailerSend();

$mailersend->template->find('template_id');
```

<a name="delete-a-template"></a>

### Delete a template

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend();

$mailersend->template->delete('template_id');
```

<a name="email-verification"></a>

## Email Verification

<a name="get-all-email-verification-lists"></a>

### Get all email verification lists

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend();

$mailersend->emailVerification->getAll($page = 1, $limit = 10);
```

<a name="get-an-email-verification-list"></a>

### Get an email verification list

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend();

$mailersend->emailVerification->find('email_verification_id');
```

<a name="create-an-email–verification-list"></a>

### Create an email verification list

```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\EmailVerificationParams;

$mailersend = new MailerSend();

$emailVerificationParams = (new EmailVerificationParams('file.csv'))
    ->setEmailAddresses(['test@mail.com']);

$mailersend->emailVerification->create($emailVerificationParams);
```

<a name="verify-an-email-list"></a>

### Verify an email list

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend();

$mailersend->emailVerification->verify('email_verification_id');
```

<a name="get-email-verification-list-results"></a>

### Get email verification list results

```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\EmailVerificationParams;

$mailersend = new MailerSend();

$mailersend->emailVerification->getResults(
        $emailVerificationId = 'email_verification_id',
        $page = 1,
        $limit = 10,
        $results = [
            EmailVerificationParams::TYPO,
            EmailVerificationParams::CATCH_ALL,
        ],
    );
```

<a name="sms-api"></a>

## SMS

<a name="send-sms"></a>

### Send SMS

```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\SmsParams;

$mailersend = new MailerSend();

$smsParams = (new SmsParams())
    ->setFrom('+12065550101')
    ->setTo(['+12065550102'])
    ->addRecipient('+12065550103')
    ->setText('Text');
    
$sms = $mailersend->sms->send($smsParams);
```

<a name="sms-personalization"></a>

### Personalization

```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\SmsParams;

$mailersend = new MailerSend();

$smsParams = (new SmsParams())
    ->setFrom('+12065550101')
    ->setTo(['+12065550102'])
    ->setText('Text {{ var }}')
    ->setPersonalization([
        new SmsPersonalization('+12065550102', [
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
    ]);
    
$sms = $mailersend->sms->send($smsParams);
```

<a name="sms-numbers-api"></a>

## SMS phone numbers

<a name="get-a-list-of-sms-numbers"></a>

### Get a list of SMS phone numbers

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend();

$sms = $mailersend->smsNumber->getAll($page = 1, $limit = 10, $paused = true);
```

<a name="get-sms-number"></a>

### Get an SMS phone number

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend();

$sms = $mailersend->smsNumber->find('sms_number_id');
```

<a name="update-sms-number"></a>

### Update a single SMS phone number

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend();

$sms = $mailersend->smsNumber->update('sms_number_id', $paused = true);
```

<a name="delete-sms-number"></a>

### Delete an SMS phone number

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend();

$sms = $mailersend->smsNumber->delete('sms_number_id');
```

<a name="sms-messages-api"></a>

## SMS messages API

<a name="get-a-list-of-sms-messages"></a>

### Get a list of SMS messages

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend();

$smsMessages = $mailersend->smsMessage->getAll($page = 1, $limit = 10);
```

<a name="get-sms-message"></a>

### Get an SMS message

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend();

$smsMessage = $mailersend->smsMessage->find('sms_message_id');
```

<a name="sms-activity-api"></a>

## SMS activity API

<a name="get-a-list-of-sms-activities"></a>

### Get a list of SMS activities

```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\SmsActivityParams;

$mailersend = new MailerSend();

$smsActivityParams = (new SmsActivityParams())
    ->setSmsNumberId('sms_number_id')
    ->setDateFrom(1623073576)
    ->setDateTo(1623074976)
    ->setStatus(['queued'])
    ->setPage(3)
    ->setLimit(15);

$smsActivity = $mailersend->smsActivity->getAll($smsActivityParams);
```

<a name="sms-recipients-api"></a>

## SMS recipients API

<a name="get-a-list-of-sms-recipients"></a>

### Get a list of SMS recipients

```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\SmsRecipientParams;

$mailersend = new MailerSend();

$smsRecipientParams = (new SmsRecipientParams())
    ->setSmsNumberId('sms_number_id')
    ->setStatus('opt_out')
    ->setPage(3)
    ->setLimit(15);

$smsRecipients = $mailersend->smsRecipient->getAll($smsRecipientParams);
```

<a name="get-sms-recipient"></a>

### Get an SMS recipient

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend();

$smsRecipients = $mailersend->smsRecipient->find('sms_recipient_id');
```

<a name="update-sms-recipient"></a>

### Update a single SMS recipient

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend();

$smsRecipients = $mailersend->smsRecipient->update('sms_recipient_id', $status = 'opt_out');
```

<a name="sms-webhooks-api"></a>

## SMS webhooks API

<a name="get-a-list-of-sms-webhooks"></a>

### Get a list of SMS webhooks

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend();

$smsRecipients = $mailersend->smsWebhook->get('sms_number_id');
```

<a name="get-sms-webhook"></a>

### Get a single SMS webhook

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend();

$smsRecipients = $mailersend->smsWebhook->find('sms_webhook_id');
```

<a name="create-sms-webhook"></a>

### Create a single SMS webhook

```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\SmsWebhookParams;

$mailersend = new MailerSend();

$smsWebhookParams = (new SmsWebhookParams())
    ->setSmsNumberId('sms_number_id')
    ->setName('Name')
    ->setUrl('https://mailersend.com/sms_webhook')
    ->setEvents(['sms.sent', 'sms.delivered', 'sms.failed'])
    ->setEnabled(false);

$smsRecipients = $mailersend->smsWebhook->create($smsWebhookParams);
```

<a name="update-sms-webhook"></a>

### Update a single SMS webhook

```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\SmsWebhookParams;

$mailersend = new MailerSend();

$smsWebhookParams = (new SmsWebhookParams())
    ->setSmsNumberId('sms_number_id')
    ->setName('Name')
    ->setUrl('https://mailersend.com/sms_webhook')
    ->setEvents(['sms.sent', 'sms.delivered', 'sms.failed'])
    ->setEnabled(false);

$smsRecipients = $mailersend->smsWebhook->update($smsWebhookParams);
```

<a name="sms-inbounds-api"></a>

## SMS inbound routing API

<a name="get-a-list-of-sms-inbounds"></a>

### Get a list of SMS inbound routes

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend();

$smsRecipients = $mailersend->smsInbound->getAll($smsNumberId = 'sms_number_id', $enabled = true, $page = 3, $limit = 15);
```

<a name="get-sms-inbound"></a>

### Get a single SMS inbound route

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend();

$smsRecipients = $mailersend->smsInbound->find('sms_inbound_id');
```

<a name="create-sms-inbound"></a>

### Add an SMS inbound route

```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\SmsInbound;
use MailerSend\Helpers\Builder\SmsInboundFilter;

$mailersend = new MailerSend();

$smsInboundParams = (new SmsInbound())
    ->setSmsNumberId('sms_number_id')
    ->setName('Name')
    ->setForwardUrl('https://mailersend.com/inbound_webhook')
    ->setFilter(new SmsInboundFilter($comparer = 'starts-with', $value = 'Stop'))
    ->setEnabled(true);

$smsRecipients = $mailersend->smsInbound->create($smsInboundParams);
```

<a name="update-sms-inbound"></a>

### Update an inbound route

```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\SmsInbound;
use MailerSend\Helpers\Builder\SmsInboundFilter;

$mailersend = new MailerSend();

$smsInboundParams = (new SmsInbound())
    ->setSmsNumberId('sms_number_id')
    ->setName('Name')
    ->setForwardUrl('https://mailersend.com/inbound_webhook')
    ->setFilter(new SmsInboundFilter($comparer = 'starts-with', $value = 'Stop'))
    ->setEnabled(true);

$smsRecipients = $mailersend->smsInbound->update('sms_inbound_id', $smsInboundParams);
```

<a name="delete-sms-inbound"></a>

### Delete an inbound route

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend();

$smsRecipients = $mailersend->smsInbound->delete('sms_inbound_id');
```

<a name="sender-identity-routing"></a>

## Sender identities

<a name="get-a-list-of-sender-identity-routes"></a>

### Get a list of Sender Identities

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend();

$mailersend->senderIdentity->getAll($domainId = 'domainId', $page = 1, $limit = 10);
```

<a name="get-a-single-sender-identity-route"></a>

### Get a single Sender Identity

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend();

$mailersend->senderIdentity->find('identityId');
```

<a name="get-a-single-sender-identity-by-email-route"></a>

### Get a single Sender Identity by email

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->senderIdentity->findByEmail('email');
```

<a name="add-a-sender-identity-route"></a>

### Add a Sender Identity

Example using only classes:

```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\SenderIdentity;

$mailersend = new MailerSend();

$mailersend->senderIdentity->create(
    (new SenderIdentity('domainId', 'name', 'email'))
);
```

Example using all options:

```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\SenderIdentity;

$mailersend = new MailerSend();

$mailersend->senderIdentity->create(
    (new SenderIdentity('domainId', 'name', 'email'))
        ->setReplyToName("John Doe")
        ->setReplyToEmail("john@test.com"))
        ->setAddNote(true)
        ->setPersonalNote("Hi John, please use this token")
);
```

<a name="update-a-sender-identity-route"></a>

### Update a Sender Identity

The examples on building the `Sender Identity` object portrayed in the 'Add a Sender Identity' also apply in here.

```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\SenderIdentity;

$mailersend = new MailerSend();

$mailersend->senderIdentity->update(
    'identityId',
    (new SenderIdentity('domainId', 'name', 'email'))
        ->setReplyToName("John Doe")
        ->setReplyToEmail("john@test.com"))
        ->setAddNote(true)
        ->setPersonalNote("Hi John, please use this token")
);
```

<a name="update-a-sender-identity-by-email-route"></a>

### Update a Sender Identity by email

The examples on building the `Sender Identity` object portrayed in the 'Add a Sender Identity' also apply in here.

```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\SenderIdentity;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->senderIdentity->updateByEmail(
    'identityId',
    (new SenderIdentity('domainId', 'name', 'email'))
        ->setReplyToName("John Doe")
        ->setReplyToEmail("john@test.com"))
        ->setAddNote(true)
        ->setPersonalNote("Hi John, please use this token")
);
```

<a name="delete-a-sender-identity-route"></a>

### Delete a Sender Identity

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend();

$mailersend->senderIdentity->delete('identityId');
```

<a name="delete-a-sender-identity-by-email-route"></a>

### Delete a Sender Identity by email

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->senderIdentity->deleteByEmail('email');
```

<a name="smtp-user-routing"></a>

## SMTP Users

<a name="get-a-list-of-smtp-users"></a>

### Get a list of SMTP Users

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->smtpUser->getAll('domainId', 25);
```

<a name="get-a-single-smtp-user"></a>

### Get a single SMTP User
```php
use MailerSend\MailerSend;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->smtpUser->find('domainId', 'smtpUserId');
```

<a name="add-smtp-user"></a>

### Add SMTP User
```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\UserParams;
use MailerSend\Helpers\Builder\SmtpUserParams;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->smtpUser->create(
    'domainId',
    (new SmtpUserParams('name'))
        ->setEnabled(false)
);
```

<a name="update-smtp-user"></a>

### Update SMTP User

The examples on building the `SMTP User` object portrayed in the 'Add SMTP User' also apply in here.
```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\UserParams;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->smtpUser->update(
    'domainId',
    'smtpUserId',
    (new SmtpUserParams('New name'))
        ->setEnabled(false)
);
```

<a name="delete-smtp-user"></a>

### Delete SMTP User
```php
use MailerSend\MailerSend;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->smtpUser->delete('domainId', 'smtpUserId');
```

<a name="user-routing"></a>

## Users

<a name="get-a-list-of-users"></a>

### Get a list of Users

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->user->getAll();
```

<a name="get-a-single-user"></a>

### Get a single User

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->user->find('userId');
```

<a name="add-a-user"></a>

### Add a User

Example using only classes:

```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\UserParams;
use MailerSend\Common\Roles;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->user->create(
    (new UserParams('email', Roles::ADMIN))
);
```

Example using all options:

```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\UserParams;
use MailerSend\Common\Roles;
use MailerSend\Common\Permissions;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->user->create(
    (new UserParams('email', Roles::CUSTOM_USER))
        ->setDomains(['domainId', 'anotherDomainId'])
        ->setTemplates(['templateId', 'anotherTemplateId'])
        ->setPermissions([Permissions::READ_OWN_TEMPLATES])
        ->setRequiresPeriodicPasswordChange(true)
);
```

<a name="update-a-user"></a>

### Update a User

The examples on building the `User` object portrayed in the 'Add a User' also apply in here.

```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\UserParams;
use MailerSend\Common\Roles;
use MailerSend\Common\Permissions;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->user->update(
    'userId',
    (new UserParams())
        ->setRole(Roles::CUSTOM_USER)
        ->setDomains(['domainId', 'anotherDomainId'])
        ->setTemplates(['templateId', 'anotherTemplateId'])
        ->setPermissions([Permissions::READ_OWN_TEMPLATES])
        ->setRequiresPeriodicPasswordChange(true)
);
```

<a name="delete-a-user"></a>

### Delete a User

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->user->delete('userId');
```

<a name="get-a-list-of-invites></a>

### Get a list of Invites

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->invite->getAll();
```

<a name="get-a-single-invite"></a>

### Get a single Invite

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->invite->find('inviteId');
```

<a name="resend-an-invite"></a>

### Resend an Invite

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->invite->resend('inviteId');
```


<a name="cancel-an-invite></a>

### Cancel an Invite

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->invite->cancel('inviteId');
```


<a name="other-endpoints"></a>

## Other endpoints

<a name="get-api-quota"></a>

### Get API quota

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend();

$mailersend->apiQuota->get();
```

<a name="debugging-validation-errors"></a>
# Debugging validation errors

```php
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\Variable;
use MailerSend\Helpers\Builder\Recipient;
use MailerSend\Helpers\Builder\EmailParams;
use MailerSend\Exceptions\MailerSendValidationException;
use MailerSend\Exceptions\MailerSendRateLimitException;

$mailersend = new MailerSend();

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
    print_r($e->getBody());
    print_r($e->getHeaders());
    print_r($e->getErrors());
    print_r($e->getStatusCode());
} catch (MailerSendRateLimitException $e) {
    print_r($e->getHeaders());
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

