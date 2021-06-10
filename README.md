<a href="https://www.mailersend.com"><img src="https://www.mailersend.com/images/logo.svg" width="200px"/></a>

MailerSend PHP SDK

[![MIT licensed](https://img.shields.io/badge/license-MIT-blue.svg)](./LICENSE.md)
![build badge](https://github.com/mailersend/mailersend-php/actions/workflows/php.yml/badge.svg)
![analysis badge](https://github.com/mailersend/mailersend-php/actions/workflows/static-analysis.yml/badge.svg)

# Table of Contents

* [Installation](#installation)
* [Usage](#usage)
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

### Sending a basic email.

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

### Messages

**List messages**

```php
### Webhooks endpoint

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
use MailerSend\Helpers\Builder\WebhookParams;
use MailerSend\MailerSend;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->messages->get($limit = 100, $page = 3);
```

**Find a specific message**

```php
$mailersend->webhooks->update('webhook_id', 'https://webhook_url', 'Webhook name', WebhookParams::ALL_ACTIVITIES);
```

**Disable/Enable a Webhook**

```php
use MailerSend\Helpers\Builder\WebhookParams;
use MailerSend\MailerSend;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->messages->find('message_id');
```

$mailersend->webhooks->update('webhook_id', 'https://webhook_url', 'Webhook name', WebhookParams::ALL_ACTIVITIES, true); //Enabled
$mailersend->webhooks->update('webhook_id', 'https://webhook_url', 'Webhook name', WebhookParams::ALL_ACTIVITIES, false); //Disabled
```

**Delete a Webhook**

```php
use MailerSend\MailerSend;

$mailersend = new MailerSend(['api_key' => 'key']);

$mailersend->webhooks->delete('webhook_id');

```

###Managing Tokens

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

### Analytics

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

### Activity

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

### Domain

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
### Recipients

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

For more expanded usage info, see [guide](GUIDE.md).

<a name="endpoints"></a>
# Available endpoints

| Feature group             | Endpoint                          | Available |
| -------------             | -----------                       | --------- |
| Email                     | `POST send`                       | ✅        |
| Webhook : list            | `GET webhooks`                    | ✅        |
| Webhook : find            | `GET webhooks/{webhook_id}`       | ✅        |
| Webhook : create          | `POST webhooks`                   | ✅        |
| Webhook : update          | `PUT webhooks/{webhook_id}`       | ✅        |
| Webhook : delete          | `DELETE webhooks/{webhook_id}`    | ✅        |
| Token : Create            | `POST token`                      | ✅        |
| Token : Update            | `PUT token/{token_id}/settings`   | ✅        |
| Token : Delete            | `DELETE token/{token_id}`         | ✅        |
| Analytics                 | `GET activityDataByDate`          | ✅        |
| Analytics                 | `GET opensByCountry`              | ✅        |
| Analytics                 | `GET opensByUserAgentName`        | ✅        |
| Analytics                 | `GET opensByReadingEnvironment`   | ✅        |
| Domain                    | `GET getAll`                      | ✅        |
| Domain                    | `GET find`                        | ✅        |
| Domain                    | `DELETE delete`                   | ✅        |
| Domain                    | `GET recipients`                  | ✅        |
| Domain                    | `PUT domainSettings`              | ✅        |
| Recipients : list         | `GET messages`                    | ✅        |
| Recipients : find         | `GET messages/{token_id}`         | ✅        |
| Recipients : delete       | `DELETE messages/{token_id}`      | ✅        |

*If, at the moment, some endpoint is not available, please use `cURL` and other available tools to access it. [Refer to official API docs for more info](https://developers.mailersend.com/).*

## Testing

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


