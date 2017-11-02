SNS Push (for AWS SNS API)
======

> This package provides a bunch of helper methods to aid interacting with the Amazon (AWS) SNS API.

[![Packagist](https://img.shields.io/badge/redu-sns--push-brightgreen.svg)](https://packagist.org/packages/redu/sns-push)

SNS Push is a simple SNS SDK wrapper with a collection of methods to aid in interacting with the AWS SNS API. It works directly with Laravel or can be used as a standalone PHP package.

# Prerequisites

 Supports  | Version
:----------|:----------
 PHP       | 7.0
 Platforms | ios/android

# Installing

You need to use Composer to install SNS Push into your project:

```
composer require redu/sns-push
```

## Configuring (Laravel)

Now you have to include `SNSPushServiceProvider` in your `config/app.php`:

```php
<?php

'providers' => [
    /*
     * Package Service Providers...
     */
    SNSPush\SNSPushServiceProvider::class,
]
```

Add 'sns' config keys to the `config/services.php`

```php
<?php

'sns' => [
    'account_id' => env('SNS_ACCOUNT_ID', ''),
    'access_key' => env('SNS_ACCESS_KEY', ''),
    'secret_key' => env('SNS_SECRET_KEY', ''),
    'scheme' => env('SNS_SCHEME', 'https'),
    'region' => env('SNS_REGION', 'eu-west-1'),
    'platform_applications' => [
        'ios' => '<application-endpoint-arn>',
        'android' => '<application-endpoint-arn>'
    ]
],
```

## Other PHP Framework (not Laravel) Setup

You should include the Composer `autoload.php` file if not already loaded:

```php
require __DIR__ . '/vendor/autoload.php';
 ```

Instantiate the SNSPush class with the following required config values:
- account_id
- access_key
- secret_key
- platform_applications

Also configurable:
- region [default: eu-west-1]
- api_version [default: 2010-03-31]
- scheme [default: https]

```php
<?php

$sns = new SNSPush([
    'account_id' => '<aws-account-id>', // Required
    'access_key' => '<aws-iam-user-access-key>', // Required
    'secret_key' => '<aws-iam-user-secret-key>', // Required
    'scheme' => 'http', // Defaults to https
    'platform_applications' => [ // Required
        'ios' => '...',
        'android' => '...'
    ]
]);
```

## Add Device to Application

Add a device to a platform application (ios/android) by passing the device token and application key to `addDevice()`.

```php
<?php

$sns->addDevice('<device-token>, 'ios');
```

## Remove Device from Application

Remove a device from AWS SNS by passing the Endpoint ARN to `removeDevice()`.

```php
<?php

$sns->removeDevice('<endpoint-arn>');
```

## Subscribe Device to Topic

Subscribe a device to a Topic by passing the Endpoint Arn and Topic Arn to `subscribeDeviceToTopic()`.

```php
<?php

$sns->subscribeDeviceToTopic('<endpoint-arn>', '<topic-arn>');
```

## Remove Device from Topic

Remove a device from a Topic by passing the Subscription Arn to `removeDeviceFromTopic()`.

```php
<?php

$sns->removeDeviceFromTopic('<subscription-arn>');
```

## Sending Push Notifications

SNS Push supports sending notifications to both Topic Endpoint or directly to an Endpoint ARN (Device).

### Messages

Messages can either be submitted as a `string`, or an object which implements `SNSPush\Message\MessageInterface`, such as `SNSPush\Message\Message`. This formats the message appropriately for Android and iOS.

```php
<?php

use SNSPush\Message\Message

$message = new Message();

$message->setTitle('Message Title')
        ->setBody('Message body')
        ->setBadge(5)
        ->setIosSound('sound.caf')
        ->setAndroidSound('sound')
        ->setPayload(
          [
              'custom-key' => 'value',
          ]
      );
```
or as a string:

```php
<?php

$message = "Message body as a string";
```

### Send to Device

Simply pass a message as either a `string` or a `SNSPush\Message\MessageInterface` object, along with the endpoint ARN

```php
<?php

$sns->sendPushNotificationToDevice(
    '<endpoint-arn>',
    $message
);
```

The message structure is sent as JSON and will be properly formatted per device from the `MessageInterface` object. This is a requirement if sending to multiple platforms and/or sending a custom payload.

### Send to Topic

```php
<?php

$sns->send->sendPushNotificationToTopic(
    '<topic-arn>',
    $message
);
```

The message should be configured in the same way as for a device endpoint.

## To do
- Support more endpoints
- Test, test, test... (still in early development, use with caution)

## Licence

[MIT License](https://github.com/ReduGroup/sns-push/blob/master/LICENSE.md) © Redu Group Ltd
