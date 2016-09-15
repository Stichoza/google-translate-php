<?php

require_once 'vendor/autoload.php';

$translator = new \Stichoza\GoogleTranslate\TranslateClient('en', 'pt');

//echo $translator->translate('dog');

$data = ['dog', 'cat', 'fish'];

var_dump($translator->translate($data));
