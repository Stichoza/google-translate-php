Google-Translate-PHP
====================

[![Latest Stable Version](https://poser.pugx.org/stichoza/google-translate-php/v/stable.svg)](https://packagist.org/packages/stichoza/google-translate-php) [![Total Downloads](https://poser.pugx.org/stichoza/google-translate-php/downloads.svg)](https://packagist.org/packages/stichoza/google-translate-php) [![Latest Unstable Version](https://poser.pugx.org/stichoza/google-translate-php/v/unstable.svg)](https://packagist.org/packages/stichoza/google-translate-php) [![License](https://poser.pugx.org/stichoza/google-translate-php/license.svg)](https://packagist.org/packages/stichoza/google-translate-php)

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
    
    
