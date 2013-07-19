Google-Translate-PHP
====================

Google Translate API free PHP class. Translates totally free of charge.

## Usage

Instantiate GoogleTranslate object
```php
$tr = new GoogleTranslate("en", "ka");
```
or set/change languages later
```php
$tr = new GoogleTranslate();
$tr->setLangFrom("en");
$tr->setLangTo("ka");
```
translate sentences
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
    
    
