Google-Translate-PHP
====================

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
$tr = new GoogleTranslate("en", "ka");
```
Rr set/change languages later
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
    
    
