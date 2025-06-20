Google Translate PHP
====================

[![Latest Stable Version](https://img.shields.io/packagist/v/Stichoza/google-translate-php.svg)](https://packagist.org/packages/stichoza/google-translate-php) [![Total Downloads](https://img.shields.io/packagist/dt/Stichoza/google-translate-php.svg)](https://packagist.org/packages/stichoza/google-translate-php) [![Downloads Month](https://img.shields.io/packagist/dm/Stichoza/google-translate-php.svg)](https://packagist.org/packages/stichoza/google-translate-php) [![Petreon donation](https://img.shields.io/badge/patreon-donate-orange.svg)](https://www.patreon.com/stichoza) [![PayPal donation](https://img.shields.io/badge/paypal-donate-blue.svg)](https://paypal.me/stichoza)

Free Google Translate API PHP Package. Translates totally free of charge.

---

 - **[Installation](#installation)**
 - **[Basic Usage](#basic-usage)**
 - [Advanced Usage](#advanced-usage)
   - [Language Detection](#language-detection)
   - [Supported Languages](#supported-languages)
   - [Preserving Parameters](#preserving-parameters)
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
> [!Note]
> **PHP 8.0 or later** is required. Use following versions of this package for older PHP versions:

| Package version | PHP Version | Documentation                                                                             |
|-----------------|-------------|-------------------------------------------------------------------------------------------|
| `^5.1`          | PHP >= 8.0  | [v5 Docs](#google-translate-php)                                                          |
| `^4.1`          | PHP >= 7.1  | [v4 Docs](https://github.com/Stichoza/google-translate-php/tree/4.1#google-translate-php) |
| `^3.2`          | PHP < 7.1   | [v3 Docs](https://github.com/Stichoza/google-translate-php/tree/3.2#google-translate-php) |

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

### Supported Languages

You can get a list of all the supported languages using the `languages` method.

```php
$tr = new GoogleTranslate();

$languages = $tr->languages(); // Get supported languages in iso-639 format

// Output: [ 'ab', 'ace', 'ach', 'aa', 'af', 'sq', 'alz', ... ]
```

Optionally, pass a target language code to retrieve supported languages with names displayed in that language.

```php
$tr = new GoogleTranslate();

$languages = $tr->languages('en'); // Get supported languages, display name in english
// Output: [ 'en' => English', 'es' => 'Spanish', 'it' => 'Italian', ... ]

echo $languages['en']; // Output: 'English'
echo $languages['ka']; // Output: 'Georgian'
```

Same as with the `translate`/`trans` methods, you can also use a static `langs` method:

```php
GoogleTranslate::langs();
// Output: [ 'ab', 'ace', 'ach', 'aa', 'af', 'sq', 'alz', ... ]

GoogleTranslate::langs('en');
// Output: [ 'en' => English', 'es' => 'Spanish', 'it' => 'Italian', ... ]
```

Supported languages are also listed in [Google API docs](https://cloud.google.com/translate/docs/languages).

### Preserving Parameters

The `preserveParameters()` method allows you to preserve certain parameters in strings while performing translations. This is particularly useful when dealing with localization files or templating engines where specific placeholders need to be excluded from translation.

Default regex is `/:(\w+)/` which covers parameters starting with `:`. Useful for translating language files of Laravel and other frameworks. You can also pass your custom regex to modify the parameter syntax.

```php
$tr = new GoogleTranslate('de');

$text = $tr->translate('Page :current of :total'); // Seite :aktuell von :gesamt

$text = $tr->preserveParameters()
           ->translate('Page :current of :total'); // Seite :current von :total
```

Or use custom regex:

```php
$text = $tr->preserveParameters('/\{\{([^}]+)\}\}/')
           ->translate('Page {{current}} of {{total}}'); // Seite {{current}} von {{total}}
```

You can use same feature with static `trans()` method too.

```php
GoogleTranslate::trans('Welcome :name', 'fr', preserveParameters: true); // Default regex

GoogleTranslate::trans('Welcome {{name}}', 'fr', preserveParameters: '/\{\{([^}]+)\}\}/'); // Custom regex
```

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

For more information, see [Creating a Client](http://guzzle.readthedocs.org/en/latest/quickstart.html#creating-a-client) section in Guzzle docs.

### Custom Token Generator

You can override the token generator class by passing a generator object as a fourth parameter of constructor or just use `setTokenProvider` method.

Generator must implement `Stichoza\GoogleTranslate\Tokens\TokenProviderInterface`.

```php
use Stichoza\GoogleTranslate\Tokens\TokenProviderInterface;

class MyTokenGenerator implements TokenProviderInterface
{
    public function generateToken(string $source, string $target, string $text): string
    {
        // Your code here
    }
}
```

And use:

```php
$tr->setTokenProvider(new MyTokenGenerator);
```

### Translation Client (Quality)

Google Translate has a parameter named `client` which defines quality of translation. First it was set to `webapp` but later google added `gtx` value which results in a better translation quality in terms of grammar and overall meaning of sentences.

You can use `->setClient()` method to switch between clients. For example if you want to use older version of translation algorithm, type `$tr->setClient('webapp')->translate('lorem ipsum...')`. Default value is `gtx`.

### Errors and Exception Handling

Static method `trans()` and non-static `translate()` and `getResponse()` methods will throw following exceptions:

 - `ErrorException` If the HTTP request fails for some reason.
 - `UnexpectedValueException` If data received from Google cannot be decoded.

As of **v5.1.0** concrete exceptions are available in `\Stichoza\GoogleTranslate\Exceptions` namespace:

 - `LargeTextException` If the requested text is too large to translate.
 - `RateLimitException` If Google has blocked you for excessive amount requests.
 - `TranslationRequestException` If any other HTTP related error occurs during translation.
 - `TranslationDecodingException` If the response JSON cannot be decoded.

All concrete exceptions are backwards compatible, so if you were using older versions, you won't have to update your code.

`TranslationDecodingException` extends `UnexpectedValueException`, while `LargeTextException`, `RateLimitException` and `TranslationRequestException` extend `ErrorException` that was used in older versions (`<5.1.0`) of this package.

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

If this package helped you reduce your time to develop something, or it solved any major problems you had, feel free to give me a cup of coffee :)

 - [Patreon](https://www.patreon.com/stichoza)
 - [PayPal](https://paypal.me/stichoza)
