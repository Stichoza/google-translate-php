Google-Translate-PHP
====================

[![Latest Stable Version](https://img.shields.io/packagist/v/Stichoza/google-translate-php.svg)](https://packagist.org/packages/stichoza/google-translate-php) [![Total Downloads](https://img.shields.io/packagist/dt/Stichoza/google-translate-php.svg)](https://packagist.org/packages/stichoza/google-translate-php) [![Downloads Month](https://img.shields.io/packagist/dm/Stichoza/google-translate-php.svg)](https://packagist.org/packages/stichoza/google-translate-php) [![License](https://img.shields.io/packagist/l/Stichoza/google-translate-php.svg)](https://packagist.org/packages/stichoza/google-translate-php) [![Code Climate](https://img.shields.io/codeclimate/github/Stichoza/google-translate-php.svg)](https://codeclimate.com/github/Stichoza/google-translate-php) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Stichoza/google-translate-php/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Stichoza/google-translate-php/?branch=master)

Google Translate API free PHP class. Translates totally free of charge.

## Installation

**New!** Now available via [Composer](https://getcomposer.org/) :sunglasses:

Install this package through [Composer](https://getcomposer.org/). Edit your project's `composer.json` file to require `stichoza/google-translate-php`.

```json
"require": {
    "stichoza/google-translate-php": "~2.0"
}
```

**Or** run a command in your command line:

```
composer require stichoza/google-translate-php
```

## Usage

Instantiate GoogleTranslate object
```php
use Stichoza\Google\GoogleTranslate;

$tr = new GoogleTranslate("en", "ka");
```
Or set/change languages later
```php
$tr = new GoogleTranslate();
$tr->setLangFrom("en");
$tr->setLangTo("ka");
```
Translate sentences
```php
echo $tr->translate("Hello World!");
```
Also, you can use shorter syntax:
```php
echo $tr->setLangFrom("en")->setLangTo("ru")->translate("Goodbye");
```
Or call a static method
```php
echo GoogleTranslate::staticTranslate("Hello again", "en", "ka");
```

## Disclaimer

This package is developed for educational purposes only. Do not depend on this package as it may break anytime as it is based on "CURLing" the Google Translate website. Consider buying [Official Google Translate API](https://cloud.google.com/translate/) for other types of usage.
