Google-Translate-PHP
====================

[![Latest Stable Version](https://img.shields.io/packagist/v/Stichoza/google-translate-php.svg)](https://packagist.org/packages/stichoza/google-translate-php) [![Total Downloads](https://img.shields.io/packagist/dt/Stichoza/google-translate-php.svg)](https://packagist.org/packages/stichoza/google-translate-php) [![Downloads Month](https://img.shields.io/packagist/dm/Stichoza/google-translate-php.svg)](https://packagist.org/packages/stichoza/google-translate-php) [![License](https://img.shields.io/packagist/l/Stichoza/google-translate-php.svg)](https://packagist.org/packages/stichoza/google-translate-php) [![Code Climate](https://img.shields.io/codeclimate/github/Stichoza/google-translate-php.svg)](https://codeclimate.com/github/Stichoza/google-translate-php) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Stichoza/google-translate-php/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Stichoza/google-translate-php/?branch=master)

Free Google Translate API PHP Package. Translates totally free of charge.

## Installation

Install this package via [Composer](https://getcomposer.org/).

```
composer require stichoza/google-translate-php
```

Or edit your project's `composer.json` to require `stichoza/google-translate-php` and then run `composer update`.

```json
"require": {
    "stichoza/google-translate-php": "~3.0"
}
```

## Usage

#### Basic Usage

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
echo TranslateClient::translate('Hello again', 'en', 'ka');
```

#### Language Detection

To detect language automatically, just set the source language to `null`

```php
$tr = new TranslateClient(null, 'es'); // Detect language and translate to Spanish
```

```php
$tr->setSource(null); // Another way
```

#### Get Detected Language

**Warning!** This feature is **experimental** and works only for object calls (non-static).

```php
$tr = new TranslateClient(null, 'fr');
$text = $tr->translate('Hello World!');
echo $tr->getLastDetectedSource(); // Output: en
```

Return value may be boolean `FALSE` if there is no detected language.

#### Available languages

Supported languages are listed in [Google API docs](https://cloud.google.com/translate/v2/using_rest#language-params).

#### Errors and Exception Handling

Both static and non-static `translate()` methods will throw following Exceptions:

 - `InvalidArgumentException` If parameters are passed incorrectly.
 - `ErrorException` If the HTTP request fails for some reason.
 - `UnexpectedValueException` If data received from Google cannot be decoded.
 - `BadMethodCallException` If you call something wrong. Call `translate()`, not Ghost Busters

In addition `translate()` method will return boolean `FALSE` if there is no translation available.

#### Older versions

See older (`~2.0`) docs [here](https://github.com/Stichoza/google-translate-php/tree/7bdf29ed44ed71dadac80ec389699ee327acdf27)

## Disclaimer

This package is developed for educational purposes only. Do not depend on this package as it may break anytime as it is based on "CURLing" the Google Translate website. Consider buying [Official Google Translate API](https://cloud.google.com/translate/) for other types of usage.
