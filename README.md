Google Translate PHP
====================

[![Build Status](https://travis-ci.org/Stichoza/google-translate-php.svg?branch=master)](https://travis-ci.org/Stichoza/google-translate-php) [![Latest Stable Version](https://img.shields.io/packagist/v/Stichoza/google-translate-php.svg)](https://packagist.org/packages/stichoza/google-translate-php) [![Total Downloads](https://img.shields.io/packagist/dt/Stichoza/google-translate-php.svg)](https://packagist.org/packages/stichoza/google-translate-php) [![Downloads Month](https://img.shields.io/packagist/dm/Stichoza/google-translate-php.svg)](https://packagist.org/packages/stichoza/google-translate-php) [![Code Climate](https://img.shields.io/codeclimate/github/Stichoza/google-translate-php.svg)](https://codeclimate.com/github/Stichoza/google-translate-php) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Stichoza/google-translate-php/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Stichoza/google-translate-php/?branch=master)

Free Google Translate API PHP Package. Translates totally free of charge.

## Installation

Install this package via [Composer](https://getcomposer.org/).

```
composer require stichoza/google-translate-php
```

Or edit your project's `composer.json` to require `stichoza/google-translate-php` and then run `composer update`.

```json
"require": {
    "stichoza/google-translate-php": "~3.2"
}
```

## Usage

### Basic Usage

> **Note:** You should have composer's autoloader included `require 'vendor/autoload.php'` (that's obvious.)

Instantiate TranslateClient object

```php
use Stichoza\GoogleTranslate\TranslateClient;

$tr = new TranslateClient('en', 'ka');
```
Or set/change languages later
```php
$tr = new TranslateClient(); // Default is from 'auto' to 'en'
$tr->setSource('en'); // Translate from English
$tr->setTarget('ka'); // Translate to Georgian
$tr->setUrlBase('http://translate.google.cn/translate_a/single'); // Set Google Translate URL base (This is not necessary, only for some countries)
```
Translate sentences
```php
echo $tr->translate('Hello World!');
```
Also, you can also use method chaining
```php
echo $tr->setSource('en')->setTarget('ka')->translate('Goodbye');
```
Or call a static method
```php
echo TranslateClient::translate('en', 'ka', 'Hello again');
```

As of v3.2 multiple sentence/array translation is available.

```php
echo $tr->translate(['I can dance', 'I like trains', 'Double rainbow']);
```

As of v3.2.3 you can call `getResponse()` method to get raw response from Google Translate. Note that this method is not available for static calls.

```php
$tr->getResponse($word); // Returns raw array of translated data.
```

### Advanced Configuration

This package uses [Guzzle](https://github.com/guzzle/guzzle) for HTTP requests. You can pass an associative array of [guzzle client configuration options](http://guzzle.readthedocs.org/en/5.3/clients.html#creating-a-client) as a third parameter to `TranslateClient` constructor.

You can configure proxy, user-agent, default headers, connection timeout and so on using this options.

```php
$tr = new TranslateClient(null, 'en', [
    'defaults' => [
        'timeout' => 10,
        'proxy' => [
            'http'  => 'tcp://localhost:8125',
            'https' => 'tcp://localhost:9124'
        ],
        'headers' => [
            'User-Agent' => 'Foo/5.0 Lorem Ipsum Browser'
        ]
    ]
]);
```

You can use `setHttpOption` method configure [guzzle client configuration options](http://docs.guzzlephp.org/en/latest/request-options.html).

```php
// set proxy to tcp://localhost:8090
$tr->setHttpOption(['proxy' => 'tcp://localhost:8090'])->translate('Hello');

// set proxy to socks5://localhost:1080
$tr->setHttpOption(['proxy' => 'socks5://localhost:1080'])->translate('World');
```

For more information, see [Creating a Client](http://guzzle.readthedocs.org/en/latest/quickstart.html#creating-a-client) section in Guzzle docs (6.x version).

### Language Detection

To detect language automatically, just set the source language to `null`

```php
$tr = new TranslateClient(null, 'es'); // Detect language and translate to Spanish
```

```php
$tr->setSource(null); // Another way
```

#### Get Detected Language

You can also use `getLastDetectedSource()` method both statically and non-statically to get detected language.

```php
$tr = new TranslateClient(null, 'fr');

$text = $tr->translate('Hello World!');

echo $tr->getLastDetectedSource();             // Output: en
echo TranslateClient::getLastDetectedSource(); // Output: en
```

> **Note:** Value of last detected source is same for both static and non-static method calls.

Return value may be boolean `FALSE` if there is no detected language.

#### Available languages

Supported languages are listed in [Google API docs](https://cloud.google.com/translate/v2/using_rest#language-params).

### Errors and Exception Handling

Both static and non-static `translate()` methods will throw following Exceptions:

 - `InvalidArgumentException` If parameters are passed incorrectly.
 - `ErrorException` If the HTTP request fails for some reason.
 - `UnexpectedValueException` If data received from Google cannot be decoded.
 - `BadMethodCallException` If you call something wrong. Call `translate()`, not Ghost Busters

In addition `translate()` method will return boolean `FALSE` if there is no translation available.

## Disclaimer

This package is developed for educational purposes only. Do not depend on this package as it may break anytime as it is based on crawling the Google Translate website. Consider buying [Official Google Translate API](https://cloud.google.com/translate/) for other types of usage.

Also, Google might ban your server IP or [require to solve CAPTCHA](https://github.com/Stichoza/google-translate-php/issues/18) if you send unusual traffic (large amount of data/requests).
 
