# Trustpilot Invitation API Client

A PHP library for accessing the [Trustpilot Invitation API](https://developers.trustpilot.com/invitation-api).

This library was originally developed and open sourced by [moneymaxim](https://www.moneymaxim.co.uk).

It has subsequently been forked and updated to use Guzzle7

## Install

Install using [composer](https://getcomposer.org/):

```sh
composer install retrochaos/trustpilot-invitation-api
```

## Usage

```php
use Trustpilot\Api\Authenticator\Authenticator;
use Trustpilot\Api\Invitation\Client;
use Trustpilot\Api\Invitation\Recipient;
use Trustpilot\Api\Invitation\Sender;
use Trustpilot\Api\Invitation\Context;

$authenticator = new Authenticator();
$accessToken = $authenticator->getAccessToken($apiKey, $apiToken, $username, $password);

$client = new Client($accessToken);

$context = new Context($businessUnitId, $templateId, $redirectUri);
// The last two arguments to the Context constructor ($tags and $locale) are optional
// $context = new Context($templateId, $redirectUri, $tags = array(), $locale = 'en-US');

$recipient = new Recipient($recipientEmail, $recipientName);
$sender    = new Sender($senderEmail, $senderName, $replyTo);

$client->invite($context, $recipient, $sender, $reference) /* : array */
```
