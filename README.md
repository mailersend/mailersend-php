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

Sending a basic email.

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

For more expanded usage info, see [guide](GUIDE.md).

<a name="endpoints"></a>
# Available endpoints

| Feature group | Endpoint    | Available |
| ------------- | ----------- | --------- |
| Email         | `POST send` | ✅         |

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


