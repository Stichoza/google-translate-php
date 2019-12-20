Google Translate PHP
====================

[![Build Status](https://travis-ci.org/Stichoza/google-translate-php.svg?branch=master)](https://travis-ci.org/Stichoza/google-translate-php) [![Latest Stable Version](https://img.shields.io/packagist/v/Stichoza/google-translate-php.svg)](https://packagist.org/packages/stichoza/google-translate-php) [![Total Downloads](https://img.shields.io/packagist/dt/Stichoza/google-translate-php.svg)](https://packagist.org/packages/stichoza/google-translate-php) [![Downloads Month](https://img.shields.io/packagist/dm/Stichoza/google-translate-php.svg)](https://packagist.org/packages/stichoza/google-translate-php) [![Petreon donation](https://img.shields.io/badge/patreon-donate-orange.svg)](https://www.patreon.com/stichoza) [![PayPal donation](https://img.shields.io/badge/paypal-donate-blue.svg)](https://paypal.me/stichoza)

Free Google Translate API PHP Package. Translates totally free of charge.

---

 - **[Installation](#installation)**
 - **[Basic Usage](#basic-usage)**
 - [Advanced Usage](#advanced-usage)
   - [Language Detection](#language-detection)
   - [Using Raw Response](#using-raw-response)
   - [Custom URL](#custom-url)
   - [HTTP Client Configuration](#http-client-configuration)
   - [Custom Token Generator](#custom-token-generator)
   - [Errors and Exception Handling](#errors-and-exception-handling)
 - [Known Limitations](#known-limitations)
 - [Disclaimer](#disclaimer)
 - [Donation](#donation)

## Installation

Install this package via [Composer](https://getcomposer.org/).

```
composer require stichoza/google-translate-php
```

> Note: **PHP 7.1 or later** is required. For older versoins, use `^3.2` version of this package (see [old docs](https://github.com/Stichoza/google-translate-php/tree/3.2#google-translate-php)).

## Basic Usage

Create GoogleTranslate object

```php
use Stichoza\GoogleTranslate\GoogleTranslate;

$tr = new GoogleTranslate('en'); // Translates into English
```
Or you can change languages later
```php
$tr = new GoogleTranslate(); // Translates to 'en' from auto-detected language by default
$tr->setSource('en'); // Translate from English
$tr->setSource(); // Detect language automatically
$tr->setTarget('ka'); // Translate to Georgian
```
Translate sentences
```php
echo $tr->translate('Hello World!');
```
Also, you can also use method chaining
```php
echo $tr->setSource('en')->setTarget('ka')->translate('Goodbye');
```
Or call a shorthand static method `trans`
```php
echo GoogleTranslate::trans('Hello again', 'ka', 'en');
```

## Advanced Usage

### Language Detection

To detect language automatically, just set the source language to `null`:

```php
$tr = new GoogleTranslate('es', null); // Or simply do not pass the second parameter 
```

```php
$tr->setSource(); // Another way
```

Use `getLastDetectedSource()` to get detected language:

```php
$tr = new GoogleTranslate('fr');

$text = $tr->translate('Hello World!');

echo $tr->getLastDetectedSource(); // Output: en
```

Return value will be `null` if the language couldn't be detected.

Supported languages are listed in [Google API docs](https://cloud.google.com/translate/docs/languages).

### Using Raw Response

For advanced usage, you might need the raw results that Google Translate provides. you can use `getResponse` method for that.

```php
$responseArray = $tr->getResponse('Hello world!');
```

### Custom URL

You can override the default Google Translate url by `setUrl` method. Useful for some countries

```php
$tr->setUrl('http://translate.google.cn/translate_a/single'); 
```

### HTTP Client Configuration

This package uses [Guzzle](https://github.com/guzzle/guzzle) for HTTP requests. You can pass an array of [guzzle client configuration options](http://docs.guzzlephp.org/en/latest/request-options.html) as a third parameter to `GoogleTranslate` constructor, or just use `setOptions` method.

You can configure proxy, user-agent, default headers, connection timeout and so on using this options.

```php
$tr = new GoogleTranslate('en', 'ka', [
    'timeout' => 10,
    'proxy' => [
        'http'  => 'tcp://localhost:8125',
        'https' => 'tcp://localhost:9124'
    ],
    'headers' => [
        'User-Agent' => 'Foo/5.0 Lorem Ipsum Browser'
    ]
]);
```

```php
// Set proxy to tcp://localhost:8090
$tr->setOptions(['proxy' => 'tcp://localhost:8090'])->translate('Hello');

// Set proxy to socks5://localhost:1080
$tr->setOptions(['proxy' => 'socks5://localhost:1080'])->translate('World');
```

For more information, see [Creating a Client](http://guzzle.readthedocs.org/en/latest/quickstart.html#creating-a-client) section in Guzzle docs (6.x version).

### Custom Token Generator

You can override the token generator class by passing a generator object as a fourth parameter of constructor or just use `setTokenProvider` method.

Generator must implement `Stichoza\GoogleTranslate\Tokens\TokenProviderInterface`.

```php
use Stichoza\GoogleTranslate\Tokens\TokenProviderInterface;

class MyTokenGenerator implements TokenProviderInterface
{
    public function generateToken(string $source, string $target, string $text) : string
    {
        // Your code here
    }
}
```

And use:

```php
$tr->setTokenProvider(new MyTokenGenerator);
```

### Errors and Exception Handling

Static method `trans()` and non-static `translate()` and `getResponse()` will throw following Exceptions:

 - `ErrorException` If the HTTP request fails for some reason.
 - `UnexpectedValueException` If data received from Google cannot be decoded.

In addition, `translate()` and `trans()` methods will return `null` if there is no translation available.

## Known Limitations
 
 - `503 Service Unavailable` response:
   If you are getting this error, it is most likely that Google has banned your external IP address and/or [requires you to solve a CAPTCHA](https://github.com/Stichoza/google-translate-php/issues/18). This is not a bug in this package. Google has become stricter, and it seems like they keep lowering the number of allowed requests per IP per a certain amount of time. Try sending less requests to stay under the radar, or change your IP frequently ([for example using proxies](#http-client-configuration)). Please note that once an IP is banned, even if it's only temporary, the ban can last from a few minutes to more than 12-24 hours, as each case is different.
 - `429 Too Many Requests` response:
   This error is basically the same as explained above.
 - `413 Request Entity Too Large` response:
   This error means that your input string is too long. Google only allows a maximum of 5000 characters to be translated at once. If you want to translate a longer text, you can split it to shorter parts, and translate them one-by-one.
 - `403 Forbidden` response:
   This is not an issue with this package. Google Translate itself has some problems when it comes to translating some characters and HTML entities. See https://github.com/Stichoza/google-translate-php/issues/119#issuecomment-558078133

 
## Disclaimer

This package is developed for educational purposes only. Do not depend on this package as it may break anytime as it is based on crawling the Google Translate website. Consider buying [Official Google Translate API](https://cloud.google.com/translate/) for other types of usage.

## Donation

If this package helped you reduce your time to develop something, or it solved any major problems you had, feel free give me a cup of coffee :)

 - [Patreon](https://www.patreon.com/stichoza)
 - [PayPal](https://paypal.me/stichoza)
 
